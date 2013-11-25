<h2>Adding Orderbook data to DB...</h2>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("../utils/ExchangeDbUtil.php");

$exchangeDb = new ExchangeDbUtil();

$temp = $exchangeDb->addToOrderbooks("mtgox", 10);
echo "<br/>MtGox Orderbook Added..." . $temp;

$temp = $exchangeDb->addToOrderbooks("bitstamp", 10);
echo "<br/>Bitstamp Orderbook Added..." . $temp;

?>