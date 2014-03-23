<?php
require_once("ticker.php");

// number of seconds in each period
define("PERIOD_1M", 60);
define("PERIOD_3M", 180);
define("PERIOD_5M", 300);
define("PERIOD_15M", 900);
define("PERIOD_30M", 1800);
define("PERIOD_1H", 3600);
define("PERIOD_2H", 7200);
define("PERIOD_4H", 14400);
define("PERIOD_6H", 21600);
define("PERIOD_12H", 43200);
define("PERIOD_1D", 86400);
define("PERIOD_3D", 259200);
define("PERIOD_1W", 604800);

class PeriodTicker extends Ticker 
{
	function __construct($timestamp=0, $high=0, $low=0, $open=0, $close=0, $avg=0, $volume=0, $avgvolume=0, $count=0) {
    if (is_object($timestamp)) {
		  $t = $timestamp;
			$t->ask = $avg;
			$t->bid = $avg;
			$t->last = $avg;
			parent::__construct($t);
			
			$this->avgvolume = (float) $t->avgvolume;
			$this->avg = (float) $t->avg;
			$this->count = (float) $t->count;
			$this->open = (float) $t->open;
			$this->close = (float) $t->close;
	   } else if (is_array($timestamp)) {
		  $t = $timestamp;
			$t['ask'] = $avg;
			$t['bid'] = $avg;
			$t['last'] = $avg;
			parent::__construct($t);
			
			$this->avgvolume = (float) $t['avgvolume'];
			$this->avg = (float) $t['avg'];
			$this->count = (float) $t['count'];
			$this->open = (float) $t['open'];
			$this->close = (float) $t['close'];
	   } else {
			parent::__construct($timestamp, $high, $low, $avg, $avg, $avg, $volume);
			$this->avgvolume = (float) $avgvolume;
			$this->avg = (float) $avg;
			$this->count = (float) $count;
			$this->open = (float) $open;
			$this->close = (float) $close;
	   }
	   if ($this->avg == 0){
	   	$this->avg = ($this->open + $this->close) / 2;
	   }	
  }
	
	static public function comparePeriodTickers($a, $b)
	{
		if ($a->getTimestamp() == $b->getTimestamp()) {
			return 0;
		}
		return ($a->getTimestamp() > $b->getTimestamp()) ? -1 : 1;
	} 
	
	public function getTickerCandle()
	{
		$str = "<div id='tickercandle_{$this->timestamp}' class='tickercandle periodcandle' data-timestamp='{$this->timestamp}' data-high='{$this->high}' data-low='{$this->low}' data-open='{$this->open}' data-close='{$this->close}' data-avg='{$this->avg}' data-avgvolume='{$this->avgvolume}' data-count='{$this->count}' data-volume='{$this->volume}'></div>";
		return $str;
	}

    public function getTickerArray() 
	{
    	//TODO Determine if these are all the properties we care about for the Ticker object
		$data = array(
			'timestamp' => $this->timestamp,
			'high'      => $this->high, 
			'low'		=> $this->low,
			'last'      => $this->last,
			'bid'       => $this->bid,
			'ask'       => $this->ask,
			'volume'    => $this->volume,
			'avgvolume' => $this->avgvolume,
			'avg' 		=> $this->avg,
			'count'		=> $this->count
			);

		return $data;
    }
	
	public function getTickerSpread($otherTicker)
	{
		if ($otherTicker && is_a($otherTicker, "PeriodTicker")) {
			$tArray = array(
					   "timestamp" => min($this->timestamp, $otherTicker->getTimestamp()),
					   "high" => $this->high - $otherTicker->getHigh(),
					   "low" => $this->low - $otherTicker->getLow(),
					   "open" => $this->last - $otherTicker->getOpen(),
					   "close" => $this->ask - $otherTicker->getClose(),
					   "avg" => $this->last - $otherTicker->getAvg(),
					   "volume" => $this->volume - $otherTicker->getVolume(),
					   "avgvolume" => $this->avgvolume - $otherTicker->getAvgVolume(),
					   "total" => $this->total - $otherTicker->getTotal(),
					   "count" => $this->count - $otherTicker->getCount()
					  );
			return new PeriodTicker($tArray);
		}
		return NULL;
	}
}
?>