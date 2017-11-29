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
$webConfig = require __DIR__ . '/../config/profiles/profiles.php';

class cli { public static $log = null; public static function log($m) { self::$log .= '[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL; } }

$ec2role = 'datagenRole';
$credentials = array('key' => null, 'secret' => null);
$json = array();
$isLocal = isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'localhost');
// If not locally deployed, then fetch credentials from instance role
if (!$isLocal) {
    $metadataRole = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/');
    // Fetch creds from ec2 metadata instance (if available)
    $creds = file_get_contents('http://169.254.169.254/latest/meta-data/iam/security-credentials/' . $metadataRole);
    $json = json_decode($creds, true);
} else { 
    $credentialsFile = __DIR__ . '/../config/aws/credentials.php';
    if (file_exists($credentialsFile)) { 
        $credentials = require $credentialsFile;
    } else { 
        die('Must provide a config/aws/credentials.php file');
    }
}

$defaultKey = isset($json['AccessKeyId']) ? $json['AccessKeyId'] : $credentials['key'];
$defaultSecret = isset($json['SecretAccessKey']) ? $json['SecretAccessKey'] : $credentials['secret'];
$token = isset($json['Token']) ? $json['Token'] : null;

$configProfile = isset($_REQUEST['configProfile']) ? $_REQUEST['configProfile'] : key($webConfig['configProfiles']);
$configSettings = $webConfig['configProfiles'][$configProfile];
$configFile = __DIR__ . '/../config/profiles/' . $configSettings['templateFolder'] . '/template.php';
foreach ($configSettings as $k => $v) { 
    $configSettings[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

$jsonConfig = isset($_REQUEST['config']) ? $_REQUEST['config'] : json_encode(require $configFile, JSON_PRETTY_PRINT);
$configSettings['config'] = json_decode($jsonConfig, true);
$configSettings['key'] = isset($_REQUEST['key']) ? $_REQUEST['key'] : $defaultKey;
$configSettings['secret'] = isset($_REQUEST['secret']) ? $_REQUEST['secret'] : $defaultSecret;
if ($token) { 
    $configSettings['token'] = $token;
}

try {
    $result = null;
    if (isset($_REQUEST['submit'])) { 
        
        if(!is_array($configSettings['config'])) { 
            throw new \Exception('Config must be a valid json array');
        }

        $factory = new AwsBootcamp\DataRepository\Factory($configSettings);
        $repository = $factory->getInstance($configSettings['implementation'], $configSettings);
        $gen = new AwsBootcamp\Generator\DataSet($repository);
        $dataSet = $gen->execute($configSettings['config'], $configSettings['total'], $configSettings['batchSize']);

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
        #container textarea { margin:0px auto; width:100%; background-color:white; font-size:0.7em; text-align:left; }
        #container .whitebg { background-color:#DDD; width:350px; }
        #container .thick { height: 30px; }
    </style>
  </head>
  <body id="container">
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
      <a class="navbar-brand" href="?">DataGen</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Configuration Profile</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">
                <?php
                foreach ($webConfig['configProfiles'] as $v => $data) {
                    echo '<a class="dropdown-item" href="?configProfile=' . $v . '">' . $v . '</a>';
                    if ($v == $configProfile) { 
                        echo '';
                    }
                }
                ?> 
            </div>
          </li>
        </ul>
      </div>
    </nav>
    <div class="site-wrapper">
      <div class="site-wrapper-inner">
         <div class="form-group">
            <label for="exampleFormControlTextarea1">DataGenerator <?php echo isset($configSettings['comment']) ? ' - ' . $configSettings['comment'] . ' (to ' . $configSettings['implementation'] . ')' : null; ?></label>
         </div>
        <div style="margin:30px auto; width:100%;">
        <form action="?" method="post">
          <div class="form-group col-sm-4" style="display:inline-block; vertical-align:top;">
            <textarea class="form-control form-control-sm" rows="28" name="config"><?php echo $jsonConfig; ?></textarea>
          </div>
          <div class="col-sm-4" style="display:inline-block; vertical-align:top; text-align:right;">
            <input type="hidden" name="configProfile" value="<?php echo $configProfile; ?>" />
            <input type="hidden" name="implementation" value="<?php echo $configSettings['implementation']; ?>" />
          <?php 
            foreach ($configSettings as $k => $v) { 
                if ($k == 'config' || $k == 'comment' || $v === null) 
                    continue;
          
                if (!$isLocal && ($k == 'key' || $k == 'secret' || $k == 'token')) 
                    continue;
          ?>
          <div class="form-group row col-sm-8">
            <label class="col-sm-6 col-form-label col-form-label-sm" for="<?php echo $k; ?>"><?php echo ucfirst($k); ?></label>
            <div class="col-sm-6">
                <input class="form-control-plaintext form-control-sm text-dark whitebg" type="text" id="<?php echo $k; ?>" name="<?php echo $k; ?>" value="<?php echo $v; ?>" placeholder="aws <?php echo $k; ?>"/>
            </div>
          </div>
          <?php } ?>
          <div class="form-group row">
            <button style="width:350px; margin-left:190px;" type="submit" class="btn btn-primary" id="submit" name="submit">Generate</button>
          </div>
          </div>
        </form>
        </div>
        <?php if($result) { ?>
        <h4>Result</h4> 
        <div style="margin:0px auto; width:100%;">
          <div class="form-group col-sm-8" style="display:inline-block; vertical-align:top;">
            <textarea class="form-control form-control-sm" rows="10" id="result"><?php echo cli::$log . PHP_EOL . $result; ?></textarea>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </body>
</html>
