<?php
require_once("historymarket.php");

class HistoryBTCeUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "btce_btcusd";
		$this->historyname = "HistoryBTCeUSD";
		$this->depthUrl = "https://btc-e.com/api/2/btc_usd/depth";
		$this->tickerUrl = "https://btc-e.com/api/2/btc_usd/ticker";
	}

	protected function parseDepthJson($res)
	{
		return json_decode($res);
	}
	
	protected function parseTickerRow($row){
		return $row;
	}
}

?>