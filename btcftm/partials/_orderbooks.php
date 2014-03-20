<h1>Order Book Depths</h1>

<div id="orderbooks-controls">
	<div class="orderbooks-item">
		<label for="orderbooks-btcvol">Bitcoin Volume: </label>
		<input type="text" name="orderbooks-btcvol" id="orderbooks-btcvol" size="20" value="1.0" />
	</div>
</div>

<div id="orderbooks-list">
<table>
<tr>
<?php
//var_dump($ARB->mob);
foreach($markets as $mkt) {
	echo "<th class='marketname marketname-{$mkt->mname} mkt-bg-{$mkt->mname}' id='orderbooks-marketname-{$mkt->mname}' colspan='4'>{$mkt->mname}";
	echo "&nbsp;<input type='checkbox' class='orderbook-toggle' id='orderbook-toggle-{$mkt->mname}' value='1' checked='checked' /></th>";
}
?>	
</tr>

<tr>
<?php
foreach($markets as $mkt) {
	echo "<th class='asks-list asks-list-{$mkt->mname} mkt-bg-dark1-{$mkt->mname}' colspan='2'>Asks <span class='list-wval' id='ask-list-wval-{$mkt->mname}'>...</span></th>";
	echo "<th class='bids-list bids-list-{$mkt->mname} mkt-bg-dark2-{$mkt->mname}' colspan='2'>Bids <span class='list-wval' id='bid-list-wval-{$mkt->mname}'>...</span></th>";
}
?>
</tr>

<tr id="orderbooks-data">
<?php
echo $ARB->mob->printOrderBooks(true);
/*
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
*/
?>
</tr>
</table>
</div>