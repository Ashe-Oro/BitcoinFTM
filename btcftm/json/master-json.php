<?php
header('Content-Type: application/json');
$noEchoLog = 1;
require_once("core/include.php");

$args = array('noclients' => 1, 'noarbitrers' => 1, 'history' => 1);

$a = new Arbitrage(NULL, $args);
$a->setTimestamp(time(), 0);
$a->execCommand('json');
?>