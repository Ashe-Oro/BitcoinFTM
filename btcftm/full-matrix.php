<?php
$noEchoLog = 1;
require_once("core/include.php");

$a = new Arbitrage();
$full = $a->mob->getFullExchangeMatrix();
?>
<table>
<tr>
<th class="ask">ASK MARKETS</th>
<th class="bid" style="text-align: center;" colspan="<?php echo count($full); ?>">BID MARKETS</th>
</tr>
<tr>
<?php
echo "<th class='ask'></th>";
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