<?php
namespace AwsBootcamp\DataRepository;

/**
 * CSV Service
 * Class that allows to push data to a CSV file
 */
class CSV implements IDataRepository { 
    /**
     * Filename
     * @var string
     */
    protected $_filename = null;

    /**
     * Class constructor
     *
     * @param string $filename File name
     * @return void
     */
    public function __construct($filename) { 
        $this->_filename = $filename;
    }

    /**
     * Returns filename
     *
     * @return string Filename
     */
    public function getFilename() { 
        return $this->_filename;
    }
    
    /**
     * Push current batch to kinesis
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $content = null;
        foreach ($batch as $record) {
            $content .=  implode(',', $record) . PHP_EOL;
        }

        $result = file_put_contents($this->_filename, $content, FILE_APPEND);
        \cli::log('Pushing a batch of ' . sizeof($batch) . ' records to file : ' . $this->_filename);

        return $result;
    }
}
