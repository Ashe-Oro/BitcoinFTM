<?php
require_once("livemarket.php");

class CryptoTradeUSD extends LiveMarket
{	
	public function __construct()
	{
		parent::__construct("USD");
		//TODO This updateRate is a random guess... Find out real update rate
		$this->depthUrl = "https://crypto-trade.com/api/1/depth/btc_usd";
		$this->tickerUrl = "https://crypto-trade.com/api/1/ticker/btc_usd";
		$this->table = "cryptotrade_btcusd";
	}

	protected function parseDepthJson($res)
	{
		return json_decode($res);
	}

	protected function parseTickerJson($res)
	{
		$json = json_decode($res);
		$data = $json->data;
		$data->volume = $data->vol_usd;
		$data->ask = $data->min_ask;
		$data->bid = $data->max_bid;
		$data->timestamp = time();
		
		$ticker = new Ticker($data);
		$t = $ticker->getTickerArray();

		iLog("[{$this->name}] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
		return $ticker;
	}
}
?>