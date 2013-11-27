<?php
require_once("./core/public_markets/market.php");

abstract class HistoryMarket extends Market
{
	public function __construct($currency)
	{
		parent::__construct($currency);
		$this->live = true;
	}
}
?>