<?php

require_once("common.php");

$args = array();
$clist = $CL->getClientsList();
//var_dump($config);

$arbs = array();
foreach($clist as $clientid => $client){
	if ($client->isActive() && $client->isTrading()) {
		$a = new Arbitrage($client, $args);
		$a->execCommand('watch');
		$arbs[$clientid] = $a;
	}
}
?>