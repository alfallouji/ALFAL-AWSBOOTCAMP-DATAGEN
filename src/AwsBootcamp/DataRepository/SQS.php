<?php
namespace AwsBootcamp\DataRepository;

/**
 * SQS Service
 * Class that allows to push data to a sqs queue
 */
class SQS implements IDataRepository { 
    /**
     * SQS queue url
     * @var string
     */
    protected $_queueUrl = null;

    /**
     * SQS client
     * @var Aws\SQS\SQSClient
     */
    protected $_client = null;
	
    /**
     * Class constructor
     *
     * @param string $queueUrl Queue url 
     * @param \Aws\Sqs\SqsClient $client SQS client
     * @return void
     */
    public function __construct($queueUrl, \Aws\Sqs\SqsClient $client) { 
        $this->_queueUrl = $queueUrl;
        $this->_client = $client;
    }

    /**
     * Push current batch to kinesis
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $entries = array();
        foreach ($batch as $record) {
            $body = json_encode($record);
            $entries[] = array('Id' => uniqid(), 'MessageBody' => $body);
        }

        \cli::log('Pushing a batch of ' . sizeof($batch) . ' records to sqs table : ' . $this->_queueUrl);
        $result = $this->_client->sendMessageBatch(array('QueueUrl' => $this->_queueUrl, 'Entries' => $entries));

        return $result;
    }
}
