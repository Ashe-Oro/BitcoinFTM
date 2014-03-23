<?php
require_once("./core/public_markets/market.php");

abstract class HistoryMarket extends Market
{
	protected $timestamp = 0;
	protected $period = 0;
	
	public function __construct($currency)
	{
		parent::__construct($currency);
		$this->live = false;
		$this->refresh = 0; // always update from DB
	}

	abstract public function parseTickerRow($row);
	
	public function updateTimestamp($timestamp, $period)
	{
		$this->timestamp = $timestamp;
		$this->period = $period;
	}

	public function updateOrderBookData()
	{
		global $DB;
		
		if ($this->timestamp > 0) {
			iLog("[{$this->name}] Updating order depth for TS: {$this->timestamp} P: {$this->period}...");
			try{
				$res = $DB->query("SELECT * FROM {$this->table}_orderbook WHERE timestamp <= {$this->timestamp} ORDER BY timestamp DESC LIMIT 1");
				if ($row = $DB->fetch_array_assoc($res)){
					$data = $this->getOrderBookObject($row);
					$this->orderBook = $this->formatOrderBook($data);
					iLog("[{$this->name}] Historical order depth updated");
				} else {
					iLog("[{$this->name}] WARNING: No historical order depth found! Using ticker data...");
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
					iLog("[{$this->name}] Historical order depth updated");
				}
			} catch (Exception $e) {
				iLog("[{$this->name}] ERROR: historical orderbook error - ".$e->getMessage());
			}
		} else {
			iLog("[{$this->name}] Updating current order depth...");
			try {
				//$res = file_get_contents($this->depthUrl);
				$res = curl($this->depthUrl);
				$data = $this->parseDepthJson($res);
				$this->orderBook = $this->formatOrderBook($data);
				iLog("[{$this->name}] Order Depth Updated");
			} catch (Exception $e) {
				iLog("[{$this->name}] ERROR: can't parse JSON feed - {$this->depthUrl} - ".$e->getMessage());
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

		$ts = $this->timestamp;
		if ($ts <= 0) { $ts = time(); }
		
		iLog("[{$this->name}] Getting current ticker for TS: {$ts} P: {$this->period}...");
		try {
			$res = $DB->query("SELECT * FROM {$this->table}_{$tPeriod} WHERE timestamp <= {$ts} ORDER BY timestamp DESC LIMIT 1");
			if($row = $DB->fetch_array_assoc($res)){
				if ($tPeriod == "ticker"){
					$row = $this->parseTickerRow($row);
					$ticker = new Ticker($row);
					$t = $ticker->getTickerArray();
					iLog("[{$this->name}] Latest ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
				} else {
					$ticker = new PeriodTicker($row);
					$t = $ticker->getTickerArray();
					iLog("[{$this->name}] Ticker @ ".date("d M Y H:i:s", $t['timestamp'])." - high: {$t['high']} low: {$t['low']} avg: {$t['avg']} open: {$t['open']} close: {$t['close']} volume: {$t['volume']} avgvolume: {$t['avgvolume']} total: {$t['total']} count: {$t['count']}");
				}
				return $ticker;
			} else {
				iLog("[{$this->name}] ERROR: no historical ticker found for for TS: {$ts} P: {$this->period}");
			}
			
		} catch (Exception $e) {
			iLog("[{$this->name}] ERROR: historical ticker error - ".$e->getMessage());
		}
		return NULL;
	}
	
}
?>