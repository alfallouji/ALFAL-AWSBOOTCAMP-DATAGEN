<?php
namespace AwsBootcamp\DataRepository;

/**
 * CloudwatchLogs Service
 * Class that allows to push data to a sqs queue
 */
class CloudwatchLogs implements IDataRepository { 
    /**
     * CloudwatchLogs stream name
     * @var string
     */
    protected $_streamName = null;

    /**
     * CloudwatchLogs group name
     * @var string
     */
    protected $_groupName = null;

    /**
     * CloudwatchLogs client
     * @var Aws\CloudwatchLogs\CloudwatchLogsClient
     */
    protected $_client = null;

    /**
     * Next sequence number
     * @var string
     */
    protected $_nextSequenceToken = null;

    /**
     * Class constructor
     *
     * @param \Aws\CloudwatchLogs\CloudwatchLogsClient $client CloudwatchLogs client
     * @param string $streamName Name of the stream
     * @param string $groupName Name of the group
     * @return void
     */
    public function __construct(\Aws\CloudwatchLogs\CloudwatchLogsClient $client, $streamName, $groupName) {
        $this->_streamName = $streamName . '-' . uniqid();
        $this->_groupName = $groupName;
        $this->_client = $client;
        $this->_client->createLogStream(array(
            'logGroupName' => $this->_groupName, 
            'logStreamName' => $this->_streamName
        ));
    }

    /**
     * Push current batch to cloudwatch logs
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $entries = array();
        foreach ($batch as $record) {
            $message = json_encode($record);
            $events[] = array('timestamp' => round(microtime(true) * 1000), 'message' => $message);
        }

        \cli::log('Pushing a batch of ' . sizeof($batch) . ' records to cloudwatch logs : ' . $this->_streamName . ' - ' . $this->_groupName);
        $request = array('logGroupName' => $this->_groupName, 'logStreamName' => $this->_streamName, 'logEvents' => $events);
        if ($this->_nextSequenceToken) { 
            $request['sequenceToken'] = $this->_nextSequenceToken;
        }

        $result = $this->_client->putLogEvents($request);
        $this->_nextSequenceToken = $result['nextSequenceToken'];
        
        return $result;
    }
}
