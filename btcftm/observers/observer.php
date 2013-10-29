<?php

abstract class Observer
{
	public function __construct($metaclass)
	{
	}
	
	abstract public function beginOpportunityFinder($depths);
	
	abstract public function endOpportunityFinder();

	abstract public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $weightedBuyPrice, $weightedSellPrice);
}

?>