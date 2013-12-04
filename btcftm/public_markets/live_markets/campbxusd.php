<?php
require_once("livemarket.php");

class CampBXUSD extends LiveMarket
{	
	public function __construct()
	{
		parent::__construct("USD");
		
		$this->updateRate = 20;
		$this->depthUrl = "http://campbx.com/api/xdepth.php";
		$this->tickerUrl = "http://campbx.com/api/xticker.php";
	}

	public function updateOrderBookData()
	{
		iLog("[CampBXUSD] Updating order depth...");
		$res = file_get_contents($this->depthUrl);
		try {
			$json = json_decode($res);
			$data = $json;
			//var_dump($json);
			$data->asks = $json->Asks;
			$data->bids = $json->Bids;
			$this->orderBook = $this->formatOrderBook($data);
			iLog("[CampBXUSD] Order depth updated");
		} catch (Exception $e) {
			iLog("[CampBXUSD] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function getCurrentTicker()
	{
		iLog("[CampBXUSD] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$data = $json;
			$data['volume'] = 0;
			$data['ask'] = $data['Best Ask'];
			$data['bid'] = $data['Best Bid'];
			$data['last'] = $data['Last Trade'];
			$data['high'] = $data['last'];
			$data['low'] = $data['low'];
			
			$ticker = new Ticker($data);
			$t = $ticker->getTickerArray();
			iLog("[CampBXUSD] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[CampBXUSD] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
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
			iLog("[CampBXUSD] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM campbxusd_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[CampBXUSD] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[CampBXUSD] ERROR: History ticker query failed: ".$e->getMessage());
			}
		}
		
		return $tickers;
	}
	
	public function getHistoryTicker($timestamp) {
		global $DB;
		$ticker = NULL;
		
		if (is_string($timestamp)){ $timestamp = strtotime($timestamp); }
		if(is_int($timestamp)){
			$qid = $DB->query("SELECT * FROM campbxusd_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
			$result = $DB->fetch_array_assoc($qid);
			return new Ticker($result);
		}
	}
	
	public function getHistorySamples($startDate, $endDate="", $sampling="day")
	{
		global $DB;
		iLog("[CampBXUSD] Getting history samples...");
	}
}
?>