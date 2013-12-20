<?php
require_once("orderbook.php");

class BidOrderBook extends OrderBook
{
	public function __construct($orders)
	{
		iLog("[MarketOrderBook] Creating new BID Order Book...");
		parent::__construct($orders);
		$this->type = "bid";	
	}
	
	protected function _sortOrderBook()
	{
		$this->_sortAndFormat(false);
	}
	
}
?>