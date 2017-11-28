<?php
namespace AwsBootcamp\DataRepository;

/**
 * Firehose Service
 * Class that allows to push data to Firehose
 */
class Firehose implements IDataRepository { 
    /**
     * Firehose \client
     * @var \Aws\Firehose\FirehoseClient
     */
    protected $_firehose = null;

    /**
     * Firehose Stream name
     * @var string
     */
    protected $_firehoseStreamName = null;

    /**
     * Class constructor
     *
     * @param Aws\Firehose\FirehoseClient $firehose Firehose Client
     * @param string $firehoseStreamName Name of the firehose stream
     * @return void
     */
    public function __construct(\Aws\Firehose\FirehoseClient $firehose, $firehoseStreamName) { 
        $this->_firehose = $firehose;
        $this->_firehoseStreamName = $firehoseStreamName;
    }

    /**
     * Push current batch to firehose
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $records = array();
        foreach ($batch as $record) { 
        	$records[] = array('Data' => json_encode($record),);
        }
        $result = $this->_firehose->putRecordBatch(array('Records' => $records, 'DeliveryStreamName' => $this->_firehoseStreamName));
        \cli::log('Pushing to firehose a batch of ' . sizeof($records) . ' records to ' . $this->_firehoseStreamName);
 
        return $result;
    }
}
