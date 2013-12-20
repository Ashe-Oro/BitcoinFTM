<?php
require_once("observer.php");

class Emailer extends Observer
{
	public function __construct($client)
	{
		parent::__construct($client);
	}

	public function beginOpportunityFinder($markets, $mob) { }
	public function opportunityFinder($markets, $mob) { }
	public function endOpportunityFinder($markets, $mob) { }

	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		// email shit
	}
}
?>