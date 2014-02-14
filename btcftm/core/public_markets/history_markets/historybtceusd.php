<?php
require_once("historymarket.php");

class HistoryBTCeUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "btce_btcusd";
		$this->depthUrl = "https://btc-e.com/api/2/btc_usd/depth";
		$this->tickerUrl = "https://btc-e.com/api/2/btc_usd/ticker";
	}

	public function parseDepthJson($res)
	{
		return json_decode($res);
	}
	
	public function parseTickerRow($row){
		$row['volume'] = $row['volume'] / 100;
		return $row;
	}
}

?>