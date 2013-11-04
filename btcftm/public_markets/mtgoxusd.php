<?php
require_once('market.php');

/**
 * CLASS: MtGoxUSD
 *
 * Public market functions for MtGox USD exchange
 */
class MtGoxUSD extends Market
{
	private $depthUrl = "http://data.mtgox.com/api/2/BTCUSD/money/depth";
	private $tickerUrl = "http://data.mtgox.com/api/1/BTCUSD/ticker";
	
	/**
	 * Creates a new Mt Gox USD Market
	 */
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
		$this->depth = array('asks' => array('price' => 0, 'amount' => 0), 'bids' => array('price' => 0, 'amount' => 0));
	}

	/**
	 * Updates the order book market depth
	 */
	public function updateDepth()
	{
		iLog("[MtGoxUSD] Updating order depth...");
		$url = "";
		$res = file_get_contents($this->depthUrl);
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
	
	/**
	 * Sorts order book ask/bid by price
	 *
	 * @param	{array}		l			array of asks or bids from order book
	 * @param	{boolean}	reverse		if true, sort in reverse (desc) order
	 * @return	{array}					sorted array
	 */
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

	/**
	 * Formats ask and bid depth array
	 *
	 * @param	{object}	depth	order book depth
	 * @return	{array}				array of asks and bids sorted by price (Asks sorted ASC, Bids sorted DESC)
	 */
	public function formatDepth($depth)
	{
		$bids = $this->sortAndFormat($depth->bids, true);
		$asks = $this->sortAndFormat($depth->asks, false);
		return array('asks' => $asks, 'bids' => $bids);
	}	
	
	/**
	 * Gets the current ticker for the MtGox USD exchange
	 *
	 * @return	{object}		Ticker object, or NULL if error
	 */
	public function getCurrentTicker()
	{
		iLog("[MtGoxUSD] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			//var_dump($json);
			
			if ($json && isset($json->result) && $json->result == 'success' && isset($json->return)){
				$j = $json->return;
				$ticker = new Ticker($j->now, $j->high->value, $j->low->value, $j->last->value, $j->buy->value, $j->sell->value, $j->vol->value);
				$t = $ticker->getTickerArray();

				iLog("[MtGoxUSD] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
				return $ticker;
			} else {
				iLog("[MtGoxUSD] ERROR: JSON error - ".$json['error']);
			}
			
		} catch (Exception $e) {
			iLog("[MtGoxUSD] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
		}
		return NULL;
	}
}
?>