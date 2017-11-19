<?php
/**
 * Web page to generate dataset and push it 
 */
// Starting timer
$start = microtime(true); 

// Increasing execution time and memory
ini_set('max_execution_time', 1800);
ini_set('memory_limit', '256M');

require __DIR__ . '/../vendor/autoload.php';

class cli { public static $log = null; public static function log($m) { self::$log .= '[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL; } }

$ec2role = 'datagenRole';
$credentials = array('key' => null, 'secret' => null);
$json = array();
$isLocal = isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'localhost');
if (!$isLocal) {
    $metadataRole = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/');
    // Fetch creds from ec2 metadata instance (if available)
    $creds = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/' . $metadataRole);
    $json = json_decode($creds, true);
} else { 
    $credentialsFile = __DIR__ . '/../config/credentials.php';
    if (file_exists($credentialsFile)) { 
        $credentials = require $credentialsFile;
    }
}

$defaultKey = isset($json['AccessKeyId']) ? $json['AccessKeyId'] : $credentials['key'];
$defaultSecret = isset($json['SecretAccessKey']) ? $json['SecretAccessKey'] : $credentials['secret'];
$token = isset($json['Token']) ? $json['Token'] : null;

$key = isset($_REQUEST['key']) ? $_REQUEST['key'] : $defaultKey;
$secret = isset($_REQUEST['secret']) ? $_REQUEST['secret'] : $defaultSecret;
$region = isset($_REQUEST['region']) ? $_REQUEST['region'] : 'us-east-1';
$streamName = isset($_REQUEST['streamName']) ? $_REQUEST['streamName'] : 'elasticsearch-stream-01';
$config = isset($_REQUEST['config']) ? json_decode($_REQUEST['config'], true) : require __DIR__ . '/../config/game-base.template.php';
$total = isset($_REQUEST['total']) ? $_REQUEST['total'] : 500;
$batchSize = isset($_REQUEST['batchSize']) ? $_REQUEST['batchSize'] : 400;

try {
    $kinesis = Aws\Kinesis\KinesisClient::factory(array(
        'credentials' => array(
            'key'    => $key,
            'secret' => $secret,
            'token' => $token,
        ),
        'region' => $region,
        'version' => 'latest',
    ));

    $result = null;
    if (isset($_REQUEST['submit'])) { 
        $dataRepository = new AwsBootcamp\DataRepository\Kinesis($kinesis, $streamName);
        $gen = new AwsBootcamp\Generator\DataSet($dataRepository);
        $dataSet = $gen->execute($config, $total, $batchSize);

        $result = PHP_EOL . 'Stats' . PHP_EOL;
        $result .= '---------' . PHP_EOL;
        $result .= 'Total generated : ' . sizeof($dataSet);
        $time = (microtime(true) - $start);
        $result .= PHP_EOL . 'Time : ' . $time . ' seconds' . PHP_EOL;
        $result .= 'Speed : ' . (sizeof($dataSet) / $time) . ' records/sec' . PHP_EOL . PHP_EOL;
    }
}
catch (\Exception $e) { 
    $result = 'Exception caught:' . PHP_EOL . $e->getMessage();
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DataGenerator</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    <link href="http://getbootstrap.com/docs/4.0/examples/cover/cover.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <style>
        #container textarea { margin:10px auto; width:90%; background-color:white; font-size:0.7em; text-align:left; }
        #container input.small { width:100px; margin-right:20px; }
        #container input.tiny { width:50px; margin-right:20px; }
    </style>
  </head>
  <body id="container">
    <div class="site-wrapper">
      <div class="site-wrapper-inner">
        <form action="?" method="post">
          <div class="form-group">
            <label for="exampleFormControlTextarea1">DataGenerator - Configuration</label>
            <textarea class="form-control form-control-sm" rows="27" name="config"><?php echo json_encode($config, JSON_PRETTY_PRINT); ?></textarea>
          </div>
          <div class="form-group">
          <?php if ($isLocal) { ?> 
            <small>Key</small>  
            <input class="small" type="text" name="key" value="<?php echo $key; ?>" placeholder="aws key"/>
            <small>Secret</small>  
            <input class="small" type="text" name="secret" value="<?php echo $secret; ?>" placeholder="aws secret"/>
          <?php  } ?>
            <small>Region</small>  
            <input class="small" type="text" name="region" value="<?php echo $region; ?>" placeholder="aws region"/>
            <small>StreamName</small>  
            <input class="small" type="text" name="streamName" value="<?php echo $streamName; ?>" placeholder="Kinesis streamName"/>
            <small>Total</small>  
            <input class="tiny" type="text" name="total" value="<?php echo $total; ?>" placeholder="total"/>
            <small>BatchSize</small>  
            <input class="tiny" type="text" name="batchSize" value="<?php echo $batchSize; ?>" placeholder="batchSize"/>
            <button type="submit" class="btn btn-primary" id="submit" name="submit">Generate</button>
          </div>
        </form>
        <?php if($result) { ?>
        <h4>Result</h4> 
        <textarea class="form-control form-control-sm" rows="10" id="result"><?php echo cli::$log . PHP_EOL . $result; ?></textarea>
        <?php } ?>
      </div>
    </div>
  </body>
</html>
