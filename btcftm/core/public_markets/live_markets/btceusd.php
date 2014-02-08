<?php
require_once("livemarket.php");

class BTCeUSD extends LiveMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->depthUrl = "https://btc-e.com/api/2/btc_usd/depth";
		$this->tickerUrl = "https://btc-e.com/api/2/btc_usd/ticker";
		$this->table = "btce_btcusd";
	}

	protected function parseDepthJson($res)
	{
		return json_decode($res);
	}

	protected function parseTickerJson($res)
	{
		$json = json_decode($res);
		$data = $json->ticker;
		$data->timestamp = $data->updated;
		$data->ask = $data->sell;
		$data->bid = $data->buy;
		$data->volume = $data->vol_cur;	
		$ticker = new Ticker($data);
		$t = $ticker->getTickerArray();

		iLog("[{$this->name}] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
		return $ticker;
	}
}
?>