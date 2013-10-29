<?php
require_once("observer.php");

class Logger extends Observer
{
	public function __create()
	{
		$clients['mtgoxusd'] = new PrivateMtGoxUSD();
		$clients['bitstampusd'] = new PrivateBitstampUSD();
	}

	public function beginOpportunityFinder($depths)
	{
		
	}

	public function endOpportunityFinder()
	{
		
	}

	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		 error_log("[Logger] profit: {$profit} USD with volume: {$volume} BTC - buy at {$buyPrice} ({$kask}) sell at {$sellPrice} ({$kbid}) ~{$perc}%");
	}
}
?>