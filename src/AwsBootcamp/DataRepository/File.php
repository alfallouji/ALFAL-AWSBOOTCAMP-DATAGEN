<?php
namespace AwsBootcamp\DataRepository;

/**
 * File Service
 * Class that allows to push data to a file
 */
class File implements IDataRepository { 
    /**
     * Filename
     * @var string
     */
    protected $_filename = null;

    /**
     * Class constructor
     *
     * @param string $filename File name
     * @return void
     */
    public function __construct($filename) { 
        $this->_filename = $filename;
        file_put_contents($this->_filename, "");        
    }
    
    /**
     * Returns filename
     *
     * @return string Filename
     */
    public function getFilename() { 
        return $this->_filename;
    }
    
    /**
     * Push current batch to kinesis
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $content = null;
        foreach ($batch as $record) {
            $content .=  json_encode(array('Data' => json_encode($record), 'PartitionKey' => uniqid(),)) . PHP_EOL;
        }
 
        $result = file_put_contents($this->_filename, $content, FILE_APPEND);
        \cli::log('Pushing a batch of ' . sizeof($batch) . ' records to file : ' . $this->_filename);

        return $result;
    }
}
