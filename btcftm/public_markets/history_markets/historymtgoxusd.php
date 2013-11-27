<?php
require_once('historymarket.php');

class HistoryMtGoxUSD extends HistoryMarket
{
	private $depthUrl = "http://data.mtgox.com/api/2/BTCUSD/money/depth";
	private $tickerUrl = "http://data.mtgox.com/api/1/BTCUSD/ticker";
	
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
		$this->depth = array('asks' => array('price' => 0, 'amount' => 0), 'bids' => array('price' => 0, 'amount' => 0));
	}

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
	
	public function getHistoryTickers($startDate, $endDate="")
	{
		global $DB;
		$tickers = NULL;
		
		if (is_string($startDate)){ $startDate = strtotime($startDate); }
		if (empty($endDate)){ $endDate = time(); }
		if (is_string($endDate)){ $endDate = strtotime($endDate); }
		
		if (is_int($startDate) && is_int($endDate)){
			iLog("[MtGoxUSD] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM mtgox_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						//$row['timestamp'] = $row['timestamp'] / 1000000; // convert microseconds to timestamp
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[MtGoxUSD] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[MtGoxUSD] ERROR: History ticker query failed: ".$e->getMessage());
			}
		}
		
		return $tickers;
	}
	
	public function getHistoryTicker($timestamp) {
		global $DB;
		$ticker = NULL;
		
		if (is_string($timestamp)){ $timestamp = strtotime($timestamp); }
		if(is_int($timestamp)){
			$qid = $DB->query("SELECT * FROM mtgox_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
			$result = $DB->fetch_array_assoc($qid);
			return new Ticker($result);
		}
	}
	
	public function getHistorySamples($startDate, $endDate="", $sampling="day")
	{
		global $DB;
		iLog("[MtGoxUSD] Getting history samples...");
	}
}
?>