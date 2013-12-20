<?php

require_once("core/include.php");

$args = array();
$clist = $CL->getClientsList();
//var_dump($config);

$arbs = array();
foreach($clist as $clientid => $client){
	$a = new Arbitrage($client, $args);
	$a->execCommand('watch');
	$arbs[$clientid] = $a;
}


?>