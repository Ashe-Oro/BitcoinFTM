<?php
require_once("historymarket.php");

class HistoryBitstampUSD extends HistoryMarket
{
	public $updateRate;
	private $depthUrl = "https://www.bitstamp.net/api/order_book/";
	private $tickerUrl = "https://www.bitstamp.net/api/ticker/";

	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
	}

	public function updateOrderBookData()
	{
		global $DB;
		
		if ($this->period) {
			iLog("[HistoryBitstampUSD] Updating order depth for TS: {$this->timestamp} P: {$this->period}...");
			try{
				$res = $DB->query("SELECT * FROM bitstamp_orderbook WHERE timestamp <= {$this->timestamp} ORDER BY timestamp DESC LIMIT 1");
				if ($row = $DB->fetch_array_assoc($res)){
					var_dump($row);
				}
			} catch (Exception $e) {
				iLog("[HistoryBitstampUSD] ERROR: historical orderbook error - ".$e->getMessage());
			}
		} else {
			$res = file_get_contents($this->depthUrl);
			try {
				$json = json_decode($res);
				$data = $json;
				$this->orderBook = $this->formatOrderBook($data);
				iLog("[HistoryBitstampUSD] Order depth updated");
			} catch (Exception $e) {
				iLog("[HistoryBitstampUSD] ERROR: historical orderbook error - ".$e->getMessage());
			}
		}
	}
	
	public function getCurrentTicker()
	{
		global $DB;
		
		iLog("[HistoryBitstampUSD] Getting current ticker for TS: {$this->timestamp} P: {$this->period}...");
		try {
			$res = $DB->query("SELECT * FROM bitstamp_".$this->getPeriodTable()." WHERE timestamp <= {$this->timestamp} ORDER BY timestamp DESC LIMIT 1");
			if($row = $DB->fetch_array_assoc($res)){
				$ticker = new Ticker($row);
				$t = $ticker->getTickerArray();
				iLog("[HistoryBitstampUSD] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
				return $ticker;
			} else {
				iLog("[HistoryBitstampUSD] ERROR: no historical ticker found for for TS: {$this->timestamp} P: {$this->period}");
			}
			
		} catch (Exception $e) {
			iLog("[HistoryBitstampUSD] ERROR: historical ticker error - ".$e->getMessage());
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
			iLog("[HistoryBitstampUSD] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM bitstamp_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[HistoryBitstampUSD] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[HistoryBitstampUSD] ERROR: History ticker query failed: ".$e->getMessage());
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
		iLog("[HistoryBitstampUSD] Getting history samples...");
	}
}

?>