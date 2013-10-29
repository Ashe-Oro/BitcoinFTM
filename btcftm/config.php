<?php
$config = array();

// watch the following markets:
// ["MtGoxEUR", "BitcoinCentralEUR", "IntersangoEUR", "Bitcoin24EUR", "BitstampEUR", "BtceUSD", "MtGoxUSD", "BitfloorUSD", "BitstampUSD"]
$config['markets'] = array("MtGoxUSD", "BitstampUSD", "BtceUSD");

// observers if any
// ["Logger", "TraderBot", "TraderBotSim", "HistoryDumper", "Emailer"]
$config['observers'] = array("Logger");

$config['marketExpirationTime'] = 120;  // in seconds: 2 minutes

$config['refreshRate'] = 20;

$config['errorLog'] = 1;
$config['echoLog'] = 1;

/** Trader Bot Config
 * Access to Private APIs
 */
$config['mtgox_key'] = "FIXME";
$config['mtgox_secret'] = "FIXME";

$config['bitstamp_username'] = "FIXME";
$config['bitstamp_password'] = "FIXME";

// SafeGuards
$config['maxTxVolume'] = 10;  		// in BTC
$config['minTxVolume'] = 1; 		// in BTC
$config['balanceMargin'] = 0.05;  	// 5%
$config['profitThresh'] = 1;  		// in USD
$config['percThresh'] = 2 ;			// in %

// Emailer Observer Config
$config['smtpHost'] = 'FIXME';
$config['smtpLogin'] = 'FIXME';
$config['smtpPasswd'] = 'FIXME';
$config['smtpTrom'] = 'FIXME';
$config['smtpTo'] = 'FIXME';


?>