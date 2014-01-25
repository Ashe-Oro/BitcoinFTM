<?php
require_once('historymarket.php');

class HistoryBitfinexUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "bitfinex_btcusd";
		$this->historyname = "HistoryBitfinexUSD";
		$this->depthUrl = "https://api.bitfinex.com/v1/book/btcusd";
		$this->tickerUrl = "https://api.bitfinex.com/v1/ticker/btcusd";
	}

	protected function parseDepthJson($res)
	{
		return json_decode($res);
	}

	protected function parseTickerRow($row)
	{
		$row['high'] = max($row['last'], $row['mid']);
		$row['low'] = min($row['last'], $row['mid']);
		$row['volume'] = 0;

		return $row;
	}
}
?>