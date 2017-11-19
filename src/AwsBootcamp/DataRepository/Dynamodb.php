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
    protected $_tablename = null;

    /**
     * Dynamodb client
     * @var Aws\Dynamodb\DynamodbClient
     */
    protected $_client = null;
	
    /**
     * Class constructor
     *
     * @param string $tablename Tablename     
     * @return void
     */
    public function __construct($tablename, Aws\Dynamodb\Client $client) { 
        $this->_tablename = $tablename;
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
        $content = null;
        foreach ($batch as $record) {
            $content .=  json_encode(array('Data' => json_encode($record), 'PartitionKey' => uniqid(),)) . PHP_EOL;
        }
 
        \cli::log('Pushing a batch of ' . sizeof($batch) . ' records to dynamodb table : ' . $this->_tablename);

        /** @todo 
	$response = $this->_client->batchWriteItem(array(
		"RequestItems" => array(
		$tableName => array(
		array(
			"PutRequest" => array(
				"Item" => array(
					"id"   => array('N' => 40),
					"type" => array('S' => "book"),
					"title"=> array('S' => "DynamoDB Cookbook")
				))
		),
		array(
			"DeleteRequest" => array(
			   ...
        */

        return $result;
    }
}
