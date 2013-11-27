<?php
require_once("traderbot.php");

class SimpleBot extends TraderBot
{
	public function __construct($client)
	{
		parent::__construct($client);
	}

	public function beginOpportunityFinder($depths)
	{
		parent::beginOpportunityFinder($depths);
	}

	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		parent::opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice);
	}
	
	public function endOpportunityFinder()
	{
		parent::endOpportunityFinder();
	}
}
?>