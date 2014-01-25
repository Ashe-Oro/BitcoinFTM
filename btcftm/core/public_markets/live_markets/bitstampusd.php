<?php
require_once("livemarket.php");

class BitstampUSD extends LiveMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
		$this->depthUrl = "https://www.bitstamp.net/api/order_book/";
		$this->tickerUrl = "https://www.bitstamp.net/api/ticker/";
		$this->table = "bitstamp";
		$this->marketname = "BitstampUSD";
	}

	protected function parseDepthJson($res)
	{
		return json_decode($res);
	}

	protected function parseTickerJson($res)
	{
		$json = json_decode($res);
		$ticker = new Ticker($json);
		$t = $ticker->getTickerArray();

		iLog("[{$this->marketname}] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
		return $ticker;
	}
}

?>