<?php
class Ticker {
	private $high;
	private $low;
	private $last;
	private $timestamp;
	private $bid;
	private $volume;
	private $ask;

	function __construct($timestamp=0, $high=0, $low=0, $last=0, $ask=0, $bid=0, $volume=0) {
       if (is_object($timestamp)) {
		   	$t = $timestamp;
			$this->high = $t->high;
			$this->low = $t->low;
			$this->last = $t->last;
			$this->timestamp = $t->timestamp;
			$this->bid = $t->bid;
			$this->volume = $t->volume;
			$this->ask = $t->ask;
	   } else
	   if (is_array($timestamp)) {
		   	$t = $timestamp;
			$this->high = $t['high'];
			$this->low = $t['low'];
			$this->last = $t['last'];
			$this->timestamp = $t['timestamp'];
			$this->bid = $t['bid'];
			$this->volume = $t['volume'];
			$this->ask = $t['ask'];
	   } else {
			$this->high = $high;
			$this->low = $low;
			$this->last = $last;
			$this->timestamp = $timestamp;
			$this->bid = $bid;
			$this->volume = $volume;
			$this->ask = $ask;
	   }	
    }

	public function getTickerObject() {
    	//TODO Determine if these are all the properties we care about for the Ticker object
		$data = (object) $this->getTickerArray();
		return $data;
    }

    public function getTickerArray() {
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
}
?>