<?php
namespace AwsBootcamp\Generator;

/**
 * DataSet Generator (for Kinesis)
 * Class that will generate a set of data and push it to a Kinesis stream
 */
class DataSet { 
    /**
     * Assoc array containing configuration definition of the data set to generate
     * @var array
     */
    protected $_config = array();

    /**
     * Current data row being generated
     * @var array
     */
    protected $_currentData = array();

    /**
     * Contains the whole data set that is being generated
     * @var array
     */
    protected $_dataSet = array();

    /**
     * Contains the total that has been generated for weightedData type field
     * @var array
     */
    protected $_weightedData = array();

    /**
     * Contains the patterns used by all the different fields for rules (e.g. {field1}, {field2}, etc.)
     * @var array
     */
    protected $_patternFields = array();

    /**
     * Actual expected distribution (from the config) based on the desired total
     * @var array
     */
    protected $_expectedDistribution = array();

    /**
     * Current distribution of the data set 
     * @var array
     */
    protected $_currentDistribution = array();

    /**
     * Helper class to evaluate a mathematical expression
     * @var \Webit\Util\EvalMath\EvalMath
     */
    protected $_evalMath = null;

    /**
     * Helper class to generate fake data 
     * @var \Faker
     */
    protected $_faker = null;

    /**
     * Array containing various counters
     * @var array
     */
    protected $_counters = array();

    /**
     * Array containing the current batch that will be sent
     * @var array
     */
    protected $_currentBatch = array();

    /**
     * Data repository where data is being pushed to 
     * @var AwsBootcamp\DataRepository\IDataRepository 
     */
    protected $_dataRepository = null;

    /**
     * Class constructor
     *
     * @param AwsBootcamp\DataRepository\IDataRepository $dataRepository Data Repository
     * @return void
     */
    public function __construct(\AwsBootcamp\DataRepository\IDataRepository $dataRepository) { 
        $this->_faker = \Faker\Factory::create();
        $this->_evalMath = new \Webit\Util\EvalMath\EvalMath();
        $this->_dataRepository = $dataRepository;
    }

    /**
     * Generate data set
     *
     * @param array $config Configuration of the data set to generate
     * @param int $total Total size of the data set to generate
     * @param int $batchSize Total size of the batch to send to Kinesis
     *
     * @return array The whole generated dataset
     */
    public function execute(array $config, $total, $batchSize) { 
        $this->_dataSet = array();
        $this->_patternFields = array();
        $this->_currentBatch = array();

        // Get list of fields for pattern replacement
        foreach ($config['fields'] as $field => $data) { 
            $this->_patternFields[] = '{' . $field . '}';
        }

        // Get desired distribution if any defined
        if (isset($config['distribution'])) { 
            foreach ($config['distribution'] as $field => $data) { 
                $sum = array_sum($data);
                foreach ($data as $value => $weight) { 
                    $this->_expectedDistribution[$field][$value] = (int) (($weight / $sum) * $total);
                    $this->_currentDistribution[$field][$value] = 0;
                }

                // Ensure we generate the right amount 
                $delta = $total - array_sum($this->_expectedDistribution[$field]);
                if ($delta != 0) { 
                    $this->_expectedDistribution[$field][$value] += $delta;
                }

                foreach ($data as $value => $weight) { 
                    \cli::log('I will generate ' . $this->_expectedDistribution[$field][$value] . ' records with ' . $field . ' = ' . $value);
                }
            }
        }

        $bigTotal = 1;
        $cpt = 1;
        while($cpt <= $total) {
            foreach ($config['fields'] as $k => $v) { 
                $this->_currentData[$k] = $this->_computeField($k, $v);
            }

            if ($this->_validateData($this->_currentData)) {
                ++$cpt;
                $this->_dataSet[] = $this->_currentData;
                $this->_currentBatch[] = $this->_currentData;

                // Push batch to kinesis 
                if (sizeof($this->_currentBatch) == $batchSize) { 
                    $this->_dataRepository->push($this->_currentBatch);
                    $this->_currentBatch = array();
                }
            } else {
                // Decrement counter (if any)
                foreach ($this->_counters as $key => $value) { 
                    $this->_counters[$key] -= $config['fields'][$key]['counter']['step'];
                }
            }

            $this->_currentData = array();
            ++$bigTotal;
        }

        // If anything left, push it to Kinesis
        if (sizeof($this->_currentBatch) > 0) { 
            $this->_dataRepository->push($this->_currentBatch);
        }

        \cli::log('Example of a data entry that got generated:');
        \cli::log(print_r($this->_dataSet[0], true));
        \cli::log('Example of a data entry that got generated:');
        \cli::log(print_r($this->_dataSet[sizeof($this->_dataSet) - 1], true));
        \cli::log('I had to generate a total of (in order to make it): ' . $bigTotal);

        return $this->_dataSet;
    }

