<?php
namespace AwsBootcamp\DataRepository;

/**
 * Factory service
 */
class Factory { 
    /**
     * Array of config settings
     * @var array
     */
    protected $_config = array();

    /**
     * Factory constructor
     *
     * @param array $config Config
     * @return void
     */
    public function __construct(array $config) { 
        $this->_config = $config;
    }

    /**
     * Get an instance of an implementation
     * 
     * @param string $implementation Implementation class name
     * @param array $params Parmeters used for the instantiation
     * @return IDataRepository Instance of a class implementing IDataRepository
     */
    public static function getInstance($implementation, array $params) {

        $credentials['key'] = $params['key'];
        $credentials['secret'] = $params['secret'];
        if (isset($params['token'])) { 
            $credentials['token'] = $params['token'];
        }

        $config = array(
            'credentials' => $credentials,
            'version' => 'latest'
        );
       
        if (isset($params['region'])) {
            $config['region'] = $params['region'];
        }

        switch (strtolower($implementation)) { 
            case 'kinesis':
                $client = \Aws\Kinesis\KinesisClient::factory($config);
                $repository = new \AwsBootcamp\DataRepository\Kinesis($client, $params['streamName']);
            break;
        
            case 'firehose':
                $client = \Aws\Firehose\FirehoseClient::factory($config);
                $repository = new \AwsBootcamp\DataRepository\Firehose($client, $params['streamName']);
            break;
                    
            case 'file':
                $repository = new \AwsBootcamp\DataRepository\File($params['file']);
                $msg = 'Generated dataset file : ' . $params['file'];
            break;

            case 'csv':
                $repository = new \AwsBootcamp\DataRepository\CSV($params['file']);
                $msg = 'Generated dataset file : ' . $params['file'];
            break;

            case 'dynamodb':
                $client = \Aws\DynamoDb\DynamoDbClient::factory($config);
                $repository = new \AwsBootcamp\DataRepository\Dynamodb($client, $params['tableName']);
            break;

            case 'sqs':
                $client = \Aws\Sqs\SqsClient::factory($config);
                $repository = new \AwsBootcamp\DataRepository\SQS($client, $params['queueUrl']);
                if ($params['batchSize'] > 10) { 
                    throw new \Exception('Fatal Error : Batch size must be lower than 10 with sqs - ' . $params['batchSize'] . ' given' . PHP_EOL);
                }
            break;

            case 'cloudwatchlogs':
                $client = \Aws\CloudwatchLogs\CloudwatchLogsClient::factory($config);
                $repository = new \AwsBootcamp\DataRepository\CloudwatchLogs($client, $params['streamName'], $params['groupName']);
            break;

            case 's3':
                $client = \Aws\S3\S3Client::factory($config);
                $repository = new \AwsBootcamp\DataRepository\S3($client, $params['bucketName'], $params['prefix']);
            break;

            case 'lambda':
                $client = \Aws\Lambda\LambdaClient::factory($config);
                $repository = new \AwsBootcamp\DataRepository\Lambda($client, $params['functionName']);
            break;

            default: 
                throw new \Exception('Must provide a valid value for implementation');
            break;
        }

        return $repository;
    }
}
