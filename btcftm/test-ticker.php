<?php

require_once("common.php");

$arbs = array();
$clist = $CL->getClientsList();


foreach($clist as $clientid => $client){
	$a = new Arbitrage($client, $args);
	$a->getArbitrer()->getMarket('BitstampUSD')->getCurrentTicker();
	$a->getArbitrer()->getMarket('MtGoxUSD')->getCurrentTicker();
	$arbs[$clientid] = $a;
}

?>