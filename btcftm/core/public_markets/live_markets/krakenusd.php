<?php
require_once("livemarket.php");

class KrakenUSD extends LiveMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->depthUrl = "https://api.kraken.com/0/public/Depth?pair=XBTUSD";
		$this->tickerUrl = "https://api.kraken.com/0/public/Ticker?pair=XBTUSD";
		$this->table = "kraken_btcusd";
	}

	protected function parseDepthJson($res)
	{
		$json = json_decode($res);
		return $json->result->XXBTZUSD;
	}

	protected function parseTickerJson($res)
	{
		$json = json_decode($res);
		$data = $json->result->XXBTZUSD;		// refer to https://www.kraken.com/help/api#get-ticker-info
		//var_dump($data);
		$jData = array(	'ask' => $data->a[0],
						'bid' => $data->b[0],
						'last' => $data->c[0],
						'low' => $data->l[0],
						'high' => $data->h[0],
						'timestamp' => time(),
						'volume' => $data->v[1] * $data->p[1]
					);
		
		$ticker = new Ticker($jData);
		$t = $ticker->getTickerArray();

		iLog("[{$this->name}] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
		return $ticker;
	}

}
?>