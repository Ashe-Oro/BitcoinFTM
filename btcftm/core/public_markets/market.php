<?php
require_once("./core/public_markets/ticker/tickerCalculator.php");
require_once("./core/public_markets/mob/marketorderbook.php");

abstract class Market
{
	public $name = '';
	public $mname = '';
	public $marketid = 0;
	public $currency = '';
	
	public $commission = '';
	public $expiration = 0;
	public $refresh = 0;
	
	public $supportsUSD = 0;
	public $supportsEUR = 0;
	public $supportsBTC = 0;
	public $supportsLTC = 0;

	public $depthUpdated = 0;
	public $orderBook = NULL;
	
	public $fc = NULL; // currency converter object, not yet needed [but it will be - aww skeet skeet]
	
	protected $live = false;
	protected $depthUrl = "";
	protected $tickerUrl = "";
	protected $table = "";
	

	public function __construct($currency)
	{
		$this->name = get_class($this);
		$this->currency = $currency;
		$this->mname = str_replace("History", "", str_replace($this->currency, "", get_class($this)));
		$this->_initMarket();
	}

	abstract public function parseDepthJson($json);
	abstract public function updateOrderBookData();
	abstract public function getCurrentTicker();

	private function _initMarket()
	{
		global $DB;
		try {
			$res = $DB->query("SELECT * FROM markets WHERE name = '{$this->mname}'");
			if ($res) {
				$row = $DB->fetch_array_assoc($res);
				$this->marketid = (int) $row['id'];
				$this->refresh = (int) $row['refresh'];
				$this->expiration = (int) $row['expiration'];
				$this->commission = (float) $row['commission'];

				$this->supportsUSD = $row['usd'];
				$this->supportsEUR = $row['eur'];
				$this->supportsBTC = $row['btc'];
				$this->supportsLTC = $row['ltc'];
			}
		} catch (Exception $e) {
			iLog("[Market] ERROR: Failed to init market - ".$e->getMessage());
		}
	}

	public function supports($currency) {
		$cur = strtoupper($currency);
		if (isset($this->{"supports{$cur}"})){
			return $this->{"supports{$cur}"};
		}
		return false;
	}
	
	public function updateMarketDepth()
	{
		global $config;

		$timeDiff = time() - $this->depthUpdated;
		if ($timeDiff >= $this->refresh) {
			$this->updateMarketOrderBooks();
		} else {
			$this->orderBook = ($this->orderBook) ? $this->orderBook : new MarketOrderBook();
			iLog("[Market] Couldn't update Market {$this->name} - time diff less than refresh rate");
		}
		$timeDiff = time() - $this->depthUpdated;
		if ($timeDiff > $this->expiration) {
			iLog("[Market] WARNING: Market {$this->name} order book is expired");
			$this->orderBook = new MarketOrderBook(); // return empty orderbook just to keep things moving along
		}
		return $this->orderBook;
	}
	
	public function formatOrderBook($depth)
	{
		iLog("[Market] Formating Order Book...");
		//var_dump($depth);
		if ($depth) {
			try {
				return new MarketOrderBook($depth->asks, $depth->bids);
			} catch (Exception $e) {
				echo "Market {$this->mname} failed format order book!";
			}
		} else {
			iLog("[Market] Market {$this->mname} had no depths...");
		}
	}	
	
	public function getOrderBook()
	{
		return $this->orderBook;
	}


	public function updateMarketOrderBooks()
	{
		try {
			$this->updateOrderBookData();
			$this->convertToUSD();
			$this->depthUpdated = time();
		} catch (Exception $e) {
			iLog("[Market] Can't update market: {$this->name} - {$e->getMessage()}");
		}
	}

	public function getTicker()
	{
		$orderBook = $this->updateMarketDepth();
		$res = array('ask' => 0, 'bid' => 0);
		if (count($depth['asks']) && count($depth['bids'])) {
			$res['ask'] = $orderBook->getAskTopOrder();
			$res['bid'] = $orderBook->getBidTopOrder();
		}
		return $res;
	}

	public function getYesterdaysLastTicker()
	{
		$timestamp = strtotime("today midnight -1 second");
		$h =  $this->getHistoryTicker($timestamp);
		return $h;
	}

