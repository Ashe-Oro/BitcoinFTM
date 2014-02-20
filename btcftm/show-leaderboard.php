<?php
$noEchoLog = 1;
require_once("core/include.php");

$args = array("nomob"=>true, "history"=>1);
$a = new Arbitrage(NULL, $args);
$cList = $a->clients;
$markets = $a->markets;

$usd = 0;
$btc = 0;
$gameid = 1;

foreach($cList as $c){
  if ($c->getGameID() == $gameid) {
    $p = $c->getPortfolio();
    if ($p){
      $bals = $p->getBalances();
      $usd = 0;
      $btc = 0;
      echo "<h2>".$c->getName()."</h2>";
      foreach($bals as $mname => $b) {
        echo "<b>{$mname}</b>: ".printCurrency($b['usd'], 'USD').' '.printCurrency($b['btc'], 'BTC')."</p>";
        $usd += $b['usd'];
        $btc += $b['btc'];
      }
      echo "<h3>Total: ".printCurrency($usd, 'USD').' '.printCurrency($btc, 'BTC')."</h3>";
    }
  }
}
?>