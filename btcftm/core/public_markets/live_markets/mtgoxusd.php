<?php
require_once('livemarket.php');

class MtGoxUSD extends LiveMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
		$this->depthUrl = "http://data.mtgox.com/api/2/BTCUSD/money/depth";
		$this->tickerUrl = "http://data.mtgox.com/api/1/BTCUSD/ticker";
		$this->table = "mtgox";
		$this->marketname = "MtGoxUSD";
	}

	protected function parseDepthJson($res)
	{
		$json = json_decode($res);
		if ($json->result == 'success') {
			return $json->data;
		}
		return NULL;
	}

	protected function parseTickerJson($res)
	{
		$json = json_decode($res);
				
		if ($json && isset($json->result) && $json->result == 'success' && isset($json->return)){
			$j = $json->return;
			$ticker = new Ticker($j->now, $j->high->value, $j->low->value, $j->last->value, $j->buy->value, $j->sell->value, $j->vol->value);
			$t = $ticker->getTickerArray();

			iLog("[{$this->marketname}] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
			return $ticker;
		} else {
			iLog("[{$this->marketname}] ERROR: JSON error - ".$json['error']);
			return NULL;
		}
	}
}
?>