<?php
namespace AwsBootcamp\DataRepository;

/**
 * Kinesis Service
 * Class that allows to push data to Kinesis
 */
interface IDataRepository { 
    /**
     * Push current batch to kinesis
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch);
}
