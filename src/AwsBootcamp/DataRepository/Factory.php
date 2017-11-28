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
        switch (strtolower($implementation)) { 
            case 'kinesis':
                $kinesis = \Aws\Kinesis\KinesisClient::factory(array(
                    'credentials' => array(
                        'key'    => $params['key'],
                        'secret' => $params['secret'],
                    ),
                    'region' => $params['region'],
                    'version' => 'latest',
                ));
                $params['streamName'] = 'elasticsearch-stream-01';
                $repository = new \AwsBootcamp\DataRepository\Kinesis($kinesis, $params['streamName']);
            break;
        
            case 'firehose':
                $firehose = \Aws\Firehose\FirehoseClient::factory(array(
                    'credentials' => array(
                        'key'    => $params['key'],
                        'secret' => $params['secret'],
                    ),
                    'region' => $params['region'],
                    'version' => 'latest',
                ));
                $repository = new \AwsBootcamp\DataRepository\Firehose($firehose, $params['streamName']);
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
               $client = \Aws\DynamoDb\DynamoDbClient::factory(array(
                        'credentials' => array(
                            'key'    => $params['key'],
                            'secret' => $params['secret'],
                        ),
                        'region' => $params['region'],
                        'version' => 'latest',
                ));
                $repository = new \AwsBootcamp\DataRepository\Dynamodb($params['tableName'], $client);
            break;

            case 'sqs':
                $client = \Aws\Sqs\SqsClient::factory(array(
                        'credentials' => array(
                            'key'    => $params['key'],
                            'secret' => $params['secret'],
                        ),
                        'region' => $params['region'],
                        'version' => 'latest',
                ));
                $repository = new \AwsBootcamp\DataRepository\SQS($params['queueUrl'], $client);
                if ($params['batchSize'] > 10) { 
                    throw new \Exception('Fatal Error : Batch size must be lower than 10 with sqs - ' . $params['batchSize'] . ' given' . PHP_EOL);
                }
            break;

            case 'cloudwatchlogs':
                $client = \Aws\CloudwatchLogs\CloudwatchLogsClient::factory(array(
                    'credentials' => array(
                        'key'    => $params['key'],
                        'secret' => $params['secret'],
                    ),
                    'region' => $params['region'],
                    'version' => 'latest',
                ));
                $repository = new \AwsBootcamp\DataRepository\CloudwatchLogs($params['streamName'], $params['groupName'], $client);
            break;

            case 's3':
                $client = \Aws\S3\S3Client::factory(array(
                        'credentials' => array(
                            'key'    => $params['key'],
                            'secret' => $params['secret'],
                        ),
                        'region' => $params['region'],
                        'version' => 'latest',
                ));
                $repository = new \AwsBootcamp\DataRepository\S3($client, $params['bucketName']);
            break;


            default: 
                throw new \Exception('Must provide a valid value for implementation');
            break;
        }

        return $repository;
    }
}
