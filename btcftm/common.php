<?php
require_once("config.php");
require_once("arbitrage.php");
require_once("observers/traderbot.php");
require_once("observers/logger.php");
require_once("private_markets/privatebitstampusd.php");
require_once("private_markets/privatemtgoxusd.php");
require_once("public_markets/bitstampusd.php");
require_once("public_markets/mtgoxusd.php");

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