<h2>Gomparing MtGox and Bitstamp Data using API...</h2>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include_once("../classes/mtgox.php");
include_once("../classes/bitstamp.php");

//Get MtGox Data
$mtgox = new MtGox();
$ticker = $mtgox->getTicker();

$high = $ticker->{'high'};
$last = $ticker->{'last'};
$timestamp = $ticker->{'timestamp'};
$volume = $ticker->{'volume'};
$low = $ticker->{'low'};
//Bid and Ask are Bitstamp related.... not sure if we have MtGox equivs but they are in the Ticker object as of now
//$bid = $ticker->{'bid'};
//$ask = $ticker->{'ask'};
echo "<h3>MtGox Data</h3>";
echo "High: " . $high . "<br/>";
echo "Low: " . $low . "<br/>";
echo "Last " . $last . "<br/>";
//echo "Bid " . $bid . "<br/>";
//echo "Ask " . $ask . "<br/>";
echo "Volume " . $volume . "<br/>";
echo "Timestamp " . $timestamp . "<br/>";

//Get BitStamp Data
$bitstamp = new BitStamp();
$ticker = $bitstamp->getTicker();

$high = $ticker->{'high'};
$last = $ticker->{'last'};
$timestamp = $ticker->{'timestamp'};
$bid = $ticker->{'bid'};
$volume = $ticker->{'volume'};
$low = $ticker->{'low'};
$ask = $ticker->{'ask'};

echo "<h3>Bitstamp Data</h3>";
echo "High: " . $high . "<br/>";
echo "Low: " . $low . "<br/>";
echo "Last " . $last . "<br/>";
echo "Bid " . $bid . "<br/>";
echo "Ask " . $ask . "<br/>";
echo "Volume " . $volume . "<br/>";
echo "Timestamp " . $timestamp . "<br/>";

?>