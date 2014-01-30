<h1>Market Charts</h1>

<script language="javascript" type="text/javascript" src="js/charts.js"></script>
<link rel="stylesheet" href="css/charts.css" />

<div id="charts">
	<ul id="bitcoin-markets">
	<?php
	foreach($markets as $mkt) {
		$mname = $mkt->getName();
		if ($mname != "Kraken") { // kraken not currently supported at bitwisdom
			echo "<li id='btcmarket_{$mname}' class='bitcoin-market-chart'><a href='#'>{$mname}</a></li>";
		}
	}
	?>
	</ul>

	<div id="bitcoin-chart">
	<iframe src="http://bitcoinwisdom.com/markets/bitfinex/btcusd" width="100%" height="400"></iframe>
	</div>
</div>