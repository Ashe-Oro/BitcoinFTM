<?php
require_once("db_config.php");

$config = array();

$config['localhost'] = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

$config['live'] = 0; // IMPORTANT! Turn this to true to activate LIVE trading!!!!!

// observers if any ["Logger", "TraderBot", "TraderBotSim", "HistoryDumper", "Emailer"]
$config['observers'] = array("Logger", "Emailer");

$config['marketExpirationTime'] = 120;  // in seconds: 2 minutes
$config['refreshRate'] = 20;
$config['errorLog'] = 1;
$config['echoLog'] = 1;

$config['minify'] = 0;

if (isset($noEchoLog)) {
	$config['echoLog'] = 0;
}

if (isset($noErrorLog)) {
	$config['errorLog'] = 0;
}

if (isset($minify)) {
  $config['minify'] = $minify;
}

//require_once("clients_config.php");

function iLog($msg)
{
	global $config;
	if ($config['errorLog']) {
		error_log($msg);
	}
	if ($config['echoLog']) {
		echo $msg."<br />\n";
	}
}

function curl($url){
  global $config;
  if ($config['localhost']){
     try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
    } catch (Exception $e) {
      iLog("[CONFIG] ERROR: Couldn't open file via curl {$url}");
      return "";
    }
  } else { // for some reason CURL doesn't work on my staging server yet.....
    try {
      $str = file_get_contents($url);
      return $str;
    } catch (Exception $e) {
      iLog("[CONFIG] ERROR: Couldn't open file via file_get_contents {$url}");
      return "";
    }
  }
}

iLog("[Config] BTC FTM Configuration Loaded - MODE: ".($config['live'] ? "LIVE" : "TESTING"));

?>