    /**
     * Compute a field
     *
     * @param string $k Field name 
     * @param array $v Assoc array containing settings and config for the field to compute
     * @return mixed Computed value
     * @throws \Exception throws an exception if an invalid configuration is defined
     */
    protected function _computeField($k, $v) {
        switch ($v['type']) { 
        case 'randomNumber':
            if (!isset($v['randomNumber']['min'])) { 
                throw new \Exception('Invalid configuration. Must define a randomNumber[min] value : ' . print_r($v, true)); 
            }
            if (!isset($v['randomNumber']['max'])) { 
                throw new \Exception('Invalid configuration. Must define a randomNumber[max] value : ' . print_r($v, true)); 
            }

            return rand($v['randomNumber']['min'], $v['randomNumber']['max']);
            break;

        case 'date':
            if (!isset($v['format'])) { 
                throw new \Exception('Invalid configuration. Must define a format value for date : ' . print_r($v, true)); 
            }

            return date($v['format']);
            break;

        case 'randomList':
            if (!isset($v['randomList'])) { 
                throw new \Exception('Invalid configuration. Must define a randomList value : ' . print_r($v, true)); 
            }

            return $v['randomList'][rand(0, sizeof($v['randomList']) - 1)];
            break;

        case 'weightedList':
            if (!isset($this->_weightedData[$k])) { 
                $this->_weightedData[$k] = array('config' => $v['weightedList'], 'current' => array_keys($v['weightedList']));
            }

            return $this->_getRandomWeightedElement($this->_weightedData[$k]['config']);
            break;

        case 'constant':
            if (!isset($v['constant'])) { 
                throw new \Exception('Invalid configuration. Must define a constant value : ' . print_r($v, true)); 
            }

            return $v['constant'];
            break;

        case 'rules':
            return $this->_computeRules($v);
            break;

        case 'mathExpression':
            return $this->_computeMathExpression($v);
            break;

        case 'faker':
            if (!isset($v['property'])) { 
                throw new \Exception('Invalid configuration. Must define a property value : ' . print_r($v, true)); 
            }

            $propertyName = $v['property'];
            return $this->_faker->$propertyName;
            break;

        case 'counter':
            if (!isset($v['counter']['start']) || !isset($v['counter']['step'])) { 
                throw new \Exception('Invalid configuration. Must provide a start and a step parameters : ' . print_r($v, true));
            }

            if (!isset($this->_counters[$k])) { 
                $this->_counters[$k] = $v['counter']['start'];
            } else { 
                $this->_counters[$k] += $v['counter']['step'];
            }

            return $this->_counters[$k];
            
        default:
            throw new \Exception('Invalid configuration. Unknown type defined : ' . $v['type']);
            break;
        }
    }

    /**
     * Compute the value based on the different rules defined
     *
     * @param $v array Array containing rules and data config settings
     * @return mixed Value computed by the rules
     */
    protected function _computeRules($v) {
        foreach($v['rules'] as $value => $patternRule) { 
            $rule = str_replace($this->_patternFields, $this->_currentData, $patternRule);

            if (false !== strpos($rule, '{') || false !== strpos($rule, '}')) {
                throw new \Exception('Check your config file. Unable to replace all fields defined in this rule : ' . $rule . ' - pattern: ' . $patternRule);
            }

            // Checking that a condition doesnt contain any letter (avoid executing any non-math conditon)
            if (preg_match('/[[:alpha:]]+/u', $rule)) {
                throw new \Exception('A rule can only be a mathematical condition: ' . $rule . ' - pattern: ' . $patternRule);
            }

            // With great power comes great responsibility - eval is very dangerous !!
            if(eval('return ' . $rule . ';')) {
                return $value;
            }
        }

        throw new \Exception('None of the rules provided matched - cant return any value');
    }

    /**
     * Compute a mathematical expression
     *
     * @param string $v Math expression
     * @return mixed Evaluated result
     */
    protected function _computeMathExpression($v) { 
        $rule = str_replace($this->_patternFields, $this->_currentData, $v['mathExpression']);

        if (false !== strpos($rule, '{') || false !== strpos($rule, '}')) {
            throw new \Exception('Check your config file. Unable to replace all fields defined in this math expression : ' . $rule);
        }
        return $this->_evalMath->evaluate($rule);
    }

    /**
     * Validate data (based on global conditions / rules) against expected distribution
     *
     * @param array $dataRow Assoc array 
     * @return boolean True if data is valid, false otherwise
     */
    protected function _validateData($dataRow) {
        foreach ($this->_expectedDistribution as $k => $values) {
            $currentValue = $dataRow[$k];
            if ($this->_currentDistribution[$k][$currentValue] >= $this->_expectedDistribution[$k][$currentValue]) {
               return false;
            }
        }

        // Update currentDistribution 
        foreach ($this->_expectedDistribution as $k => $values) { 
            $currentValue = $dataRow[$k];
            ++$this->_currentDistribution[$k][$currentValue];
        }

        return true;
    }

    /**
     * Helper function to get a random element using weights
     * 
     * @param array $weightedValues Assoc array (value => weight)
     * @return mixed Value
     */
    protected function _getRandomWeightedElement(array $weightedValues) {
        $rand = mt_rand(1, (int) array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
    }
}
