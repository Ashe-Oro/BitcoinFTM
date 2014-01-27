<h1>Arbitrage Exchange Matrix</h1>

<script language="javascript" type="text/javascript" src="js/matrix.js"></script>

<link rel="stylesheet" href="css/matrix.css" />


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
		$aname = str_replace("History", "", $askmarket);
		echo "<th class='bid'>{$aname}</th>";
	}
	?>
	</tr>

<?php
$fclone = $full;
foreach($full as $askmarket => $mx){
	$aname = str_replace("History", "", $askmarket);
	echo "<tr>";
	echo "<th class='ask'>{$aname}</th>";
	foreach($fclone as $bidmarket => $mx2){
		$bname = str_replace("History", "", $bidmarket);
		$m = isset($mx[$bidmarket]) ? $mx[$bidmarket] : NULL;
		if (!$m || $aname == $bname) {
			echo "<td class='matrix-cell'>----</td>";
		} else {
			//$class = ($m['profit'] > 0) ? 'pos' : 'neg';
			echo "<td class='matrix-cell' id='matrix-{$aname}-{$bname}'>...</td>";
		}
	}
	echo "</tr>";
}

//var_dump($full);
?>
</table>
	</div>
</div>