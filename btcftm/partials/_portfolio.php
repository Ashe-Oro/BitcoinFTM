<h1>Portfolio Management</h1>

<table>
<tr>
<th>Market</th><th>API Info</th><th>Balances</th><th>Add Funds</th></tr>
<?php
$curlist = $currencies->getCurrencyList();
foreach($markets as $mkt) {
	$pmarket = $client->getPrivateMarket($mkt->mname);
	$key  = ($pmarket) ? $pmarket->getAPIKey() : "";
	$secret = ($pmarket) ? $pmarket->getAPISecret() : "";

	echo "<tr id='portfolio-{$mkt->mname}'>";
	echo "<td class='portfolio-market'><h3>{$mkt->mname}</h3></td>";
	echo "<td class='portfolio-api-data'>";
	echo "<div class='portfolio-key'>API Key: <span class='portfolio-key-val'>{$key}</span></div>";
	echo "<div class='portfolio-secret'>API Secret: <span class='portfolio-key-secret'>{$secret}</span></div>";
	echo "</td>";

	echo "<td class='portfolio-balances'>";
	foreach($curlist as $abbr => $cur) {
		if ($mkt->supports($abbr)){
			$c = ($pmarket) ? $currencies->printCurrency($pmarket->getBalance($abbr), $abbr) : $currencies->printCurrency(0, $abbr);
			echo "<div class='portfolio-{$abbr}'>{$abbr}: <span class='portfolio-value' id='portfolio-value-{$mkt->mname}'>{$c}</span></div>";
		}
	}
	echo "</td>";

	echo "<td class='portfolio-finance'>";
  foreach($curlist as $abbr => $cur) {
    if ($mkt->supports($abbr)){
      echo "<div class='portfolio-{$abbr}'><input type='button' name='finance-portfolio-{$abbr}' id='finance-portfolio-{$abbr}-{$mkt->mname}' value='Add {$abbr} at {$mkt->mname}' /></div>";
    }
  }
	echo "</td>";
	echo "</tr>";
}
?>
</table>

<div id="portfolio-edit">
	<div class='portfolio-key'><input type='text' size='40' maxlength='40' value='' /></div>
	<div class='portfolio-secret'><input type='text' size='40' maxlength='40' value='' /></div>
	<div class='portfolio-update'><input type='submit' value='Update' /></div>
</div>