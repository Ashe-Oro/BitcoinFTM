<h2>Adding Orderbook data to DB...</h2>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("../utils/ExchangeDbUtil.php");

$exchangeDb = new ExchangeDbUtil();

$temp = $exchangeDb->addToOrderbooks("mtgox", 5);
echo "<br/>MtGox Orderbook Added..." . $temp;

$temp = $exchangeDb->addToOrderbooks("bitstamp", 5);
echo "<br/>Bitstamp Orderbook Added..." . $temp;

$temp = $exchangeDb->addToOrderbooks("btce_btcusd", 5);
echo "<br/>Btc-e Orderbook Added...(BTCUSD)" . $temp;

$temp = $exchangeDb->addToOrderbooks("bitfinex_btcusd", 5);
echo "<br/>Bitfinex Orderbook Added...(BTCUSD)" . $temp;

$temp = $exchangeDb->addToOrderbooks("kraken_btcusd", 5);
echo "<br/>Kraken Orderbook Added...(BTCUSD)" . $temp;

$temp = $exchangeDb->addToOrderbooks("cryptotrade_btcusd", 5);
echo "<br/>CryptoTrade Orderbook Added...(BTCUSD)" . $temp;

$temp = $exchangeDb->addToOrderbooks("campbx_btcusd", 5);
echo "<br/>CampBX Orderbook Added...(BTCUSD)" . $temp;

?>