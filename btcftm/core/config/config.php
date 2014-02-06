<?php
require_once("db_config.php");

$config = array();

$config['live'] = 0; // IMPORTANT! Turn this to true to activate LIVE trading!!!!!

// watch the following markets: ["MtGoxEUR", "BitcoinCentralEUR", "IntersangoEUR", "Bitcoin24EUR", "BitstampEUR", "BtceUSD", "MtGoxUSD", "BitfloorUSD", "BitstampUSD"]
$config['markets'] = array("MtGoxUSD", "BitstampUSD", "KrakenUSD", "BTCeUSD", "BitfinexUSD", "CryptoTradeUSD", "CampBXUSD");

// observers if any ["Logger", "TraderBot", "TraderBotSim", "HistoryDumper", "Emailer"]
$config['observers'] = array("Logger", "Emailer");

$config['marketExpirationTime'] = 120;  // in seconds: 2 minutes
$config['refreshRate'] = 20;
$config['errorLog'] = 1;
$config['echoLog'] = 1;

if (isset($noEchoLog)) {
	$config['echoLog'] = 0;
}

if (isset($noErrorLog)) {
	$config['errorLog'] = 0;
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
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

iLog("[Config] BTC FTM Configuration Loaded - MODE: ".($config['live'] ? "LIVE" : "TESTING"));

?>