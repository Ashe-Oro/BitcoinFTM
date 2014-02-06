<?php
require_once("./core/public_markets/market.php");

abstract class LiveMarket extends Market
{
	protected $depthUrl = "";
	protected $tickerUrl = "";
	protected $marketname = "LiveMarket";
	protected $table = "";
	
	public function __construct($currency)
	{
		parent::__construct($currency);
		$this->live = true;
	}

	abstract protected function parseDepthJson($res);
	abstract protected function parseTickerJson($res);

	public function updateOrderBookData()
	{
		iLog("[{$this->marketname}] Updating order book data...");
		$url = "";
		try {
			$res = curl($this->depthUrl);
			//$res = file_get_contents($this->depthUrl);
			$data = $this->parseDepthJson($res);
			$this->orderBook = $this->formatOrderBook($data);
			//var_dump($this->depth);
			iLog("[{$this->marketname}] Order Depth Updated");
		} catch (Exception $e) {
			iLog("[{$this->marketname}] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}
	
	public function getCurrentTicker()
	{
		iLog("[{$this->marketname}] Getting current ticker...");
		try {
			$res = curl($this->tickerUrl);
			//$res = file_get_contents($this->tickerUrl);
			return $this->parseTickerJson($res);		
		} catch (Exception $e) {
			iLog("[{$this->marketname}] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
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
			iLog("[{$this->marketname}] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM {$this->table}_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						//$row['timestamp'] = $row['timestamp'] / 1000000; // convert microseconds to timestamp
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[{$this->marketname}] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[{$this->marketname}] ERROR: History ticker query failed: ".$e->getMessage());
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
			$qid = $DB->query("SELECT * FROM {$this->table}_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
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