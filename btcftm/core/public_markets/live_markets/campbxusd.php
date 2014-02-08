<?php
require_once("livemarket.php");

class CampBXUSD extends LiveMarket
{	
	public function __construct()
	{
		parent::__construct("USD");
		
		$this->updateRate = 20;
		$this->depthUrl = "http://campbx.com/api/xdepth.php";
		$this->tickerUrl = "http://campbx.com/api/xticker.php";
		$this->table = "campbx_btcusd";
	}

	protected function parseDepthJson($res)
	{
		$json = json_decode($res);
		$data = $json;
		//var_dump($json);
		$data->asks = $json->Asks;
		$data->bids = $json->Bids;
		return $data;
	}

	protected function parseTickerJson($res)
	{
		$json = json_decode($res);
		$data = $json;
		
		$data->volume = 0;
		$data->ask = $data->{"Best Ask"};
		$data->bid = $data->{"Best Bid"};
		$data->last = $data->{"Last Trade"};
		$data->high = $data->last;
		$data->low = $data->last;
		$data->timestamp = time();
			
		$ticker = new Ticker($data);
		$t = $ticker->getTickerArray();

		iLog("[{$this->name}] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
		return $ticker;
	}
}
?>