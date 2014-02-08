<?php
$noEchoLog = 0;
require_once("core/include.php");

$a = new Arbitrage();
$markets = $a->markets;

foreach($markets as $mkt){
  $sma10 = $mkt->getSMA(10);
  $sma25 = $mkt->getSMA(25);
  echo "{$mkt->mname}:<br />";
  var_dump($sma10);
  echo "<br />";
  var_dump($sma25);
  echo "<br /><br />";
}

?>