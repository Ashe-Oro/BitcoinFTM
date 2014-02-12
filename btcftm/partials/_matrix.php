<h1>Arbitrage Exchange Matrix</h1>

<div id="enter-the-matrix">
<?php /*	<div class="updating">
	Updating... this may take a few seconds...
	</div>
	<div class="waiting">
	Waiting 15 seconds...
	</div>
*/ ?>
	<?php $full = $ARB->mob->getFullExchangeMatrix(); ?>
	<div id="full-matrix">

	<table>
	<tr>
	<th class="ask">ASK MARKETS</th>
	<th class="bid" style="text-align: center;" colspan="<?php echo count($full); ?>">BID MARKETS</th>
	</tr>
	<tr>
	<?php
	echo "<th class='ask'></th>";
	foreach($full as $askmarket => $mx){
		echo "<th class='bid'>".sanitizeMarketName($askmarket)."</th>";
	}
	?>
	</tr>

<?php
$fclone = $full;
foreach($full as $askmarket => $mx){
	$aname = sanitizeMarketName($askmarket);
	echo "<tr>";
	echo "<th class='ask'>{$aname}</th>";
	foreach($fclone as $bidmarket => $mx2){
		$bname = sanitizeMarketName($bidmarket);
		$m = isset($mx[$bidmarket]) ? $mx[$bidmarket] : NULL;
		if (!$m || $aname == $bname) {
			echo "<td class='matrix-cell'><span class='matrix-cell-value'>----</span><span class='matrix-cell-perc'></span></td>";
		} else {
			//$class = ($m['profit'] > 0) ? 'pos' : 'neg';
			echo "<td class='matrix-cell' id='matrix-{$aname}-{$bname}' data-ask='{$aname}' data-bid='{$bname}'><span class='matrix-cell-value'>...</span><span class='matrix-cell-perc'></span></td>";
		}
	}
	echo "</tr>";
}

//var_dump($full);
?>
</table>
	</div>
<h3>Click any highlighted Arbitrage Opportunity above to begin Arbitrage!</h3>
</div>