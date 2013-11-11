<?php
require_once("observer.php");

class Logger extends Observer
{
	public function __construct($client)
	{
		parent::__construct($client);
	}

	public function beginOpportunityFinder($depths)
	{
		
	}

	public function endOpportunityFinder()
	{
		
	}

	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		$perc = round($perc, 4);
		$profit = round($profit, 4);
		 iLog("[Logger] TRADE profit {$profit}USD with volume {$volume}BTC - Buy {$kask['name']} @{$buyprice} - Sell {$kbid['name']} @{$sellprice} - ~{$perc}%");
	}
}
?>