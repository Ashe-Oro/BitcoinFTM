<?php
require_once("marketorderbook.php");

class MOB 
{
	private $markets = NULL;
	private $orderbooks = NULL;
	private $matrix = NULL;
	private $fullmatrix = NULL;
	
	public function __construct($markets)
	{
		iLog("[MOB] Creating new MOB for ".count($markets)." markets");
		$this->markets = $markets;
		$this->orderbooks = array();
		foreach($markets as $m){
			$m->updateMarketDepth();
			$obook = $m->getOrderBook();
			$this->orderbooks[$m->name] = $obook;
		}
		$this->matrix = $this->_updateExchangeMatrix();
		//$this->dumpOrderBooks();
	}
	
	public function getMarketOrderBook($marketName)
	{
		if (isset($this->orderbooks[$marketName])){
			return $this->orderbooks[$marketName];
		}
		return NULL;
	}
	
	public function getMarketAskOrderBook($marketName)
	{
		if ($mbook = $this->getMarketOrderBook($marketName)){
			return $mbook->getAskOrderBook();
		}
		return NULL;
	}
	
	public function getMarketBidOrderBook($marketName)
	{
		if ($mbook = $this->getMarketOrderBook($marketName)){
			return $mbook->getBidOrderBook();
		}
		return NULL;
	}
	
	public function getMarketAskTopOrder($marketName)
	{
		if ($mbook = $this->getMarketOrderBook($marketName)){
			return $mbook->getAskTopOrder();
		}
		return NULL;
	}
	
	public function getMarketBidTopOrder($marketName)
	{
		if ($mbook = $this->getMarketOrderBook($marketName)){
			//var_dump($mbook);
			return $mbook->getBidTopOrder();
		}
		return NULL;
	}
	
	public function getBestMarketOpportunity($askMarketName)
	{
		iLog("[MOB] Getting best market opportunity for {$askMarketName}...");
		$comp = $this->getMarketOrderBookComparison($askMarketName);
		if ($comp && count($comp)){
			$best = $comp[0];
			iLog("[MOB] Best Market Opportunity for {$askMarketName}: {$best['market']} at profit of {$best['profit']}USD/BTC");
			return $best;
		}
		return NULL;
	}
	
	public function getMarketOrderBookComparison($askMarketName)
	{
		if ($oBook = $this->getMarketOrderBook($askMarketName)){
			iLog("[MOB] Getting market order book comparison for {$askMarketName}");
			$comp = array();
			foreach($this->orderbooks as $mname => $obook) {
				if ($mname != $askMarketName){
					$profit = $this->compareMarketOrderBooks($askMarketName, $mname);
					$comp[] = array("market" => $mname, "profit" => $profit);
					//iLog("[MOB] Opportunity for {$askMarketName} at {$mname}: {$profit}");
				}
			}
			usort($comp, array("MOB", "sortByProfit"));
			return $comp;
		}
		return NULL;
	}
	
	// sort by profit DESC with biggest profit on top
	static public function sortByProfit($a, $b)
	{
		if ($a['profit'] == $b['profit']) { return 0; }
		return ($a['profit'] < $b['profit']) ? 1 : -1;
	}
	
	protected function _updateExchangeMatrix()
	{
		iLog("[MOB] Getting exchange matrix...");
		$matrix = array();
		foreach($this->markets as $m){
			$matrix[$m->name] = $this->getBestMarketOpportunity($m->name);
		}
		uasort($matrix, array("MOB", "sortByProfit"));
		$this->matrix = $matrix;
		return $matrix;
	}
	
	public function getExchangeMatrix()
	{
		return $this->matrix;
	}
	
	public function getFullExchangeMatrix()
	{
		$matrix = array();
		
		foreach($this->markets as $m){
			$matrix[$m->name] = array();
			foreach($this->orderbooks as $mname => $obook) {
				if ($mname != $m->name){
					$profit = $this->compareMarketOrderBooks($m->name, $mname);
					$matrix[$m->name][$mname] = array("market" => $mname, "profit" => $profit);
					//iLog("[MOB] Opportunity for {$askMarketName} at {$mname}: {$profit}");
				}
			}
		}
		return $matrix;
	}
	
	public function compareMarketOrderBooks($askMarketName, $bidMarketName)
	{
		$aOrder = $this->getMarketAskTopOrder($askMarketName);
		$bOrder = $this->getMarketBidTopOrder($bidMarketName);
		
		if ($aOrder && $bOrder) {
			$aPrice = $aOrder->getPrice();
			$bPrice = $bOrder->getPrice();
			$dPrice = $bPrice - $aPrice;
			iLog("[MOB] Ask Market {$askMarketName}: {$aPrice}, Bid Market {$bidMarketName}: {$bPrice}, Spread: {$dPrice}");
			return $dPrice;
		}
		return NULL;
	}
	
	public function dumpOrderBooks()
	{
		//var_dump($this->orderbooks); // use for debugging ONLY!!
	}
}
?>