<?php

require_once("common.php");

$args = array();

//var_dump($config);

$arbitrage = new Arbitrage($args);

$arbitrage->getArbitrer()->getMarket('BitstampUSD')->getCurrentTicker();
$arbitrage->execCommand('watch');


?>