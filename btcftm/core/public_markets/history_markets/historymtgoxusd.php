<?php
require_once('historymarket.php');

class HistoryMtGoxUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "mtgox";
		$this->historyname = "HistoryMtGoxUSD";
		$this->depthUrl = "http://data.mtgox.com/api/2/BTCUSD/money/depth";
		$this->tickerUrl = "http://data.mtgox.com/api/1/BTCUSD/ticker";
	}

	protected function parseDepthJson($res)
	{
		$json = json_decode($res);
		if ($json->result == 'success') {
			return $json->data;
		}
		return NULL;
	}

	protected function parseTickerRow($row)
	{
		return $row;
	}
}
?>