<h1>Order Book Depths</h1>

<script language="javascript" type="text/javascript" src="js/orderbooks.js"></script>

<link rel="stylesheet" href="css/orderbooks.css" />


<div id="orderbooks-list">
<table>
<tr>
<?php
//var_dump($ARB->mob);
foreach($markets as $mkt) {
	$mname = str_replace("USD", "", str_replace("History","", $mkt->name));
	echo "<th class='marketname marketname-{$mname}' colspan='4'>{$mname}</th>";
}
?>	
</tr>

<tr>
<?php
foreach($markets as $mkt) {
	$mname = str_replace("USD", "", str_replace("History","",$mkt->name));
	echo "<th class='asks-list asks-list-{$mname}' colspan='2'>Asks</th>";
	echo "<th class='bids-list bids-list-{$mname}' colspan='2'>Bids</th>";
}
?>
</tr>

<tr>
<?php
foreach($markets as $mkt) {
	$mname = str_replace("USD", "", str_replace("History","",$mkt->name));
	$asks = $ARB->mob->getMarketAskOrderBook($mkt->name);
	$orders = $asks->getOrders();
	echo "<td class='asks-list-price ask-list-price-{$mname}'>";
	foreach($orders as $a){
		echo "$".$a->getPrice()."<br />";
	}
	echo "</td>";
	echo "<td class='asks-list-amount ask-list-amount-{$mname}'>";
	foreach($orders as $a){
		echo $a->getAmount()."<br />";
	}
	echo "</td>";

	$bids = $ARB->mob->getMarketBidOrderBook($mkt->name);
	//echo $mkt->name.":";var_dump($bids);
	$orders = $bids->getOrders();
	echo "<td class='bids-list-price bid-list-price-{$mname}'>";
	foreach($orders as $a){
		echo "$".$a->getPrice()."<br />";
	}
	echo "</td>";
	echo "<td class='bids-list-amount bid-list-amount-{$mname}'>";
	foreach($orders as $a){
		echo $a->getAmount()."<br />";
	}
	echo "</td>";
}
?>
</tr>
</table>
</div>