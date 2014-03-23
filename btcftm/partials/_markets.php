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
	echo "<th class='market mkt-bg-{$mkt->mname}' id='market-th-{$mkt->mname}'>{$mkt->mname}</th>";
}
?>
</tr>

<?php
$vals = array('ask', 'bid', 'last','high','low','sma10','sma25','volume');
foreach($vals as $v){
	echo "<tr>";
	echo "<td class='value'>".ucfirst($v)."</td>";
	foreach($markets as $mkt) {
		echo "<td class='mkt-value-row mkt-{$v}' id='mkt-{$v}-{$mkt->mname}'><div class='val'>...</div><div class ='perc'></div></td>";
	}
	echo "</tr>";
}
?>

</table>
	</div>
</div>