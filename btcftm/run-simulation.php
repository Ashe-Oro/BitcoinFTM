<?php
require_once("core/include.php");

$cList = new ClientsList(array("Deshman"));
$args = array("start" => "1-9-2013", "scale" => PERIOD_30M, "history" => 1);

$a = new Arbitrage($cList, $args);
$a->execCommand('sim', $args);
?>