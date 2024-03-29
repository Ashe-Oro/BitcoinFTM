<?php
require_once("livemarket.php");

class CryptoTradeUSD extends LiveMarket
{	
	public function __construct()
	{
		parent::__construct("USD");
		//TODO This updateRate is a random guess... Find out real update rate
		$this->updateRate = 100;
		$this->depthUrl = "https://crypto-trade.com/api/1/depth/btc_usd";
		$this->tickerUrl = "https://crypto-trade.com/api/1/ticker/btc_usd";
	}

	public function updateOrderBookData()
	{
		iLog("[CryptoTradeUSD] Updating order depth...");
		$res = file_get_contents($this->depthUrl);
		try {
			$json = json_decode($res);
			$data = $json;
			$this->orderBook = $this->formatOrderBook($data);
			iLog("[CryptoTradeUSD] Order depth updated");
		} catch (Exception $e) {
			iLog("[CryptoTradeUSD] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function getCurrentTicker()
	{
		iLog("[CryptoTradeUSD] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$data = $json->data;
			$data->volume = $data->vol_usd;
			$data->ask = $data->min_ask;
			$data->bid = $data->max_bid;
			$data->timestamp = time();
			
			$ticker = new Ticker($data);
			$t = $ticker->getTickerArray();
			iLog("[CryptoTradeUSD] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[CryptoTradeUSD] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
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
			iLog("[CryptoTradeUSD] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM cryptotradeusd_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[CryptoTradeUSD] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[CryptoTradeUSD] ERROR: History ticker query failed: ".$e->getMessage());
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
			$qid = $DB->query("SELECT * FROM cryptotradeusd_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
			$result = $DB->fetch_array_assoc($qid);
			return new Ticker($result);
		}
	}
	
	public function getHistorySamples($startDate, $endDate="", $sampling="day")
	{
		global $DB;
		iLog("[CryptoTradeUSD] Getting history samples...");
	}
}
?>