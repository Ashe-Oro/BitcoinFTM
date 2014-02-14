<?php
header('Content-Type: application/json');
$starttime = strtotime("-14 day");
$endtime = time();
if (isset($_GET['start'])){
  $starttime = (int) $_GET['start'];
}
if (isset($_GET['end'])){
  $endtime = (int) $_GET['end'];
}

$noEchoLog = 1;
require_once("core/include.php");

$args = array('noclients' => 1, 'history' => 1);

$a = new Arbitrage(NULL, $args);
$a->setTimestamp(time(), 0);

$period = PERIOD_1H;

$args = array("start" => $starttime, "end" => $endtime, "period" => $period);
$a->execCommand('chart-json', $args);
?>