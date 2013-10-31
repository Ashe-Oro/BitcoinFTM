<h2>Adding Live data to DB...</h2>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("utils/ExchangeDbUtil.php");

//TODO This either needs to be set to run on a cron
//OR made to run at a certain interval (but not exceeding max allowed by any exchange server)

$exchangeDb = new ExchangeDbUtil();

$temp = $exchangeDb->addToHistory("mtgox");
echo "MtGox..." . $temp;

$temp = $exchangeDb->addToHistory("bitstamp");
echo "Bitstamp..." . $temp;
?>