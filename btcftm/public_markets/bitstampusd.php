<?php
require_once("market.php");

class BitstampUSD extends Market
{
	public $updateRate;

	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
	}

	public function updateDepth()
	{
		$url = 'https://www.bitstamp.net/api/order_book/';
		$res = file_get_contents($url);
		try {
			$json = json_decode($res);
			$data = $json;
			$this->depth = $this->formatDepth($data);
		} catch (Exception $e) {
			iLog("[BitstampUSD] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function sortAndFormat($l, $reverse)
	{
		$r = array();
		foreach($l as $i) {
			array_push($r, array('price' => (float) $i[0], 'amount' => (float) $i[1]));
		}
		usort($r, array("BitstampUSD", "comparePrice"));
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
}

?>