<?php
$noEchoLog = 1;
require_once("core/include.php");

$a = new Arbitrage();
$full = $a->mob->getFullExchangeMatrix();
$matrix = $a->mob->getExchangeMatrix();
?>
<h2>Best Opportunities</h2>
<table>
<tr><th>Ask Market</th><th>Bid Market</th><th>Profit USD/BTC</th></tr>
<?php
foreach($matrix as $askMarket => $op){
	echo "<tr id='{$askMarket}_op'><td>{$askMarket}:</td><td>{$op['market']}</td><td>{$op['profit']}</td></tr>";
}
?>
</table>

<h2>Full Matrix</h2>
<p><span class="ask">ASK MARKETS</span> | <span class="bid">BID MARKETS</span></p>
<table>
<tr>
<?php
echo "<th></th>";
foreach($full as $askmarket => $mx){
	echo "<th class='bid'>{$askmarket}</th>";
}
?>
</tr>

<?php
$fclone = $full;
foreach($full as $askmarket => $mx){
	echo "<tr>";
	echo "<th class='ask'>{$askmarket}</th>";
	foreach($fclone as $bidmarket => $mx2){
		$m = isset($mx[$bidmarket]) ? $mx[$bidmarket] : NULL;
		if (!$m || $askmarket == $bidmarket) {
			echo "<td>----</td>";
		} else {
			$class = ($m['profit'] > 0) ? 'pos' : 'neg';
			echo "<td class='{$class}'>{$m['profit']}</td>";
		}
	}
	echo "</tr>";
}

//var_dump($full);
?>
</table>