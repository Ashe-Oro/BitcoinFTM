<?php
require_once("historymarket.php");

class HistoryCryptoTradeUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "cryptotrade_btcusd";
		$this->depthUrl = "https://crypto-trade.com/api/1/depth/btc_usd";
		$this->tickerUrl = "https://crypto-trade.com/api/1/ticker/btc_usd";
	}

	protected function parseDepthJson($res)
	{
		return json_decode($res);
	}
	
	protected function parseTickerRow($row){
		//	var_dump($row);
		return $row;
	}
}

?>