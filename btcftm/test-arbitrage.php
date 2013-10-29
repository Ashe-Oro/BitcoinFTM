<?php

require_once("common.php");

$args = array();

//var_dump($config);

$arbitrage = new Arbitrage($args);
$arbitrage->execCommand('watch');


?>