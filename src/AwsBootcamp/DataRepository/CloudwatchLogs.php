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
    protected $_nextSequenceToken = '49577046975324293808998647044542222926306086973125849234';

    /**
     * Class constructor
     *
     * @param string $streamName Name of the stream
     * @param string $groupName Name of the group
     * @param \Aws\CloudwatchLogs\CloudwatchLogsClient $client CloudwatchLogs client
     * @return void
     */
    public function __construct($streamName, $groupName, \Aws\CloudwatchLogs\CloudwatchLogsClient $client) { 
        $this->_streamName = $streamName;
        $this->_groupName = $groupName;
        $this->_client = $client;
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
            $events[] = array('timestamp' => time(), 'message' => $message);
        }

        \cli::log('Pushing a batch of ' . sizeof($batch) . ' records to cloudwatch logs : ' . $this->_streamName . ' - ' . $this->_groupName);
        $request = array('logGroupName' => $this->_groupName, 'logStreamName' => $this->_streamName, 'logEvents' => $events);
        if ($this->_nextSequenceToken) { 
            $request['sequenceToken'] = $this->_nextSequenceToken;
        }

        $result = $this->_client->putLogEvents($request);
        $this->_nextSequenceToken = $result['nextSequenceToken'];
        \cli::log('SeqNumber: ' . $result['nextSequenceToken']);

        return $result;
    }
}
