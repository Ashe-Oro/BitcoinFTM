<?php
class Ticker {
	private $high;
	private $low;
	private $last;
	private $timestamp;
	private $bid;
	private $volume;
	private $ask;

	function __construct($high, $low, $last, $timestamp, $bid, $volume, $ask) {
        $this -> high = $high;
        $this -> low = $low;
        $this -> last = $last;
        $this -> timestamp = $timestamp;
        $this -> bid = $bid;
        $this -> volume = $volume;
        $this -> ask = $ask;
    }

    public function getTicker() {
    	//TODO Determine if these are all the properties we care about for the Ticker object
		$data = (object) array(
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