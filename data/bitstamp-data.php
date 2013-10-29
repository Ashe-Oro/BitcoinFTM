<h2>Getting Data From Bitstamp API...</h2>
<?php

$json = file_get_contents('https://www.bitstamp.net/api/ticker/');
$obj = json_decode($json);

$high = $obj->{'high'};
$last = $obj->{'last'};
$timestamp = $obj->{'timestamp'};
$bid = $obj->{'bid'};
$volume = $obj->{'volume'};
$low = $obj->{'low'};
$ask = $obj->{'ask'};

echo "High: " . $high . "<br/>";
echo "Low: " . $low . "<br/>";
echo "Last " . $last . "<br/>";
echo "Bid " . $bid . "<br/>";
echo "Ask " . $ask . "<br/>";
echo "Volume " . $volume . "<br/>";
echo "Timestamp " . $timestamp . "<br/>";

?>