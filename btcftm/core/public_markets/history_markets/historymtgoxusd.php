<?php
require_once('historymarket.php');

class HistoryMtGoxUSD extends HistoryMarket
{
	private $depthUrl = "http://data.mtgox.com/api/2/BTCUSD/money/depth";
	private $tickerUrl = "http://data.mtgox.com/api/1/BTCUSD/ticker";
	
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
	}

	public function updateOrderBookData()
	{
		global $DB;
		
		if ($this->timestamp > 0) {
			iLog("[HistoryMtGoxUSD] Updating order depth for TS: {$this->timestamp} P: {$this->period}...");
			try{
				$res = $DB->query("SELECT * FROM mtgox_orderbook WHERE timestamp <= {$this->timestamp} ORDER BY timestamp DESC LIMIT 1");
				if ($row = $DB->fetch_array_assoc($res)){
					$this->orderBook = $this->formatOrderBook($row);
					iLog("[HistoryMtGoxUSD] Historical order depth updated");
				} else {
					iLog("[HistoryMtGoxUSD] WARNING: No historical order depth found! Using ticker data...");
					$ticker = $this->getCurrentTicker();
					$data = new stdClass();
					$data->asks = array(array($ticker->getAvg(), 10));
					$data->bids = array(array($ticker->getAvg(), 10));
					$this->orderBook = $this->formatOrderBook($data);
					iLog("[HistoryMtGoxUSD] Historical order depth updated");
				}
			} catch (Exception $e) {
				iLog("[HistoryMtGoxUSD] ERROR: historical orderbook error - ".$e->getMessage());
			}
		} else {
			iLog("[HistoryMtGoxUSD] Updating current order depth...");
			$url = "";
			$res = file_get_contents($this->depthUrl);
			try {
				$json = json_decode($res);
				if ($json->result == 'success') {
					$data = $json->data;
					$this->orderBook = $this->formatOrderBook($data);
					//var_dump($this->depth);
					iLog("[MtGoxUSD] Order Depth Updated");
				}
			} catch (Exception $e) {
				iLog("[MtGoxUSD] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
			}
		}
	}
	
	public function getCurrentTicker()
	{
		global $DB;
		
		iLog("[HistoryMtGoxUSD] Getting current ticker for TS: {$this->timestamp} P: {$this->period}...");
		try {
			$res = $DB->query("SELECT * FROM mtgox_".$this->getPeriodTable()." WHERE timestamp <= {$this->timestamp} ORDER BY timestamp DESC LIMIT 1");
			if($row = $DB->fetch_array_assoc($res)){
				$ticker = new PeriodTicker($row);
				$t = $ticker->getTickerArray();
				iLog("[HistoryMtGoxUSD] Ticker @ ".date("d M Y H:i:s", $t['timestamp'])." - high: {$t['high']} low: {$t['low']} avg: {$t['avg']} open: {$t['open']} close: {$t['close']} volume: {$t['volume']} avgvolume: {$t['avgvolume']} total: {$t['total']} count: {$t['count']}");
				return $ticker;
			} else {
				iLog("[HistoryMtGoxUSD] ERROR: no historical ticker found for for TS: {$this->timestamp} P: {$this->period}");
			}
			
		} catch (Exception $e) {
			iLog("[HistoryMtGoxUSD] ERROR: historical ticker error - ".$e->getMessage());
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
			iLog("[HistoryMtGoxUSD] Get History Tickers...");
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
				iLog("[HistoryMtGoxUSD] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[HistoryMtGoxUSD] ERROR: History ticker query failed: ".$e->getMessage());
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
		iLog("[HistoryMtGoxUSD] Getting history samples...");
	}
}
?>