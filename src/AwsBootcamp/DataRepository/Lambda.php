<?php
namespace AwsBootcamp\DataRepository;

/**
 * Lambda Service
 * Class that allows to push data to Lambda
 */
class Lambda implements IDataRepository { 
    /**
     * Lambda \client
     * @var \Aws\Lambda\LambdaClient
     */
    protected $_lambda = null;

    /**
     * Lambda Bucket name
     * @var string
     */
    protected $_functionName = null;

    /**
     * Lambda invocation type
     * @var string
     */
    protected $_invocationType = 'RequestResponse';

	/**
	 * Log type
	 * @var string
	 */
	protected $_logType = 'None';

	/**
	 * Qualifier
	 * @var string
	 */
	protected $_qualifier = null;

    /**
     * Class constructor
     *
     * @param Aws\Lambda\LambdaClient $lambda Lambda Client
     * @param string $functionName Name of the lambda function
     * @return void
     */
    public function __construct(\Aws\Lambda\LambdaClient $lambda, $functionName) { 
        $this->_lambda = $lambda;
        $this->_functionName = $functionName;
        $this->_invocationType = 'RequestResponse';
		$this->_logType = 'None';
        $this->_qualifier = null;
        $this->_clientContext = null;
    }

    /**
     * Push current batch to lambda
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $data = null;
        foreach ($batch as $record) { 
            $data .= json_encode($record) . PHP_EOL;
        }

		$result = $this->_lambda->invoke(array(
			// FunctionName is required
			'FunctionName' => $this->_functionName,
			'InvocationType' => $this->_invocationType,
			'LogType' => $this->_logType,
			'ClientContext' => $this->_clientContext,
			'Payload' => $data,
		));

		\cli::log('Pushing to lambda a payload of ' . sizeof($batch) . ' records to ' . $this->_functionName);

        return $result;
    }
}
