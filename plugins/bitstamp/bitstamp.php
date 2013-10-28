<?php
header('Content-type: application/json');
$ticker = file_get_contents('https://www.bitstamp.net/api/ticker/');
echo $ticker;
?>