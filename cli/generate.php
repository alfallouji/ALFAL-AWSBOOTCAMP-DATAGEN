<?php
$start = microtime(true); 

class cli { public static function log($m) { echo '[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL; } }
require __DIR__ . '/../vendor/autoload.php';

$shortopts = 'i:d::f::';
$longopts = array('implementation:', 'dev::', 'file::',);
$opts = getopt($shortopts, $longopts);
$isDev = isset($opts['d']) ? $opts['d'] : true;
$implementation = isset($opts['i']) ? $opts['i'] : 'file';
$file = isset($opts['f']) ? $opts['f'] : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataset.json';

$json = array();
if (!$isDev) { 
    // Fetch creds from ec2 metadata instance (if available)
    $creds = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/ec2-s3Role');
    $json = json_decode($creds, true);
} else { 
    $credentials = require __DIR__ . '/../config/credentials.php';
}

$key = isset($json['AccessKeyId']) ? $json['AccessKeyId'] : $credentials['key'];
$secret = isset($json['SecretAccessKey']) ? $json['SecretAccessKey'] : $credentials['secret'];
$region = 'us-east-1';
$kinesis = Aws\Kinesis\KinesisClient::factory(array(
    'credentials' => array(
        'key'    => $key,
        'secret' => $secret,
    ),
    'region' => $region,
    'version' => 'latest',
));

$msg = null;
switch ($implementation) { 
    case 'kinesis':
        $streamName = 'elasticsearch-stream-01';
        $repository = new AwsBootcamp\DataRepository\Kinesis($kinesis, $streamName);
    break;
    
    case 'file':
        $repository = new AwsBootcamp\DataRepository\File($file);
        $msg = 'Generated dataset file : ' . $file;
    break;

    default: 
        throw new \Exception('Must provide a valid value for implementation');
    break;
}

$gen = new AwsBootcamp\Generator\DataSet($repository);
$config = require __DIR__ . '/../config/game-base.template.php';
$dataSet = $gen->execute($config, 220, 50);

echo PHP_EOL . $msg;

echo PHP_EOL . 'Stats' . PHP_EOL;
echo '---------' . PHP_EOL;
echo 'Total generated : ' . sizeof($dataSet);
$time = (microtime(true) - $start);
echo PHP_EOL . 'Time : ' . $time . ' seconds' . PHP_EOL;
echo 'Speed : ' . (sizeof($dataSet) / $time) . ' records/sec' . PHP_EOL . PHP_EOL;