	public function getHistoryTicker($timestamp="") {
		global $DB;
		
		if (empty($timestamp)) { $timestamp = time(); }
		if (is_string($timestamp)){ $timestamp = strtotime($timestamp); }
		if(is_int($timestamp)){
			$qid = $DB->query("SELECT * FROM {$this->table}_ticker WHERE timestamp <= {$timestamp} ORDER BY timestamp DESC LIMIT 1");
			$row = $DB->fetch_array_assoc($qid);
			$tclass = "History{$this->mname}{$this->currency}";
			$row = $tclass::parseTickerRow($row);
			return new Ticker($row);
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
			iLog("[{$this->name}] Get History Tickers...");
			try {
				$ret = $DB->query("SELECT * FROM {$this->table}_ticker WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC");
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						$tclass = "History{$this->mname}{$this->currency}";
						$row = $tclass::parseTickerRow($row);
						//$row['timestamp'] = $row['timestamp'] / 1000000; // convert microseconds to timestamp
						array_push($tickers, new Ticker($row));
					}
				}
				iLog("[{$this->name}] {$rowcount} Tickers retrieved");
				return $tickers;
			} catch (Exception $e) {
				iLog("[{$this->name}] ERROR: History ticker query failed: ".$e->getMessage());
			}
		}
		
		return $tickers;
	}

	public function getHistorySamples($startDate, $endDate="", $period=PERIOD_1D)
	{
		global $DB;
		$tickers = NULL;
		
		if (is_string($startDate)){ $startDate = strtotime($startDate); }
		if (empty($endDate)){ $endDate = time(); }
		if (is_string($endDate)){ $endDate = strtotime($endDate); }

		$ptable = $this->getPeriodTable($period);
		$tclass = ($ptable == 'ticker') ? "Ticker" : "PeriodTicker";

		if (is_int($startDate) && is_int($endDate)){
			iLog("[{$this->name}] Getting history samples...");

			try {
				$q = "SELECT * FROM {$this->table}_{$ptable} WHERE timestamp >= {$startDate} AND timestamp < {$endDate} ORDER BY timestamp DESC";
				//echo $q;
				$ret = $DB->query($q);
				$rowcount = $DB->num_rows($ret);
				if ($rowcount > 0){
					$tickers = array();
					while ($row = $DB->fetch_array_assoc($ret)){
						//$row['timestamp'] = $row['timestamp'] / 1000000; // convert microseconds to timestamp
						array_push($tickers, new $tclass($row));
					}
				}
				iLog("[{$this->name}] {$rowcount} History sample tickers retrieved");
				return $tickers;
			} catch (Exception $e) {
				iLog("[{$this->name}] ERROR: History sample ticker query failed: ".$e->getMessage());
			}
		}
	}

	protected function getPeriodTable($period="")
	{
		if (isset($this->period) && empty($period)){
			$period = $this->period;
		}

		switch($period) {
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
	

	public function getSMA($days)
	{
		$startdate = strtotime("midnight today -{$days} days");
		$enddate = strtotime("midnight today");
		return $this->getHistorySampleSMA($startdate, $enddate, PERIOD_1D);
	}

	public function getXMA($days)
	{
		$startdate = strtotime("midnight today -{$days} days");
		$enddate = strtotime("midnight today");
		return $this->getHistorySampleXMA($startdate, $enddate, PERIOD_1D);
	}
	
	private function _getNewSMA($history)
	{
		$tc = new TickerCalculator($history);
		return $tc->getSMATicker();
	}
	
	public function getHistorySMA($startDate, $endDate="")
	{
		return $this->_getNewSMA($this->getHistoryTickers($startDate, $endDate));
	}
	
	public function getHistoryPeriodSMA($startDate, $endDate="", $period=PERIOD_15M)
	{
		return $this->_getNewSMA($this->getHistoryPeriodTickers($startDate, $endDate, $period));
	}
	
	public function getHistorySampleSMA($startDate, $endDate="", $period=PERIOD_1D)
	{
		return $this->_getNewSMA($this->getHistorySamples($startDate, $endDate, $period));
	}
	
	private function _getNewXMA($history)
	{
		$tc = new TickerCalculator($history);
		return $tc->getXMATicker();
	}
	
	public function getHistoryXMA($startDate, $endDate="")
	{
		return $this->_getNewXMA($this->getHistoryTickers($startDate, $endDate));
	}
	
	public function getHistoryPeriodXMA($startDate, $endDate="", $period=PERIOD_15M)
	{
		return $this->_getNewXMA($this->getHistoryPeriodTickers($startDate, $endDate, $period));
	}
	
	public function getHistorySampleXMA($startDate, $endDate="", $sampling="day")
	{
		return $this->_getNewXMA($this->getHistorySamples($startDate, $endDate, $sampling));
	}
	
	public function getHistoryPeriodTickers($startDate, $endDate="", $period=PERIOD_15M, $prop="Last")
	{
		global $DB;
		
		if (is_string($startDate)){ $startDate = strtotime($startDate); }
		if (empty($endDate)){ $endDate = time(); }
		if (is_string($endDate)){ $endDate = strtotime($endDate); }
		
		if (!is_int($startDate) || !is_int($endDate) || !is_int($period)){ return NULL; }
		
		// reverse to start with oldest dates
		$hTicker = array_reverse($this->getHistoryTickers($startDate, $endDate)); 
		if (!count($hTicker)){ return NULL; }
		
		$tIndex = 0;
		$func = "get{$prop}";
		
		
		$tDate = $startDate;
		$tickers = array();
		
		// loop over time range from end to start
		while($tDate <= $endDate) {
			$nextDate = min($tDate + $period, $endDate);
			
			$newT = array(
				'timestamp' => $tDate, 
				'high' => -1, 
				'low' => -1, 
				'open' => -1, 
				'close' => -1, 
				'avg' => -1,
				'volume' => 0, 
				'avgvolume' => 0, 
				'count' => 0
			);
			
			// 
			$thisT = 0;
			$last = NULL;
			$brokeOut = false;
			
			for($i = $tIndex; $i < count($hTicker); $i++){
				$t = $hTicker[$i];
				
				//echo "Ticker {$i} ts: ".$t->getTimestamp() ." tDate: {$tDate} nextDate: {$nextDate}<br />";
				
				if ($t->getTimestamp() >= $tDate && $t->getTimestamp() < $nextDate){
					$tVal = $t->$func();
					
					$newT['count'] += 1;
					
					// open value is first ticker value
					if ($newT['open'] == -1 || $thisT == 0){
						$newT['open'] = $tVal;
					}
					
					// find the highest ticker value
					if ($newT['high'] == -1 || $newT['high'] < $tVal) {
						$newT['high'] = $tVal;
					}
					
					// find the lowest ticker value
					if ($newT['low'] == -1 || $newT['low'] < $tVal) {
						$newT['low'] = $tVal;
					}
					
					// average ticker value
					$newT['avg'] = ($newT['avg'] == -1) ? $tVal : $newT['avg']; // init average
					$newT['avg'] = (($newT['avg'] * ($newT['count']-1)) + $tVal)/$newT['count']; // average in the value
					
					// total ticker volume
					$newT['volume'] += $t->getVolume();
					
					// average ticker volume
					$newT['avgvolume'] = ($newT['avgvolume'] == 0) ? $t->getVolume() : $newT['avgvolume'];
					$newT['avgvolume'] = (($newT['avgvolume'] * ($newT['count']-1)) + $t->getVolume())/$newT['count']; // average in the value
					
				} else {
					$tIndex = $i;
					if ($last) { 
						$newT['close'] = $t->$func(); 
					}
					if ($newT['close'] != -1 && $newT['open'] != -1) {
						array_push($tickers, new PeriodTicker($newT)); // create new PeriodTicker and add to array
					}
					$brokeOut = true;
					break;
				}
				
				$last = $t;
				$thisT++;
			}
			
			// exited loop because of end of tickers rather than break statement
			if (!$brokeOut) {
				if ($last) { 
					$newT['close'] = $t->$func(); 
				}
				if ($newT['close'] != -1 && $newT['open'] != -1) {
					array_push($tickers, new PeriodTicker($newT));
				}
			}
			
			$tDate += $period;
		}
		
		return array_reverse($tickers); // reverse array to put tickers from most recent to least recent
	}
	
	public function isLiveData()
	{
		return $this->live;
	}
	
	public function convertToUSD()
	{
		if($this->currency == 'USD') { return; }
		// otherwise do other stuff, but we're only in USD right now
	}

	public function getName()
	{
		return str_replace("History","", str_replace($this->currency, "", $this->name));
	}
	
}

function sanitizeMarketName($mname)
{
	return str_replace("History","",str_replace("USD","", $mname));
}
?>