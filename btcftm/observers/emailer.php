<?php
require_once("observer.php");

class Emailer extends Observer
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
		// email shit
	}
}
?>