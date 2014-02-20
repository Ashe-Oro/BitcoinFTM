<?php
header('Content-Type: application/json');
$noEchoLog = 1;
require_once("core/include.php");
require_once("core/public_markets/ticker/periodTicker.php");

$range = "-2 week";
$starttime = strtotime("-2 week");
$endtime = time();
$display = "avg";

if (isset($_GET['start'])){
  $starttime = (int) (is_int($_GET['start']) ? $_GET['start'] : strtotime($_GET['start']));
}
if (isset($_GET['end'])){
  $endtime = (int) (is_int($_GET['end']) ? (int) $_GET['end'] : strtotime($_GET['end']));
}

if (isset($_GET['range'])){
  $range = $_GET['range'];
  $starttime = (int) strtotime(urldecode($_GET['range']));
}
if (isset($_GET['disp'])){
  $display = $_GET['disp'];
}

if (strpos($range, "hour")){
  $period = PERIOD_1M;
}
if (strpos($range, "day")) {
   $int = intval(str_replace(" day", "", str_replace("-","", $range)));
   if ($int < 3) {
    $period = PERIOD_30M;
  } else {
    $period = PERIOD_1H;
  }
}
if (strpos($range, "week")) {
  $int = intval(str_replace(" week", "", str_replace("-","", $range)));
  if ($int < 3) {
    $period = PERIOD_1H;
  } else {
    $period = PERIOD_1D;
  }
}


$args = array('noclients' => 1, 'history' => 1);

$a = new Arbitrage(NULL, $args);
$a->setTimestamp(time(), 0);

$period = PERIOD_1H;

$args = array("start" => $starttime, "end" => $endtime, "period" => $period, "value" => $display);
$a->execCommand('chart-json', $args);
?>