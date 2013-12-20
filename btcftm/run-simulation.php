<?php
require_once("core/include.php");

$start = "2013-11-25";
if (isset($_GET['start'])) { $start = $_GET['start']; }

$cList = new ClientsList(array("Deshman"));
$args = array("start" => $start, "period" => PERIOD_30M, "history" => 1);

$config['echoLog'] = 0;
$a = new Arbitrage($cList, $args);
$config['echoLog'] = 1;
$a->execCommand('sim', $args);
?>