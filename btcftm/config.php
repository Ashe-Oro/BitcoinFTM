<?php
$config = array();

// watch the following markets:
// ["MtGoxEUR", "BitcoinCentralEUR", "IntersangoEUR", "Bitcoin24EUR", "BitstampEUR", "BtceUSD", "MtGoxUSD", "BitfloorUSD", "BitstampUSD"]
$config['markets'] = array("MtGoxUSD", "BitstampUSD");

// observers if any
// ["Logger", "TraderBot", "TraderBotSim", "HistoryDumper", "Emailer"]
$config['observers'] = array("Logger", "TraderBot");

$config['marketExpirationTime'] = 120;  // in seconds: 2 minutes

$config['refreshRate'] = 20;

$config['errorLog'] = 1;
$config['echoLog'] = 1;

/** Trader Bot Config
 * Access to Private APIs
 */
 
 /***** THESE ARE ASHE'S PERSONAL KEYS! DO NOT RELEASE THESE LIVE! ****/
 /** These will need to pull from the clients DB to work on a per-client basis **/
$config['mtgox_key'] = "eccf3b13-75bb-44e6-8f3f-66a577d05d8d";
$config['mtgox_secret'] = "R1XAuUGamakYoFSDdKMLjy8hrhqZyfzJTo/gPsdd8ogt7XlpimZbTKHDE6IDM5c0idVJbcNeWUTnyzDgdUcIKg==";
$config['mtgox_clientid'] = 0;

$config['bitstamp_key'] = "drRD82T04GZqYMJP3zxTXZh3i4HWX1Sm";
$config['bitstamp_secret'] = "pTO2lm83ogiUiAweuU6yy6sURTAM4J9m";
$config['bitstamp_clientid'] = 73820;

/**** END PERSONAL KEYS ***/

// SafeGuards
$config['maxTxVolume'] = 10;  		// max amount of BTC / trade
$config['minTxVolume'] = 1; 		// min amount of BTC / trade
$config['balanceMargin'] = 0.05;  	// 5%
$config['profitThresh'] = 1;  		// min profit in USD / trade
$config['percThresh'] = 2 ;			// min % profit in USD / trade

// Emailer Observer Config
$config['smtpHost'] = 'FIXME';
$config['smtpLogin'] = 'FIXME';
$config['smtpPasswd'] = 'FIXME';
$config['smtpTrom'] = 'FIXME';
$config['smtpTo'] = 'FIXME';


?>