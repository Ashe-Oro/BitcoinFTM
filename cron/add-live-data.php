<h2>Adding Live data to DB...</h2>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("../utils/ExchangeDbUtil.php");

$exchangeDb = new ExchangeDbUtil();

?>
<h3>BTC/USD</h3>
<?php
$temp = $exchangeDb->addToTicker("mtgox");
echo "MtGox Added..." . $temp;

$temp = $exchangeDb->addToTicker("bitstamp");
echo "<br/>Bitstamp Added..." . $temp;

$temp = $exchangeDb->addToTicker("btce_btcusd");
echo "<br/>Btce Added (BTCUSD)..." . $temp;

$temp = $exchangeDb->addToTicker("bitfinex_btcusd");
echo "<br/>Bitfinex Added (BTCUSD)..." . $temp;
?>
<h3>LTC/BTC</h3>
<?php
$temp = $exchangeDb->addToTicker("bitfinex_ltcbtc");
echo "<br/>Bitfinex Added (LTCBTC)..." . $temp;

$temp = $exchangeDb->addToTicker("btce_ltcbtc");
echo "<br/>Btce Added..." . $temp;
?>