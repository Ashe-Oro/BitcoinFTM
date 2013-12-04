<?php
require_once("./core/public_markets/ticker/tickerCalculator.php");
require_once("./core/public_markets/mob/marketorderbook.php");

abstract class Market
{
	public $name = '';
	public $currency = '';
	public $depthUpdated = 0;
	public $updateRate = 60;
	public $orderBook = NULL;
	
	protected $live = false;

	public $fc = NULL; // currency converter object, not yet needed

	public function __construct($currency)
	{
		$this->name = get_class($this);
		$this->currency = $currency;
	}
	
	abstract public function getHistoryTickers($startDate, $endDate="");
	abstract public function getHistoryTicker($timestamp);
	abstract public function getHistorySamples($startDate, $endDate="", $sampling="day");
	abstract public function updateOrderBookData();
	abstract public function getCurrentTicker();
	
	public function updateMarketDepth()
	{
		global $config;

		$timeDiff = time() - $this->depthUpdated;
		if ($timeDiff > $this->updateRate) {
			$this->updateMarketOrderBooks();
		}
		$timeDiff = time() - $this->depthUpdated;
		if ($timeDiff > $config['marketExpirationTime']) {
			iLog("[Market] WARNING: Market {$this->name} order book is expired");
			$this->orderBook = new MarketOrderBook();
		}
		return $this->orderBook;
	}
	
	public function formatOrderBook($depth)
	{
		iLog("[Market] Formating Order Book...");
		return new MarketOrderBook($depth->asks, $depth->bids);
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
	
	public function getHistorySampleSMA($startDate, $endDate="", $sampling="day")
	{
		return $this->_getNewSMA($this->getHistorySamples($startDate, $endDate, $sampling));
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
	
}
?>