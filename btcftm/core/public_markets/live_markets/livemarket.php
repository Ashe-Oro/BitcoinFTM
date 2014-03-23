<?php
require_once("./core/public_markets/market.php");

abstract class LiveMarket extends Market
{	
	public function __construct($currency)
	{
		parent::__construct($currency);
		$this->live = true;
	}

	abstract public function parseTickerJson($res);

	public function updateOrderBookData()
	{
		iLog("[{$this->name}] Updating order book data...");
		$url = "";
		try {
			$res = curl($this->depthUrl);
			//$res = file_get_contents($this->depthUrl);
			$data = $this->parseDepthJson($res);
			$this->orderBook = $this->formatOrderBook($data);
			//var_dump($this->depth);
			iLog("[{$this->name}] Order Depth Updated");
		} catch (Exception $e) {
			iLog("[{$this->name}] ERROR: can't parse JSON feed - {$url} - ".$e->getMessage());
		}
	}
	
	public function getCurrentTicker()
	{
		iLog("[{$this->name}] Getting current ticker...");
		try {
			$res = curl($this->tickerUrl);
			//$res = file_get_contents($this->tickerUrl);
			return $this->parseTickerJson($res);		
		} catch (Exception $e) {
			iLog("[{$this->name}] ERROR: can't parse JSON feed - {$this->tickerUrl} - ".$e->getMessage());
		}
		return NULL;
	}
	
}
?>