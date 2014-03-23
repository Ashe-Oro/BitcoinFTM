<?php
require_once("livemarket.php");

class BTCeLTC extends LiveMarket
{	
	public function __construct()
	{
		parent::__construct("LTC");
		//TODO This updateRate is a random guess... Find out real update rate
		$this->updateRate = 100;
		$this->depthUrl = "https://btc-e.com/api/2/ltc_btc/depth";
		$this->tickerUrl = "https://btc-e.com/api/2/ltc_btc/ticker";
	}

	public function updateOrderBookData()
	{
		iLog("[BTCeLTC] Updating order depth...");
		$res = file_get_contents($this->depthUrl);
		try {
			$json = json_decode($res);
			$data = $json;
			$this->orderBook = $this->formatOrderBook($data);
			iLog("[BTCeLTC] Order depth updated");
		} catch (Exception $e) {
			iLog("[BTCeLTC] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function getCurrentTicker()
	{
		iLog("[BTCeLTC] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$ticker = new Ticker($json['ticker']);
			$t = $ticker->getTickerArray();
			iLog("[BTCeLTC] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[BTCeLTC] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
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
			iLog("[BTCeLTC] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM btceltc_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[BTCeLTC] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[BTCeLTC] ERROR: History ticker query failed: ".$e->getMessage());
			}
		}
		
		return $tickers;
	}
	
	public function getHistoryTicker($timestamp="") {
		global $DB;
		$ticker = NULL;
		
		if (empty($timestamp)) { $timestamp = time(); }
if (is_string($timestamp)){ $timestamp = strtotime($timestamp); }
		if(is_int($timestamp)){
			$qid = $DB->query("SELECT * FROM btceltc_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
			$result = $DB->fetch_array_assoc($qid);
			return new Ticker($result);
		}
	}
	
	public function getHistorySamples($startDate, $endDate="", $sampling="day")
	{
		global $DB;
		iLog("[BTCeLTC] Getting history samples...");
	}
}
?>