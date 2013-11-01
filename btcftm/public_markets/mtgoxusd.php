<?php
require_once('market.php');

class MtGoxUSD extends Market
{
	private $depthUrl = "";
	private $tickerUrl = "";
	
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
		$this->depth = array('asks' => array('price' => 0, 'amount' => 0), 'bids' => array('price' => 0, 'amount' => 0));
	}

	public function updateDepth()
	{
		iLog("[MtGoxUSD] Updating order depth...");
		$url = "http://data.mtgox.com/api/2/BTCUSD/money/depth";
		$res = file_get_contents($url);
		try {
			$json = json_decode($res);
			if ($json->result == 'success') {
				$data = $json->data;
				$this->depth = $this->formatDepth($data);
				//var_dump($this->depth);
				iLog("[MtGoxUSD] Order Depth Updated");
			}
		} catch (Exception $e) {
			iLog("[MtGoxUSD] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function sortAndFormat($l, $reverse=false)
	{
		$r = array();
		foreach($l as $i) {
			array_push($r, array('price' => $i->price, 'amount' => $i->amount));
		}
		usort($r, array("MtGoxUSD", "comparePrice"));
		if ($reverse) {
			$r = array_reverse($r);
		}
		return $r;
	}

	public function formatDepth($depth)
	{
		$bids = $this->sortAndFormat($depth->bids, true);
		$asks = $this->sortAndFormat($depth->asks, false);
		return array('asks' => $asks, 'bids' => $bids);
	}	
	
	public function getCurrentTicker()
	{
		iLog("[MtGoxUSD] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$ticker = new Ticker($json);
			iLog("[MtGoxUSD] Current ticker - high: {$ticker['high']} low: {$ticker['low']} last: {$ticker['last']} ask: {$ticker['ask']} bid: {$ticker['bid']} volume: {$ticker['volume']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[MtGoxUSD] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
		}
	}
}
?>