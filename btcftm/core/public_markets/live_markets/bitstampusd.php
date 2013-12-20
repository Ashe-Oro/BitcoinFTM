<?php
require_once("livemarket.php");

class BitstampUSD extends LiveMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
		$this->depthUrl = "https://www.bitstamp.net/api/order_book/";
		$this->tickerUrl = "https://www.bitstamp.net/api/ticker/";
	}

	public function updateOrderBookData()
	{
		iLog("[BitstampUSD] Updating order book data...");
		$res = file_get_contents($this->depthUrl);
		//var_dump($res);
		try {
			$json = json_decode($res);
			$data = $json;
			//var_dump($data);
			$this->orderBook = $this->formatOrderBook($data);
			//var_dump($this->orderBook);
			iLog("[BitstampUSD] Order depth updated");
		} catch (Exception $e) {
			iLog("[BitstampUSD] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
		return $this->orderBook;
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
	
	public function getHistoryTickers($startDate, $endDate="")
	{
		global $DB;
		$tickers = NULL;
		
		if (is_string($startDate)){ $startDate = strtotime($startDate); }
		if (empty($endDate)){ $endDate = time(); }
		if (is_string($endDate)){ $endDate = strtotime($endDate); }
		
		if (is_int($startDate) && is_int($endDate)){
			iLog("[BitstampUSD] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM bitstamp_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[BitstampUSD] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[BitstampUSD] ERROR: History ticker query failed: ".$e->getMessage());
			}
		}
		
		return $tickers;
	}
	
	public function getHistoryTicker($timestamp) {
		global $DB;
		$ticker = NULL;
		
		if (is_string($timestamp)){ $timestamp = strtotime($timestamp); }
		if(is_int($timestamp)){
			$qid = $DB->query("SELECT * FROM bitstamp_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
			$result = $DB->fetch_array_assoc($qid);
			return new Ticker($result);
		}
	}
	
	public function getHistorySamples($startDate, $endDate="", $sampling="day")
	{
		global $DB;
		iLog("[BitstampUSD] Getting history samples...");
	}
}

?>