<?php
require_once("livemarket.php");

class BitfinexUSD extends LiveMarket
{	
	public function __construct()
	{
		parent::__construct("USD");
		//TODO This updateRate is a random guess... Find out real update rate
		$this->updateRate = 100;
		$this->depthUrl = "https://api.bitfinex.com/v1/book/btcusd";
		$this->tickerUrl = "https://api.bitfinex.com/v1/ticker/btcusd";
		$this->table = "bitfinexusd";
		$this->marketname = "BitfinexUSD";
	}

	protected function parseDepthJson($res)
	{
		return json_decode($res);
	}

	protected function parseTickerJson($res)
	{
		$json = json_decode($res);
		$json->last = $json->last_price;
		$json->high = max($json->last_price, $json->mid); // doesn't have high or low in JSON, so make it up
		$json->low = min($json->last_price, $json->mid);
		$json->volume = 0;
			
		$ticker = new Ticker($json);
		$t = $ticker->getTickerArray();

		iLog("[{$this->marketname}] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
		return $ticker;
	}
}
?>