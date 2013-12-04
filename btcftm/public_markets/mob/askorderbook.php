<?php
require_once("orderbook.php");

class AskOrderBook extends OrderBook
{
	public function __construct($orders)
	{
		iLog("[MarketOrderBook] Creating new ASK Order Book...");
		//var_dump($orders);
		parent::__construct($orders);
		$this->type = "ask";	
	}
	
	protected function _sortOrderBook()
	{
		$this->_sortAndFormat(true);
	}
	
}
?>