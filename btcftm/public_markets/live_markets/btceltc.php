<?php
require_once("market.php");

class BtceLTC extends Market
{	
	private $depthUrl = "https://btc-e.com/api/2/btc_usd/depth";
	private $tickerUrl = "https://btc-e.com/api/2/btc_usd/ticker";

	public function __construct()
	{
		parent::__construct("LTC");
		//TODO This updateRate is a random guess... Find out real update rate
		$this->updateRate = 100;
	}

	public function updateDepth()
	{
		iLog("[BtceLTC] Updating order depth...");
		$res = file_get_contents($this->depthUrl);
		try {
			$json = json_decode($res);
			$data = $json;
			$this->depth = $this->formatDepth($data);
			iLog("[BtceLTC] Order depth updated");
		} catch (Exception $e) {
			iLog("[BtceLTC] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function sortAndFormat($l, $reverse)
	{
		$r = array();
		foreach($l as $i) {
			array_push($r, array('price' => (float) $i[0], 'amount' => (float) $i[1]));
		}
		usort($r, array("BtceLTC", "comparePrice"));
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
		iLog("[BtceLTC] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$ticker = new Ticker($json);
			iLog("[BtceLTC] Current ticker - high: {$ticker['high']} low: {$ticker['low']} avg: {$ticker['avg']} vol: {$ticker['vol']} last: {$ticker['last_price']} sell: {$ticker['sell']} buy: {$ticker['buy']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[BtceLTC] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
		}
	}
}
?>