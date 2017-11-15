<?php
$start = microtime(true); 

class cli { public static function log($m) { echo '[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL; } }

require __DIR__ . '/../vendor/autoload.php';

if (!getenv('IS_DEV')) { 
    // Fetch creds from ec2 metadata instance (if available)
    $creds = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/ec2-s3Role');
    $json = json_decode($creds, true);
} else { 
    $credentials = require __DIR__ . '/../config/credentials.php';
}

$defaultKey = isset($json['AccessKeyId']) ? $json['AccessKeyId'] : $credentials['key'];
$defaultSecret = isset($json['SecretAccessKey']) ? $json['SecretAccessKey'] : $credentials['secret'];

$kinesis = Aws\Kinesis\KinesisClient::factory(array(
    'credentials' => array(
        'key'    => $key,
        'secret' => $secret,
    ),
    'region' => $region,
    'version' => 'latest',
));

$streamName = 'elasticsearch-stream-01';
$dataRepository = new AwsBootcamp\DataRepository\Kinesis($kinesis, $streamName);
$gen = new AwsBootcamp\Generator\DataSet($dataRepository);
 
$config = require __DIR__ . '/../config/game-base.template.php';
$dataSet = $gen->execute($config, 100, 50);

echo PHP_EOL . 'Stats' . PHP_EOL;
echo '---------' . PHP_EOL;
echo 'Total generated : ' . sizeof($dataSet);
$time = (microtime(true) - $start);
echo PHP_EOL . 'Time : ' . $time . ' seconds' . PHP_EOL;
echo 'Speed : ' . (sizeof($dataSet) / $time) . ' records/sec' . PHP_EOL . PHP_EOL;
