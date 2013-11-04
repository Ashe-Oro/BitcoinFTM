<?php
require_once("market.php");

/**
 * CLASS: BitstampUSD
 *
 * Public market functions for Bitstamp USD exchange
 */
class BitstampUSD extends Market
{
	public $updateRate;
	private $depthUrl = "https://www.bitstamp.net/api/order_book/";
	private $tickerUrl = "https://www.bitstamp.net/api/ticker/";

	/**
	 * Creates a new Bitstamp USD Market
	 */
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
	}

	/**
	 * Updates the order book market depth
	 */
	public function updateDepth()
	{
		iLog("[BitstampUSD] Updating order depth...");
		$res = file_get_contents($this->depthUrl);
		try {
			$json = json_decode($res);
			$data = $json;
			$this->depth = $this->formatDepth($data);
			iLog("[BitstampUSD] Order depth updated");
		} catch (Exception $e) {
			iLog("[BitstampUSD] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	/**
	 * Sorts order book ask/bid by price
	 *
	 * @param	{array}		l			array of asks or bids from order book
	 * @param	{boolean}	reverse		if true, sort in reverse (desc) order
	 * @return	{array}					sorted array
	 */
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
	
	public function getCurrentTicker()
	{
		iLog("[BitstampUSD] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$ticker = new Ticker($json);
			$t = $ticker->getTickerArray();

			iLog("[BitstampUSD] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[MtGoxUSD] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
		}
	}
}

?>