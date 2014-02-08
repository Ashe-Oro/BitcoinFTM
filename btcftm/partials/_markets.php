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

<tr>
<td class="value">Last</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-last' id='mkt-last-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>

<tr>
<td class="value">High</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-high' id='mkt-high-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>

<tr>
<td class="value">Low</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-low' id='mkt-low-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>

<tr>
<td class="value">Bid</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-bid' id='mkt-bid-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>

<tr>
<td class="value">Ask</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-ask' id='mkt-ask-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>

<tr>
<td class="value">SMA10</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-sma10' id='mkt-sma10-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>


<tr>
<td class="value">SMA25</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-sma25' id='mkt-sma25-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>

<tr>
<td class="value">Volume</td>
<?php
foreach($markets as $mkt) {
	echo "<td class='mkt-vol' id='mkt-vol-{$mktnames[$mkt->name]}'>...</td>";
}
?>
</tr>




</table>
	</div>
</div>