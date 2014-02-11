<?php
require_once("historymarket.php");

class HistoryKrakenUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "kraken_btcusd";
		$this->depthUrl = "https://api.kraken.com/0/public/Depth?pair=XBTUSD";
		$this->tickerUrl = "https://api.kraken.com/0/public/Ticker?pair=XBTUSD";
	}

	public function parseDepthJson($res)
	{
		$json = json_decode($res);
		return $json->result->XXBTZUSD;
	}
	
	public function parseTickerRow($row){
		$row['volume'] = $row['volume'] * $row['last'];
		return $row;
	}
}

?>