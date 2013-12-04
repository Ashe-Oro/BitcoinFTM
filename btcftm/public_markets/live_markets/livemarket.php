<?php
require_once("./core/public_markets/market.php");

abstract class LiveMarket extends Market
{
	protected $depthUrl = "";
	protected $tickerUrl = "";
	
	public function __construct($currency)
	{
		parent::__construct($currency);
		$this->live = true;
	}
}
?>