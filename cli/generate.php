<?php
$start = microtime(true); 

$help = <<<EOT

Generate a dataset and persist it into Kinesis, Dynamodb or local file

Usage: {$_SERVER['_']} {$_SERVER['argv'][0]} OPTIONS

    --isLocal                   Set the script to run in a local environment (will fetch aws credentials from config)
    --implementation=value      Implementation to use (kinesis|dynamodb|file)
    --file=value                Filename for the file implementation (e.g. /tmp/dataset.json)
    --region=value              AWS region to use (e.g. us-east-1)
    --configFile=value          Config file (e.g. config/myconfig.php)
    --tableName=value           Dynamodb table name 
    --help                      Display this help

Example: {$_SERVER['_']} {$_SERVER['argv'][0]} --isLocal --implementation=file --filename=/tmp/dataset.json


EOT;

class cli { public static function log($m) { echo '[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL; } }
require __DIR__ . '/../vendor/autoload.php';

$longopts = array('implementation:', 'isLocal::', 'file::', 'region::', 'configFile::', 'tableName::', 'help');
$opts = getopt(null, $longopts);

if (isset($opts['help'])) { 
    die($help);
}

$isLocal = isset($opts['isLocal']);
$implementation = isset($opts['implementation']) ? $opts['implementation'] : 'file';
$file = isset($opts['file']) ? $opts['file'] : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataset.json';
$region = isset($opts['region']) ? $opts['region'] : 'us-east-1';
$configFile = isset($opts['configFile']) ? $opts['configFile'] : __DIR__ . '/../config/game-base.template.php';
$tableName = isset($opts['tableName']) ? $opts['tableName'] : 'datagen-dynamo-table';

$credentials = array('key' => null, 'secret' => null);
$json = array();
if (!$isLocal) { 
    // Fetch creds from ec2 metadata instance (if available)
    $creds = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/ec2-s3Role');
    $json = json_decode($creds, true);
} else { 
    $credentials = require __DIR__ . '/../config/credentials.php';
}

$key = isset($json['AccessKeyId']) ? $json['AccessKeyId'] : $credentials['key'];
$secret = isset($json['SecretAccessKey']) ? $json['SecretAccessKey'] : $credentials['secret'];

$msg = null;
switch ($implementation) { 
    case 'kinesis':
        $kinesis = Aws\Kinesis\KinesisClient::factory(array(
            'credentials' => array(
                'key'    => $key,
                'secret' => $secret,
            ),
            'region' => $region,
            'version' => 'latest',
        ));
        $streamName = 'elasticsearch-stream-01';
        $repository = new AwsBootcamp\DataRepository\Kinesis($kinesis, $streamName);
    break;
    
    case 'file':
        $repository = new AwsBootcamp\DataRepository\File($file);
        $msg = 'Generated dataset file : ' . $file;
    break;

    case 'dynamodb':
    $client = Aws\DynamoDb\DynamoDbClient::factory(array(
            'credentials' => array(
                'key'    => $key,
                'secret' => $secret,
            ),
            'region' => $region,
            'version' => 'latest',
    ));
    break;

    default: 
        throw new \Exception('Must provide a valid value for implementation');
    break;
}

$gen = new AwsBootcamp\Generator\DataSet($repository);
$config = require $configFile;
$dataSet = $gen->execute($config, 220, 50);

echo PHP_EOL . $msg;
echo PHP_EOL . 'Stats' . PHP_EOL;
echo '---------' . PHP_EOL;
echo 'Total generated : ' . sizeof($dataSet);
$time = (microtime(true) - $start);
echo PHP_EOL . 'Time : ' . $time . ' seconds' . PHP_EOL;
echo 'Speed : ' . (sizeof($dataSet) / $time) . ' records/sec' . PHP_EOL . PHP_EOL;
