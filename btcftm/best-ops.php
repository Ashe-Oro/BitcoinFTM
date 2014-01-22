<?php
$noEchoLog = 1;
require_once("core/include.php");

$a = new Arbitrage();
$matrix = $a->mob->getExchangeMatrix();
?>
<table>
<tr><th>Ask Market</th><th>Bid Market</th><th>Profit USD/BTC</th></tr>
<?php
foreach($matrix as $askMarket => $op){
	echo "<tr id='{$askMarket}_op'><td>{$askMarket}:</td><td>{$op['market']}</td><td>{$op['profit']}</td></tr>";
}
?>
</table>