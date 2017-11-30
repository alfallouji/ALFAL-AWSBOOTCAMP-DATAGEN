<?php
$start = microtime(true); 

$help = <<<EOT

Generate a dataset and persist it into Kinesis, Dynamodb or local file

Usage: {$_SERVER['_']} {$_SERVER['argv'][0]} OPTIONS

    --batchSize                 Size of the batch (max of 25 for dynamodb and 500 for kinesis)
    --total                     Total size of the dataset
    --isLocal                   Set the script to run in a local environment (will fetch aws credentials from config)
    --implementation=value      Implementation to use (kinesis|firehose|sqs|cloudwatchlogs|dynamodb|s3|lambda|file|csv)
    --file=value                Filename for the file implementation (e.g. /tmp/dataset.json)
    --region=value              AWS region to use (e.g. us-east-1)
    --profile=value             Profile to use (e.g. base)
    --tableName=value           Dynamodb table name 
    --queueUrl=value            URL to the SQS queue
    --groupName=value           Cloudwatch logs group name
    --streamName=value          Cloudwatch logs stream name
    --help                      Display this help

Example: {$_SERVER['_']} {$_SERVER['argv'][0]} --isLocal --implementation=file --filename=/tmp/dataset.json


EOT;

class cli { public static function log($m) { echo '[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL; } }
require __DIR__ . '/../vendor/autoload.php';

$longopts = array(
    'batchSize::', 
    'total::', 
    'implementation:', 
    'isLocal::', 
    'file::', 
    'region::', 
    'profile::', 
    'tableName::',
    'queueUrl::',
    'groupName::',
    'streamName::', 
    'help'
);
$opts = getopt(null, $longopts);

if (isset($opts['help'])) { 
    die($help);
}

$isLocal = isset($opts['isLocal']);
$profile = isset($opts['profile']) ? $opts['profile'] : 'game-base';
$configFile = __DIR__ . '/../config/profiles/' . $profile . '/template.php';
$batchSize = isset($opts['batchSize']) ? $opts['batchSize'] : 25;
$total = isset($opts['total']) ? $opts['total'] : 100;
$implementation = isset($opts['implementation']) ? $opts['implementation'] : 'file';

$credentials = array('key' => null, 'secret' => null);
$json = array();
if (!$isLocal) { 
    // Fetch creds from ec2 metadata instance (if available)
    $creds = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/ec2-s3Role');
    $json = json_decode($creds, true);
} else { 
    $credentials = require __DIR__ . '/../config/aws/credentials.php';
}

$factoryConfig['key'] = isset($json['AccessKeyId']) ? $json['AccessKeyId'] : $credentials['key'];
$factoryConfig['secret'] = isset($json['SecretAccessKey']) ? $json['SecretAccessKey'] : $credentials['secret'];
$factoryConfig['file'] = isset($opts['file']) ? $opts['file'] : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataset.json';
$factoryConfig['region'] = isset($opts['region']) ? $opts['region'] : 'us-east-1';
$factoryConfig['tableName'] = isset($opts['tableName']) ? $opts['tableName'] : 'datagen-dynamo-table';
$factoryConfig['queueUrl'] = isset($opts['queueUrl']) ? $opts['queueUrl'] : null;
$factoryConfig['groupName'] = isset($opts['groupName']) ? $opts['groupName'] : null;
$factoryConfig['streamName'] = isset($opts['streamName']) ? $opts['streamName'] : null;

cli::log('Options');
foreach($factoryConfig as $k => $v) { 
    cli::log($k . '=' . $v);
}

$msg = null;
$factory = new AwsBootcamp\DataRepository\Factory($factoryConfig);
$repository = $factory->getInstance($implementation, $factoryConfig);
$gen = new AwsBootcamp\Generator\DataSet($repository);
$config = require $configFile;
$dataSet = $gen->execute($config, $total, $batchSize);

echo PHP_EOL . $msg;
echo PHP_EOL . 'Stats' . PHP_EOL;
echo '---------' . PHP_EOL;
echo 'Total generated : ' . sizeof($dataSet);
$time = (microtime(true) - $start);
echo PHP_EOL . 'Time : ' . $time . ' seconds' . PHP_EOL;
echo 'Speed : ' . (sizeof($dataSet) / $time) . ' records/sec' . PHP_EOL . PHP_EOL;
