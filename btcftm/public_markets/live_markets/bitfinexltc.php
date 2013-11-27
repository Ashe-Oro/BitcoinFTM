<?php
require_once("market.php");

class BitfinexLTC extends Market
{	
	private $depthUrl = "https://api.bitfinex.com/v1/book/ltcbtc";
	private $tickerUrl = "https://api.bitfinex.com/v1/ticker/ltcbtc";

	public function __construct()
	{
		parent::__construct("LTC");
		//TODO This updateRate is a random guess... Find out real update rate
		$this->updateRate = 100;
	}

	public function updateDepth()
	{
		iLog("[BitfinexLTC] Updating order depth...");
		$res = file_get_contents($this->depthUrl);
		try {
			$json = json_decode($res);
			$data = $json;
			$this->depth = $this->formatDepth($data);
			iLog("[BitfinexLTC] Order depth updated");
		} catch (Exception $e) {
			iLog("[BitfinexLTC] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function sortAndFormat($l, $reverse)
	{
		$r = array();
		foreach($l as $i) {
			array_push($r, array('price' => (float) $i[0], 'amount' => (float) $i[1]));
		}
		usort($r, array("BitstampLTC", "comparePrice"));
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
		iLog("[BitfinexLTC] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$ticker = new Ticker($json);
			iLog("[BitfinexLTC] Current ticker - mid: {$ticker['mid']} last: {$ticker['last_price']} ask: {$ticker['ask']} bid: {$ticker['bid']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[BitfinexLTC] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
		}
	}
}
?>