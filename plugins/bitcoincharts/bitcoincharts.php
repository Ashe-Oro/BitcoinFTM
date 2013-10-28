<?php
header('Content-type: application/json');
$ticker = file_get_contents('http://api.bitcoincharts.com/v1/markets.json');
echo $ticker;
?>