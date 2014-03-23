<?php
$noEchoLog = 1;
require_once("core/include.php");

$args = array('noclients' => 1);
$a = new Arbitrage(NULL, $args);
echo $a->mob->printOrderBooks(true);
?>