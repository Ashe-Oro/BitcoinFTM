<?php
require_once("trade.php");

class Portfolio 
{
	private $portfolioID = 0;
	private $trades = array(); // array of trades
	private $tradeCount = 0;
	private $client = NULL;
	
	public function __construct($client)
	{
		$this->client = $client;
	}
	
	public function addTrade($trade)
	{
		$trades[$trade->getTradeID()] = $trade;
		$tradeCount++;
	}
	
	public function removeTrade($tradeID)
	{
		if (isset($trades[$tradeID])) {
			unset($trades[$tradeID]));
			$tradeCount--;
		}
	}
	
	public function getTradeCount()
	{
		return $this->tradeCount;
	}
	
	private function _recordDB()
	{
		if ($this->txid == 0){
			$this->_addDB();
		} else {
			$this->_updateDB();
		}
	}
	
	private function _addDB()
	{
		global $DB;
		$query = ""; // ADD INSERT QUERY HERE FOR PORTFOLIO DB
		$res = $DB->query($query);
		if ($res) {
			$portfolio = getPortfolioDB($this->clientID, true);
			$this->portfolioID = $portfolio['portfolioid'];
		} else {
			iLog("[Portfolio] ERROR: Failed to add portfolio ID: {$this->portfolioID} client: {$this->clientID}");
		}
	}
	
	private function _updateDB()
	{
		global $DB;
		$query = ""; // ADD UPDATE QUERY HERE FOR PORTFOLIO DB
		$res = $DB->query($query);
		if ($res) {
			// yay we did it!
		} else {
			iLog("[Transaction] ERROR: Failed to update transaction ID: {$this->tradeID} client: {$this->clientID}");
		}
	}
	
	public function getTransactionDB($txid, $queryOnly=true)
	{
		global $DB;
		if ($txid && is_int($txid)) {
			$query = "SELECT * FROM transactions WHERE txid = {$txid}";
			$res = $DB->query($query);
			if ($res) {
				if ($queryOnly) { return $res; }
				return new Transaction($res);
			}
		}
		return NULL;
	}
	
	/**** we need to add other functions here that evaluate portfolio performance, position, and profit ***/
	
	
	public function getProfit()
	{
		// summarize profit from all trades and return
	}
	
	public function getMarketPosition()
	{
		// evaluates our current position in the markets
	}
	
	public function evaluateLastTrade()
	{
		// evaluates our performance on the last trade
	}
	
	public function getOpenTrades()
	{
		// returns all trades in OPEN status
	}
	
	public function getCloseTrades()
	{
		// returns all trades in CLOSE status
	}
	
	public function getSearchingTrades()
	{
		// returns all trades in SEARCHING status
	}
	
	public function getErrTrades()
	{
		// returns all trades in ERR statuses
	}
}
?>