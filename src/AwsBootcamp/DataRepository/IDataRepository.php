<?php
namespace AwsBootcamp\DataRepository;

/**
 * DataRepository Interface
 */
interface IDataRepository { 
    /**
     * Push current batch 
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch);
}
