<?php
namespace AwsBootcamp\DataRepository;

/**
 * Kinesis Service
 * Class that allows to push data to Kinesis
 */
class Kinesis implements IDataRepository { 
    /**
     * Kinesis \client
     * @var \Aws\Kinesis\KinesisClient
     */
    protected $_kinesis = null;

    /**
     * Kinesis Stream name
     * @var string
     */
    protected $_kinesisStreamName = null;

    /**
     * Class constructor
     *
     * @param Aws\Kinesis\KinesisClient $kinesis Kinesis Client
     * @param string $kinesisStreamName Name of the kinesis stream
     * @return void
     */
    public function __construct(\Aws\Kinesis\KinesisClient $kinesis, $kinesisStreamName) { 
        $this->_kinesis = $kinesis;
        $this->_kinesisStreamName = $kinesisStreamName;
    }

    /**
     * Push current batch to kinesis
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $records = array();
        foreach ($batch as $record) { 
            $records[] = array('Data' => json_encode($record), 'PartitionKey' => uniqid(),);
        }
        $result = $this->_kinesis->putRecords(array('Records' => $records, 'StreamName' => $this->_kinesisStreamName));
        \cli::log('Pushing to kinesis a batch of ' . sizeof($records) . ' records to ' . $this->_kinesisStreamName);

        return $result;
    }
}
