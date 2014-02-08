<?php
require_once("periodTicker.php");

class TickerCalculator
{
	private $tickers = array();
	private $tickerClass = "";
	
	public function __construct($tickers)
	{
		if (is_array($tickers)){
			$this->tickers = $tickers;
			$this->_sortTickers();
			$this->tickerClass = get_class($this->tickers[0]);
		} else {
			iLog("[TickerCalculator] ERROR: Invalid ticker array.");
		}
	}
	
	private function _sortTickers()
	{
		usort($this->tickers, array("TickerCalculator", "compareTickers"));
	}
	
	static public function compareTickers($a, $b)
	{
		if ($a->getTimestamp() == $b->getTimestamp()) {
			return 0;
		}
		return ($a->getTimestamp() > $b->getTimestamp()) ? -1 : 1;
	}
	
	public function getTickerClass()
	{
		return $this->tickerClass;
	}
	
	public function getTickers()
	{
		return $this->tickers;
	}
	
	public function getTickerCount()
	{
		return count($this->tickers);
	}
	
	public function getTickerAt($timestamp)
	{
		foreach($this->tickers as $t) {
			if ($t->getTimestamp() <= $timestamp) {
				return $t;
			}
		}
		return NULL;
	}
	
	public function getMax($prop)
	{
		$max = -1;
		$func = "get{$prop}";
		if (function_exists($func)){
			foreach($this->tickers as $t) {
				$tVal = $t->$func();
				if ($max == -1 || $tVal > $max) {
					$max = $tVal;
				}
			}
		}
		return $max;
	}
	
	public function getMin($prop)
	{
		$min = -1;
		$func = "get{$prop}";
		if (function_exists($func)){
			foreach($this->tickers as $t) {
				$tVal = $t->$func();
				if ($min == -1 || $tVal < $min) {
					$min = $tVal;
				}
			}
		}
		return $min;
	}
	
	public function getMaxTimestamp()
	{
		return $this->tickers[0]->getTimestamp();
	}
	
	public function getMinTimestamp()
	{
		return $this->tickers[$this->getTickerCount()-1]->getTimestamp();
	}
	
	public function getMaxHigh()
	{
		$maxHigh = -1;
		foreach($this->tickers as $t) {
			if ($maxHigh == -1 || $t->getHigh() > $maxHigh) {
				$maxHigh = $t->getHigh();
			}
		}
		return $maxHigh;
	}
	
	public function getMinLow()
	{
		$maxLow = -1;
		foreach($this->tickers as $t) {
			if ($maxLow == -1 || $t->getLow() < $maxLow) {
				$maxLow = $t->getLow();
			}
		}
		return $maxLow;
	}
	
	public function getSMATicker()
	{
		if ($this->tickers && count($this->tickers)) {
			$t = $this->tickers[0];
			if ($t && is_a($t, "Ticker")) {
				if ($this->tickerClass == "PeriodTicker"){
					$tArray = array(
									"timestamp" => $t->getTimestamp(),
									"high" => $this->_getSMA("High"),
									"low" => $this->_getSMA("Low"),
									"open" => $this->_getSMA("Open"),
									"close" => $this->_getSMA("Close"),
									"avg" => $this->_getSMA("Avg"),
									"volume" => $this->_getSMA("Volume"),
									"avgvolume" => $this->_getSMA("AvgVolume"),
									"count" => $this->_getSMA("Count")
									);
					return new PeriodTicker($tArray);
				} else {
					$tArray = array(
									"timestamp" => $t->getTimestamp(),
									"high" => $this->_getSMA("High"),
									"low" => $this->_getSMA("Low"),
									"last" => $this->_getSMA("Last"),
									"ask" => $this->_getSMA("Ask"),
									"bid" => $this->_getSMA("Bid"),
									"volume" => $this->_getSMA("Volume")
									);
					return new Ticker($tArray);
				}
			}
		}
		return NULL;
	}
	
	private function _getSMA($prop)
	{
		$tCount = 0;
		$tProp = 0;
		$func = "get{$prop}";
		foreach($this->tickers as $t){
			$tProp += $t->$func();
			$tCount++;
		}
		$tProp = ($tCount > 0) ? $tProp / $tCount : 0;
		return $tProp;
	}
	
	public function getXMATicker()
	{
		$t = $this->tickers[0];
		if ($this->tickerClass == "PeriodTicker"){
			$tArray = array(
							"timestamp" => $t->getTimestamp(),
							"high" => $this->_getXMA("High"),
							"low" => $this->_getXMA("Low"),
							"open" => $this->_getXMA("Open"),
							"close" => $this->_getXMA("Close"),
							"avg" => $this->_getXMA("Avg"),
							"volume" => $this->_getXMA("Volume"),
							"avgvolume" => $this->_getXMA("AvgVolume"),
							"count" => $this->_getXMA("Count")
							);
			return new PeriodTicker($tArray);
		} else {
			$tArray = array(
							"timestamp" => $t->getTimestamp(),
							"high" => $this->_getXMA("High"),
							"low" => $this->_getXMA("Low"),
							"last" => $this->_getXMA("Last"),
							"ask" => $this->_getXMA("Ask"),
							"bid" => $this->_getXMA("Bid"),
							"volume" => $this->_getXMA("Volume")
							);
			return new Ticker($tArray);
		}
	}
	
	private function _getXMA($prop)
	{
		$SMA = $this->_getSMA($prop);
		$count = $this->getTickerCount();
		$mult = (2 / ($count + 1));
		$func = "get{$prop}";
		
		$tProp = $SMA;
		foreach($this->tickers as $t){
			$tProp = (($t->$func() - $tProp)*$mult)+$tProp;
		}
		return $tProp;
	}
	
	/*** ooh, soooo high! ***/
	public function getSMAHigh()
	{
		return $this->_getSMA("High");
	}
	
	/*** don't forget to bring a towel! ***/
	public function getXMAHigh()
	{
		return $this->_getXMA("High");
	}
	
	public function getSMALow()
	{
		return $this->_getSMA("Low");
	}
	
	public function getXMALow()
	{
		return $this->_getXMA("Low");;
	}
	
	public function getSMALast()
	{
		return $this->_getSMA("Last");
	}
	
	public function getXMALast()
	{
		return $this->_getXMA("Last");
	}
	
	public function getSMAAsk()
	{
		return $this->_getSMA("Ask");
	}
	
	public function getXMAAsk()
	{
		return $this->_getXMA("Ask");
	}
	
	public function getSMABid()
	{
		return $this->_getSMA("Bid");
	}
	
	public function getXMABid()
	{
		return $this->_getXMA("Bid");
	}
	
	public function getSMAVolume()
	{
		return $this->_getSMA("Volume");
	}
	
	public function getXMAVolume()
	{
		return $this->_getXMA("Volume");
	}
}
?>