<h1>Markets Overview</h1>

<div id="market-list">
<?php /*
	<div class="updating">
	Updating... this may take a few seconds...
	</div>
	<div class="waiting">
	Waiting 15 seconds...
	</div>
*/ ?>
	<div id="full-markets">

<?php
$mktnames = array();
?>
<table>
<tr>
<th></th>
<?php
foreach($markets as $mkt) {
	$mktnames[$mkt->name] = str_replace("USD", "", str_replace("History", "", $mkt->name));
	echo "<th class='market'>{$mktnames[$mkt->name]}</th>";
}
?>
</tr>

<?php
$vals = array('last','high','low','ask','bid','sma10','sma25','volume');
foreach($vals as $v){
	echo "<tr>";
	echo "<td class='value'>".ucfirst($v)."</td>";
	foreach($markets as $mkt) {
		echo "<td class='mkt-value-row mkt-{$v}' id='mkt-{$v}-{$mktnames[$mkt->name]}'><div class='val'>...</div><div class ='perc'></div></td>";
	}
	echo "</tr>";
}
?>

</table>
	</div>
</div>