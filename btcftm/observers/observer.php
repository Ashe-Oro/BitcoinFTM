<?php

abstract class Observer
{
	public $name;
	
	public function __construct()
	{
		$this->name = get_class($this);
	}
	
	abstract public function beginOpportunityFinder($depths);
	
	abstract public function endOpportunityFinder();

	abstract public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $weightedBuyPrice, $weightedSellPrice);
}

?>