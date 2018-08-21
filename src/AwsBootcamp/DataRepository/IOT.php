<?php
namespace AwsBootcamp\DataRepository;

/**
 * IOT Service
 * Class that allows to push data to a iot queue
 */
class IOT implements IDataRepository { 
    /**
     * IOT queue url
     * @var string
     */
    protected $_topicUrl = null;

    /**
     * IOT client
     * @var Aws\IOT\IOTClient
     */
    protected $_client = null;
	
    /**
     * Class constructor
     *
     * @param \Aws\IotDataPlane\IotDataPlaneClient $client IOT client
    * @param string $topicUrl Queue url 
     * @return void
     */
    public function __construct(\Aws\IotDataPlane\IotDataPlaneClient $client, $topicUrl) { 
        $this->_topicUrl = $topicUrl;
        $this->_client = $client;
    }

    /**
     * Push current batch to IOT
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        foreach ($batch as $record) {
            $body = json_encode($record);
	    	$result = $this->_client->publish([
		    	"payload" => $body,
			    'qos' => 1,
			    'topic' => $this->_topicUrl
		    ]);
        }

        return true;
    }
}
