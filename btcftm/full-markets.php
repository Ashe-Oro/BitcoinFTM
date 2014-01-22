<?php
$noEchoLog = 1;
require_once("core/include.php");

$a = new Arbitrage();
$markets = $a->markets;
$tickers = array();
?>
<table>
<tr>
<th></th>
<?php
foreach($markets as $mkt) {
	echo "<th class='market'>{$mkt->name}</th>";
	$tickers[] = $mkt->getCurrentTicker();
}
?>
</tr>

<tr>
<td class="value">Last</td>
<?php
foreach($tickers as $t) {
	echo "<td>".$t->getLast()."</td>";
}
?>
</tr>

<tr>
<td class="value">High</td>
<?php
foreach($tickers as $t) {
	echo "<td>".$t->getHigh()."</td>";
}
?>
</tr>

<tr>
<td class="value">Low</td>
<?php
foreach($tickers as $t) {
	echo "<td>".$t->getLow()."</td>";
}
?>
</tr>

<tr>
<td class="value">Bid</td>
<?php
foreach($tickers as $t) {
	echo "<td>".$t->getBid()."</td>";
}
?>
</tr>

<tr>
<td class="value">Ask</td>
<?php
foreach($tickers as $t) {
	echo "<td>".$t->getAsk()."</td>";
}
?>
</tr>

<tr>
<td class="value">Volume</td>
<?php
foreach($tickers as $t) {
	echo "<td>".$t->getVolume()."</td>";
}
?>
</tr>


</table>