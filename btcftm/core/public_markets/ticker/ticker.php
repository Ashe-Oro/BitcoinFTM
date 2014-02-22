<?php
class Ticker 
{
	protected $high;
	protected $low;
	protected $last;
	protected $timestamp;
	protected $bid;
	protected $volume;
	protected $ask;

	// PERIOD TICKER STUFF, BUT WE KEEP IT HERE SO WE CAN INTERFACE
	protected $avg;
	protected $avgvolume;
	protected $count;
	protected $open;
	protected $close;

	function __construct($timestamp=0, $high=0, $low=0, $last=0, $ask=0, $bid=0, $volume=0) {
    if (is_object($timestamp)) {
		  $t = $timestamp;
			$this->high = (float) $t->high;
			$this->low = (float) $t->low;
			$this->last = (float) $t->last;
			$this->timestamp = (int) $t->timestamp;
			$this->bid = (float) $t->bid;
			$this->volume = (float) $t->volume;
			$this->ask = (float) $t->ask;
	  } else
	  if (is_array($timestamp)) {
	   	$t = $timestamp;
			$this->high = (float) isset($t['high']) ? $t['high'] : 0;
			$this->low = (float) isset($t['low']) ? $t['low'] : 0;
			$this->last =(float)  isset($t['last']) ? $t['last'] : 0;
			$this->timestamp = (int) isset($t['timestamp']) ? $t['timestamp'] : 0;
			$this->ask = (float) isset($t['ask']) ? $t['ask'] : 0;
			$this->bid = (float) isset($t['bid']) ? $t['bid'] : 0;
			$this->volume = (float) isset($t['volume']) ? $t['volume'] : 0;
	  } else {
			$this->high = (float) $high;
			$this->low = (float) $low;
			$this->last = (float) $last;
			$this->timestamp = (int) $timestamp;
			$this->bid = (float) $bid;
			$this->volume = (float) $volume;
			$this->ask = (float) $ask;
	  }	

	  $this->avg = $this->last;
	  $this->avgvolume = $this->volume;
	  $this->count = 1;
	  $this->open = $this->last;
	  $this->close = $this->last;
  }

	public function getTickerObject() 
	{
    	//TODO Determine if these are all the properties we care about for the Ticker object
		$data = (object) $this->getTickerArray();
		return $data;
    }

    public function getTickerArray() 
	{
    	//TODO Determine if these are all the properties we care about for the Ticker object
		$data = array(
			'high'      => $this->high, 
			'low'		=> $this->low,
			'last'      => $this->last,
			'timestamp' => $this->timestamp,
			'bid'       => $this->bid,
			'volume'    => $this->volume,
			'ask'       => $this->ask
			);

		return $data;
    }
	
	public function getTickerSpread($otherTicker)
	{
		if ($otherTicker && is_a($otherTicker, "Ticker")) {
			$tArray = array(
					   "timestamp" => min($this->timestamp, $otherTicker->getTimestamp()),
					   "high" => $this->high - $otherTicker->getHigh(),
					   "low" => $this->low - $otherTicker->getLow(),
					   "last" => $this->last - $otherTicker->getLast(),
					   "ask" => $this->ask - $otherTicker->getAsk(),
					   "bid" => $this->last - $otherTicker->getBid(),
					   "volume" => $this->volume - $otherTicker->getVolume()
					  );
			return new Ticker($tArray);
		}
		return NULL;
	}
	
	public function getTickerCandle()
	{
		$str = "<div id='tickercandle_{$this->timestamp}' class='tickercandle' data-timestamp='{$this->timestamp}' data-high='{$this->high}' data-low='{$this->low}' data-last='{$this->last}' data-ask='{$this->ask}' data-bid='{$this->bid}' data-volume='{$this->volume}'></div>";
		return $str;
	}
	
	/** Get HIGH 24/7!!! ***/
	public function getHigh()
	{
		return $this->high;
	}
	
	/** Get low, get low, get low, get low ***/
	public function getLow()
	{
		return $this->low;
	}
	
	public function getLast()
	{
		return $this->last;
	}
	
	public function getBid()
	{
		return $this->bid;
	}
	
	public function getAsk()
	{
		return $this->ask;
	}
	
	public function getVolume()
	{
		return $this->volume;
	}
	
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	
	public function getOpen()
	{
		return $this->open;
	}
	
	public function getClose()
	{
		return $this->close;
	}
	
	public function getAvgVolume()
	{
		return $this->avgvolume;
	}
	
	public function getAvg()
	{
		return $this->avg;
	}
	
	public function getCount()
	{
		return $this->count;
	}
}
?>