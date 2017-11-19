<?php
namespace AwsBootcamp\DataRepository;

/**
 * Dynamodb Service
 * Class that allows to push data to a dynamodb table
 */
class Dynamodb implements IDataRepository { 
    /**
     * Dynamodb table name
     * @var string
     */
    protected $_tableName = null;

    /**
     * Dynamodb client
     * @var Aws\Dynamodb\DynamodbClient
     */
    protected $_client = null;
	
    /**
     * Class constructor
     *
     * @param string $tableName Tablename     
     * @param \Aws\DynamoDb\DynamoDbClient $client Dynamodb client
     * @return void
     */
    public function __construct($tableName, \Aws\DynamoDb\DynamoDbClient $client) { 
        $this->_tableName = $tableName;
        $this->_client = $client;
    }

    /**
     * Push current batch to dynamodb
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $putRequest = array();
        foreach ($batch as $record) {
            $item = array();
            foreach ($record as $k => $v) { 
                $item[$k] = array('S' => (string) $v);
            }
            $item['id'] = array('S' => uniqid());
            $putRequest[] = array('PutRequest' => array('Item' => $item));
        }

        \cli::log('Pushing a batch of ' . sizeof($batch) . ' records to dynamodb table : ' . $this->_tableName);
	    $result = $this->_client->batchWriteItem(array("RequestItems" => array($this->_tableName => $putRequest)));

        return $result;
    }
}
