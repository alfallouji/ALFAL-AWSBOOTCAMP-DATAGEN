<?php
namespace AwsBootcamp\DataRepository;

/**
 * S3 Service
 * Class that allows to push data to S3
 */
class S3 implements IDataRepository { 
    /**
     * S3 \client
     * @var \Aws\S3\S3Client
     */
    protected $_s3 = null;

    /**
     * S3 Bucket name
     * @var string
     */
    protected $_bucketName = null;

    /**
     * S3 Prefix for file
     * @var string
     */
    protected $_prefix = null;

    /**
     * Class constructor
     *
     * @param Aws\S3\S3Client $s3 S3 Client
     * @param string $bucketName Name of the s3 stream
     * @return void
     */
    public function __construct(\Aws\S3\S3Client $s3, $bucketName, $prefix) { 
        $this->_s3 = $s3;
        $this->_bucketName = $bucketName;
        $this->_prefix = $prefix;
    }

    /**
     * Push current batch to s3
     * 
     * @param array $batch Batch to push
     *
     * @return array Result
     */
    public function push(array $batch) { 
        $data = null;
        foreach ($batch as $record) { 
            $data .= json_encode($record) . PHP_EOL;
        }
		$uniqid = uniqid();
		$pathToFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uniqid;
		file_put_contents($pathToFile, $data);
		$filename = $this->_prefix . 'datagen_' . $uniqid;
        $result = $this->_s3->putObject(array(
		    'Bucket'     => $this->_bucketName,
		    'Key'        => $filename,
		    'SourceFile' => $pathToFile,
		    'Metadata'   => array(),
		));
        
		\cli::log('Pushing to s3 a batch of ' . sizeof($batch) . ' records to ' . $this->_bucketName);

        return $result;
    }
}
