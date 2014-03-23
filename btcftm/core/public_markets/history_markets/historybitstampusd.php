<?php
require_once("historymarket.php");

class HistoryBitstampUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "bitstamp";
		$this->depthUrl = "https://www.bitstamp.net/api/order_book/";
		$this->tickerUrl = "https://www.bitstamp.net/api/ticker/";
	}

	public function parseDepthJson($res)
	{
		return json_decode($res);
	}
	
	public function parseTickerRow($row){
		return $row;
	}
}

?>