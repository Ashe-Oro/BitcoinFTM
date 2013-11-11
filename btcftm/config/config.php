<?php
require_once("db_config.php");

$config = array();

// watch the following markets: ["MtGoxEUR", "BitcoinCentralEUR", "IntersangoEUR", "Bitcoin24EUR", "BitstampEUR", "BtceUSD", "MtGoxUSD", "BitfloorUSD", "BitstampUSD"]
$config['markets'] = array("MtGoxUSD", "BitstampUSD");

// observers if any ["Logger", "TraderBot", "TraderBotSim", "HistoryDumper", "Emailer"]
$config['observers'] = array("Logger", "TraderBot");

$config['marketExpirationTime'] = 120;  // in seconds: 2 minutes
$config['refreshRate'] = 20;
$config['errorLog'] = 1;
$config['echoLog'] = 1;

require_once("clients_config.php");

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

?>