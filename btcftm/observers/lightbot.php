<?php
require_once("traderbot.php");

class Lightbot extends TraderBot
{
	public function __construct($client)
	{
		parent::__construct($client);
	}

	public function beginOpportunityFinder($markets, $mob)
	{
		parent::beginOpportunityFinder($markets, $mob);
	}
	
	public function opportunityFinder($markets, $mob)
	{
		parent::opportunityFinder($markets, $mob);
	}
	
	public function endOpportunityFinder($markets, $mob)
	{
		parent::endOpportunityFinder($markets, $mob);
	}

	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		parent::opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice);
	}
}
?>