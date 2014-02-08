<div id="account-balance">
<fieldset>
<legend>Account Balances</legend>
<table>
<tr>
<th class="marketname">Market</th>
<th colspan="3" class="price">Price</th>
<th colspan="2" class="usd">USD</th>
<th colspan="2" class="btc">BTC</th>
<th colspan="3" class="total">Total</th>
</tr>
<tr>
<th class="marketname"></th>
<th class="price">Last</th><th class="price">Ask</th><th class="price">Bid</th>
<th class="usd">Avail</th><th class="usd">-&gt;BTC</th>
<th class="btc">Avail</th><th class="btc">-&gt;USD</th>
<th class="total">in USD</th><th class="total">in BTC</th>
<?php
$totalusdbal = 0;
$totalbtcbal = 0;
foreach($markets as $mkt){
	$mname = $mkt->mname;
	//var_dump($client);  
	$usdbal = $client->getMarketBalance($mname, "USD");
	$btcbal = $client->getMarketBalance($mname, "BTC");

	if ($usdbal == -1 && $btcbal == -1){
		$usd = "--";
		$btc = "--";
	} else {
		$usd = '$'.$usdbal;
		$btc = $btcbal." BTC";
		$totalusdbal += $usdbal;
		$totalbtcbal += $btcbal;
	}

	echo "<tr class='account-mkt' id='account-mkt-{$mname}'>";
	echo "<td class='account-mkt-name' id='account-mkt-name-{$mname}'>{$mname}</td>";
	echo "<td class='account-mkt-price' id='account-mkt-price-{$mname}'>...</td>";
	echo "<td class='account-mkt-ask' id='account-mkt-ask-{$mname}'>...</td>";
	echo "<td class='account-mkt-bid' id='account-mkt-bid-{$mname}'>...</td>";
	echo "<td class='account-mkt-usdbal' id='account-mkt-usdbal-{$mname}' data-usdbal='{$usdbal}'>".$ARB->printCurrency($usdbal, "USD")."</td>";
	echo "<td class='account-mkt-usd2btc' id='account-mkt-usd2btc-{$mname}'>...</td>";
	echo "<td class='account-mkt-btcbal' id='account-mkt-btcbal-{$mname}' data-btcbal='{$btcbal}'>".$ARB->printCurrency($btcbal, "BTC")."</td>";
	echo "<td class='account-mkt-btc2usd' id='account-mkt-btc2usd-{$mname}'>...</td>";
	echo "<td class='account-mkt-usdtotal' id='account-mkt-usdtotal-{$mname}'>...</td>";
	echo "<td class='account-mkt-btctotal' id='account-mkt-btctotal-{$mname}'>...</td>";
	echo "</tr>";
}

echo "<tr class='account-mkt' id='account-mkt-total'>";
echo "<td class='account-mkt-name' id='account-mkt-name-total'>TOTAL</td>";
echo "<td class='account-mkt-price' id='account-mkt-price-total'></td>";
echo "<td class='account-mkt-ask' id='account-mkt-ask-total'></td>";
echo "<td class='account-mkt-bid' id='account-mkt-bid-total'></td>";
echo "<td class='account-mkt-usdbal' id='account-mkt-usdbal-total'>".$ARB->printCurrency($totalusdbal, "USD")."</td>";
echo "<td class='account-mkt-usd2btc' id='account-mkt-usd2btc-total'>...</td>";
echo "<td class='account-mkt-btcbal' id='account-mkt-btcbal-total'>".$ARB->printCurrency($totalbtcbal, "BTC")."</td>";
echo "<td class='account-mkt-btc2usd' id='account-mkt-btc2usd-total'>...</td>";
echo "<td class='account-mkt-usdtotal' id='account-mkt-usdtotal-total'>...</td>";
echo "<td class='account-mkt-btctotal' id='account-mkt-btctotal-total'>...</td>";
echo "</tr>";
?>
</table>
</fieldset>
</div>