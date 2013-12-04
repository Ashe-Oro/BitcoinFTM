<?php

abstract class Observer
{
	public $name;
	protected $client;
	
	public function __construct($client)
	{
		$this->client = $client;
		$this->name = get_class($this);
	}
	
	abstract public function beginOpportunityFinder($markets, $mob);
	abstract public function opportunityFinder($markets, $mob);
	abstract public function endOpportunityFinder($markets, $mob);

	abstract public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $weightedBuyPrice, $weightedSellPrice);
}

?>