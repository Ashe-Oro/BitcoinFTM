<h1>Order Book Depths</h1>

<div id="orderbooks-list">
<table>
<tr>
<?php
//var_dump($ARB->mob);
foreach($markets as $mkt) {
	echo "<th class='marketname marketname-{$mkt->mname}' colspan='4'>{$mkt->mname}</th>";
}
?>	
</tr>

<tr>
<?php
foreach($markets as $mkt) {
	echo "<th class='asks-list asks-list-{$mkt->mname}' colspan='2'>Asks</th>";
	echo "<th class='bids-list bids-list-{$mkt->mname}' colspan='2'>Bids</th>";
}
?>
</tr>

<tr>
<?php
foreach($markets as $mkt) {
	$asks = $ARB->mob->getMarketAskOrderBook($mkt->mname);
	$orders = ($asks) ? $asks->getOrders() : array();
	echo "<td class='asks-list-price ask-list-price-{$mkt->mname}'>";
	foreach($orders as $a){
		echo "$".$a->getPrice()."<br />";
	}
	echo "</td>";
	echo "<td class='asks-list-amount ask-list-amount-{$mkt->mname}'>";
	foreach($orders as $a){
		echo $a->getAmount()."<br />";
	}
	echo "</td>";

	$bids = $ARB->mob->getMarketBidOrderBook($mkt->mname);
	//echo $mkt->name.":";var_dump($bids);
	$orders = ($bids) ? $bids->getOrders() : array();
	echo "<td class='bids-list-price bid-list-price-{$mkt->mname}'>";
	foreach($orders as $a){
		echo "$".$a->getPrice()."<br />";
	}
	echo "</td>";
	echo "<td class='bids-list-amount bid-list-amount-{$mkt->mname}'>";
	foreach($orders as $a){
		echo $a->getAmount()."<br />";
	}
	echo "</td>";
}
?>
</tr>
</table>
</div>