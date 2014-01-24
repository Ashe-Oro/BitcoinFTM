<?php
require_once("./core/public_markets/market.php");

abstract class HistoryMarket extends Market
{
	protected $timestamp = 0;
	protected $period = 0;
	protected $historyname = "HistoryMarket";
	protected $table = "";

	protected $depthUrl;
	protected $tickerUrl;
	
	public function __construct($currency)
	{
		parent::__construct($currency);
		$this->live = false;
		$this->updateRate = 0; // always update from DB
	}

	abstract protected function parseDepthJson($json);
	
	public function updateTimestamp($timestamp, $period)
	{
		$this->timestamp = $timestamp;
		$this->period = $period;
	}

	public function updateOrderBookData()
	{
		global $DB;
		
		if ($this->timestamp > 0) {
			iLog("[{$this->historyname}}] Updating order depth for TS: {$this->timestamp} P: {$this->period}...");
			try{
				$res = $DB->query("SELECT * FROM {$this->table}_orderbook WHERE timestamp <= {$this->timestamp} ORDER BY timestamp DESC LIMIT 1");
				if ($row = $DB->fetch_array_assoc($res)){
					$data = $this->getOrderBookObject($row);
					$this->orderBook = $this->formatOrderBook($data);
					iLog("[{$this->historyname}] Historical order depth updated");
				} else {
					iLog("[{$this->historyname}] WARNING: No historical order depth found! Using ticker data...");
					$ticker = $this->getCurrentTicker();
					$data = new stdClass();
					if ($this->getPeriodTable() == 'ticker') {
						$data->asks = array(array($ticker->getAsk(), 10));
						$data->bids = array(array($ticker->getBid(), 10));
					} else {
						$data->asks = array(array($ticker->getAvg(), 10));
						$data->bids = array(array($ticker->getAvg(), 10));
					}
					$this->orderBook = $this->formatOrderBook($data);
					iLog("[{$this->historyname}] Historical order depth updated");
				}
			} catch (Exception $e) {
				iLog("[{$this->historyname}] ERROR: historical orderbook error - ".$e->getMessage());
			}
		} else {
			iLog("[{$this->historyname}] Updating current order depth...");
			$res = file_get_contents($this->depthUrl);
			try {
				$data = $this->parseDepthJson($res);
				$this->orderBook = $this->formatOrderBook($data);
				iLog("[{$this->historyname}] Order Depth Updated");
			} catch (Exception $e) {
				iLog("[{$this->historyname}] ERROR: can't parse JSON feed - {$this->depthUrl} - ".$e->getMessage());
			}
		}
	}

	protected function getOrderBookObject($row)
	{
		if ($row){
			$asks = $row['asks'];
			$bids = $row['bids'];
			$asks = explode("),(", substr($asks, 1, strlen($asks)-2));
			$bids = explode("),(", substr($bids, 1, strlen($bids)-2)); // remove open/close parenthesis
			foreach ($asks as $i => $a) { $asks[$i] = explode(",", $a); }
			foreach ($bids as $j => $b) { $bids[$j] = explode(",", $b); }

			$data = new stdClass();
			$data->asks = $asks;
			$data->bids = $bids;
			//var_dump($data);
			return $data;
		}
		return NULL;
	}


	public function getCurrentTicker()
	{
		global $DB;
		$tPeriod = $this->getPeriodTable();
		
		iLog("[{$this->historyname}] Getting current ticker for TS: {$this->timestamp} P: {$this->period}...");
		try {
			$res = $DB->query("SELECT * FROM {$this->table}_{$tPeriod} WHERE timestamp <= {$this->timestamp} ORDER BY timestamp DESC LIMIT 1");
			if($row = $DB->fetch_array_assoc($res)){
				if ($tPeriod == "ticker"){
					$ticker = new Ticker($row);
					$t = $ticker->getTickerArray();
					iLog("[{$this->historyname}] Latest ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
				} else {
					$ticker = new PeriodTicker($row);
					$t = $ticker->getTickerArray();
					iLog("[{$this->historyname}] Ticker @ ".date("d M Y H:i:s", $t['timestamp'])." - high: {$t['high']} low: {$t['low']} avg: {$t['avg']} open: {$t['open']} close: {$t['close']} volume: {$t['volume']} avgvolume: {$t['avgvolume']} total: {$t['total']} count: {$t['count']}");
				}
				return $ticker;
			} else {
				iLog("[{$this->historyname}] ERROR: no historical ticker found for for TS: {$this->timestamp} P: {$this->period}");
			}
			
		} catch (Exception $e) {
			iLog("[{$this->historyname}] ERROR: historical ticker error - ".$e->getMessage());
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
			iLog("[{$this->historyname}] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM {$this->table}_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[{$this->historyname}] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[{$this->historyname}] ERROR: History ticker query failed: ".$e->getMessage());
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
		iLog("[{$this->historyname}] Getting history samples...");
	}
	
	protected function getPeriodTable()
	{
		switch($this->period) {
			// use half-hourly table
			case PERIOD_30M:
			{
				return "history_half_hours";
			}
			
			// use hourly table
			case PERIOD_1H:
			case PERIOD_2H:
			case PERIOD_4H:
			case PERIOD_6H:
			case PERIOD_12H:
			{
				return "history_hours";
			}
			
			// use daily table
			case PERIOD_1D:
			case PERIOD_3D:
			{
				return "history_days";
			}
			
			// use weekly table
			case PERIOD_1W:
			{
				return "history_weeks";
			}
			
			// use granular table
			default:
			{
				return "ticker";
			}
		}
	}
}
?>