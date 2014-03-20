<div id="portfolio-perc">
<fieldset>
<legend>Portfolio Percentages</legend>
<table>
<tr>
<th class="marketname">Market</th>
<th colspan="2" class="usd">USD</th>
<th colspan="2" class="btc">BTC</th>
<th colspan="3" class="total">Total</th>
</tr>
<tr>
<th class="marketname"></th>
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

  echo "<tr class='account-mkt' id='perc-mkt-{$mname}'>";
  echo "<td class='account-mkt-name' id='perc-mkt-name-{$mname}'>{$mname}</td>";
  echo "<td class='account-mkt-usdbal' id='perc-mkt-usdbal-{$mname}'>...</td>";
  echo "<td class='account-mkt-usd2btc' id='perc-mkt-usd2btc-{$mname}'>...</td>";
  echo "<td class='account-mkt-btcbal' id='perc-mkt-btcbal-{$mname}'>...</td>";
  echo "<td class='account-mkt-btc2usd' id='perc-mkt-btc2usd-{$mname}'>...</td>";
  echo "<td class='account-mkt-usdtotal' id='perc-mkt-usdtotal-{$mname}'>...</td>";
  echo "<td class='account-mkt-btctotal' id='perc-mkt-btctotal-{$mname}'>...</td>";
  echo "</tr>";
}

echo "<tr class='account-mkt' id='perc-mkt-total'>";
echo "<td class='account-mkt-name' id='perc-mkt-name-total'>TOTAL</td>";
echo "<td class='account-mkt-usdbal' id='perc-mkt-usdbal-total'>...</td>";
echo "<td class='account-mkt-usd2btc' id='perc-mkt-usd2btc-total'>...</td>";
echo "<td class='account-mkt-btcbal' id='perc-mkt-btcbal-total'>...</td>";
echo "<td class='account-mkt-btc2usd' id='perc-mkt-btc2usd-total'>...</td>";
echo "<td class='account-mkt-usdtotal' id='perc-mkt-usdtotal-total'>...</td>";
echo "<td class='account-mkt-btctotal' id='perc-mkt-btctotal-total'>...</td>";
echo "</tr>";

?>
</table>
</fieldset>
</div>