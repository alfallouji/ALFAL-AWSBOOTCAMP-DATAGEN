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
$configSettings['key'] = isset($_REQUEST['key']) ? $_REQUEST['key'] : $defaultKey;
$configSettings['secret'] = isset($_REQUEST['secret']) ? $_REQUEST['secret'] : $defaultSecret;
if ($token) { 
    $configSettings['token'] = $token;
}

foreach ($configSettings as $k => $v) { 
    $configSettings[$k] = isset($_REQUEST[$k]) ? $_REQUEST[$k] : $v;
}

$configFile = __DIR__ . '/../config/templates/' . $configSettings['templateFolder'] . '/template.php';
$jsonConfig = isset($_REQUEST['config']) && !isset($_REQUEST['loadTemplate']) ? $_REQUEST['config'] : json_encode(require $configFile, JSON_PRETTY_PRINT);
$configSettings['config'] = json_decode($jsonConfig, true);
$loop = isset($_REQUEST['loop']) ? $_REQUEST['loop'] : false;
$templateFolders = array();
$dirs = array_filter(glob(__DIR__ . '/../config/templates/*'), 'is_dir');
foreach ($dirs as $dir) { 
    $parts = explode(DIRECTORY_SEPARATOR, $dir);
    $templateFolders[] = array_pop($parts);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="http://getbootstrap.com/docs/4.0/examples/cover/cover.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <link href="css/jsoneditor.min.css" rel="stylesheet" type="text/css">
    <script src="js/jsoneditor.min.js"></script>
    <style>
        #container .divtext { margin:0px auto; width:100%; background-color:white; font-size:0.7em; text-align:left; }
        #container .whitebg { background-color:#DDD; width:250px; }
        #container .thick { height: 30px; }
        #container #config a { color:blue; }
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
                    echo '<a class="dropdown-item" href="?configProfile=' . $v . '"';
                    if ($v == $configProfile) { 
                        echo ' style="background-color:#BBB;"';
                    }
                    echo '">' . $v . '</a>';
                }
                ?> 
            </div>
          </li>
        </ul>
      </div>
      <?php if ($loop && isset($_REQUEST['submit'])) { ?>
      <label style="margin:0px 10px 5px 0px;" class="badge badge-pill badge-primary" id="counter"></label> 
      <?php } ?>
      <label><?php echo isset($configSettings['comment']) ? $configProfile . ' | ' . $configSettings['comment'] . ' (to ' . $configSettings['implementation'] . ')' : null; ?></label>
    </nav>
    <div class="site-wrapper" style="width:100%;">
      <div class="site-wrapper-inner" style="width:100%;">
        <div style="margin:100px auto 30px auto; width:100%;">
        <form action="?" method="post" id="frm" name="frm">
          <div class="form-group col-sm-4" style="display:inline-block;">
            <div class="divtext form-control form-control-sm" style="height:500px;" name="configJson" id="configJson"></div>
            <div class="form-group row col-sm-12" style="margin-top:10px;">
                <div class="col-sm-12">
            <?php
                echo '<select id="templateFolder" name="templateFolder" style="width:120px;" class="form-control-plaintext form-control-sm text-dark whitebg">';
                foreach($templateFolders as $templateFolder) {
                    echo '<option value="' . $templateFolder . '"';
                    if ($templateFolder == $configSettings['templateFolder']) { 
                        echo ' selected="selected"';
                    }
                    echo '>' . $templateFolder . '</option>';
                }
                echo '</select>';
?>
               <button type="submit" class="btn btn-primary" id="loadTemplate" name="loadTemplate">Load template</button>
               </div>
            </div>

          </div>
          <div class="col-sm-4" style="display:inline-block; vertical-align:top; text-align:right;">
			<input type="hidden" name="config" id="config" value="" />
            <input type="hidden" name="configProfile" value="<?php echo $configProfile; ?>" />
            <input type="hidden" name="implementation" value="<?php echo $configSettings['implementation']; ?>" />
            <?php 
            foreach ($configSettings as $k => $v) { 
                if ($k == 'config' || $k == 'comment' || $k == 'interval' || $k == 'templateFolder' || $v === null) 
                    continue;
                if (!$isLocal && ($k == 'key' || $k == 'secret' || $k == 'token')) 
                    continue;
            ?>
            <div class="form-group row col-sm-12">
                <label class="col-sm-4 col-form-label col-form-label-sm" for="<?php echo $k; ?>"><?php echo ucfirst($k); ?></label>
                <div class="col-sm-8">
                    <input class="form-control-plaintext form-control-sm text-dark whitebg" type="text" 
                        id="<?php echo $k; ?>" name="<?php echo $k; ?>" value="<?php echo $v; ?>" placeholder="aws <?php echo $k; ?>"/>
                </div>
            </div>
          <?php } ?>
          <div class="form-group row col-sm-12">
            <label class="col-sm-4"></label>
            <div class="col-sm-8">
                <button style="width:250px;" type="submit" class="btn btn-primary" id="submit" name="submit">Generate</button>
            </div>
          </div>

          <div class="form-group row col-sm-12">
            <div class="col-sm-4"></div>
            <div class="col-sm-8" style="font-size:0.8em;">
                <input class="tiny" type="checkbox" id="loop" name="loop" placeholder="loop" value="1" <?php if ($loop) { echo 'checked="checked"'; } ?> />
                <label for="loop">Send every</label>
                <input style="margin:0px 5px 0px 5px;width:60px;font-size:0.8em;height:20px;" type="text" id="interval" name="interval" value="<?php echo $configSettings['interval']; ?>"/> (ms)
            </div>
          </div>
          </div>
        </form>
        </div>
        <?php if($result) { ?>
        <h4>Result</h4> 
        <div style="margin:0px auto; width:100%;">
          <div class="form-group col-sm-8" style="display:inline-block; vertical-align:top;">
            <div class="divtext form-control form-control-sm" id="result"><pre style="text-shadow:none;padding:20px;"><?php echo cli::$log . PHP_EOL . $result; ?></pre></div>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </body>
</html>
<script>
	var interval = null;
	$(document).ready(function() {
	<?php if ($loop && isset($_REQUEST['submit'])) { ?>
		var cpt = <?php echo $configSettings['interval']/1000; ?>;
		interval = setInterval(function() {
			if (cpt ==  0) { 
				refreshPage();
				clearInterval(interval);
			}     
			if (cpt > 0) {   
				$('#counter').html('Resend in ' + cpt );
				--cpt;
			}
		}, 1000);

		$('#loop').change(function() {
			if(!this.checked) {
				$('#counter').html('Resend cancelled');
				clearInterval(interval);
			}
		});
	<?php } ?> 
		function refreshPage() {
			$('#counter').html('<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>');
			$('#submit').click();
		}

		$('#frm').on('submit', function(e){
            var json = JSON.stringify(editor.get(), null, 2);
            $('#config').val(json);
			this.submit();
		});
	});

	// create the editor
	var container = document.getElementById("configJson");
	var options = {'mode': 'code', 'modes': ['code', 'form', 'tree']};
	var editor = new JSONEditor(container, options);
	var json = <?php echo $jsonConfig; ?>;
    editor.set(json);
    editor.expandAll();
</script>
