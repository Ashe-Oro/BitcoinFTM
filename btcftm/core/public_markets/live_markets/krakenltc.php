<?php
require_once("livemarket.php");

class KrakenLTC extends LiveMarket
{
	public function __construct()
	{
		parent::__construct("LTC");
		$this->updateRate = 60;
		$this->depthUrl = "https://api.kraken.com/0/public/Depth?pair=LTCUSD";
		$this->tickerUrl = "https://api.kraken.com/0/public/Ticker?pair=LTCUSD";
	}

	public function updateOrderBookData()
	{
		iLog("[KrakenLTC] Updating order depth...");
		$res = file_get_contents($this->depthUrl);
		try {
			$json = json_decode($res);
			$data = $json->result->XLTCZUSD;
			$this->orderBook = $this->formatOrderBook($data);
			iLog("[KrakenLTC] Order depth updated");
		} catch (Exception $e) {
			iLog("[KrakenLTC] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}

	public function getCurrentTicker()
	{
		iLog("[KrakenLTC] Getting current ticker...");
		$res = file_get_contents($this->tickerUrl);
		try {
			$json = json_decode($res);
			$data = $json->result->XLTCZUSD;		// refer to https://www.kraken.com/help/api#get-ticker-info
			$jData = array(	'ask' => $data['a'][0],
							'bid' => $data['b'][0],
							'last' => $data['c'][0],
							'low' => $data['l'][0],
							'high' => $data['h'][0],
							'timestamp' => time(),
							'volume' => $data['v'][1]
						);
			
			$ticker = new Ticker($jData);
			$t = $ticker->getTickerArray();
			iLog("[KrakenLTC] Current ticker - high: {$t['high']} low: {$t['low']} last: {$t['last']} ask: {$t['ask']} bid: {$t['bid']} volume: {$t['volume']}");
			return $ticker;
		} catch (Exception $e) {
			iLog("[KrakenLTC] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
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
			iLog("[KrakenLTC] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM krakenltc_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[KrakenLTC] {$rowcount} Tickers retrieved");
			} catch (Exception $e) {
				iLog("[KrakenLTC] ERROR: History ticker query failed: ".$e->getMessage());
			}
		}
		
		return $tickers;
	}
	
	public function getHistoryTicker($timestamp) {
		global $DB;
		$ticker = NULL;
		
		if (is_string($timestamp)){ $timestamp = strtotime($timestamp); }
		if(is_int($timestamp)){
			$qid = $DB->query("SELECT * FROM krakenltc_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
			$result = $DB->fetch_array_assoc($qid);
			return new Ticker($result);
		}
	}
	
	public function getHistorySamples($startDate, $endDate="", $sampling="day")
	{
		global $DB;
		iLog("[KrakenLTC] Getting history samples...");
	}

}
?>