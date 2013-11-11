<?php
require_once("transaction.php");

/**
 * CLASS: Trade
 *
 * This class will contain multiple transactions, both buy and sell, that comprise a single trade strategy
 */
class Trade
{
	private $tradeID;
	private $openTs; 		// open timestamp
	private $openBuyTx; 	// open buy transactions (array of Transactions)
	private $openSellTx; 	// open sell transactions (array of Transactions)
	private $openDeltaTx;	// 
	private $closeTs;
	private $closeBuyTx; 	// close buy transactions (array of Transactions)
	private $closeSellTx; 	// close sell transactions (array of Transactions)
	private $closeDeltaTx;
	private $volume;
	private $estProfit;
	private $actProfit;
	
	private $status;
	private $isSimpleTrade;
	
	const SEARCHING = "Searching";
	const OPENING = "Opening";
	const OPEN = "Open";
	const CLOSING = "Closing";
	const CLOSE = "Close";
	const OPEN_ERR = "Open ERROR";
	const CLOSE_ERR = "Close ERROR";
	
	/**
	 * Creates a new Trade
	 */
	public function __construct($trade=NULL)
	{
		if (is_null($trade)) {
			$this->tradeID = 0;
			$this->openTs = 0;
			$this->openBuyTx = array();
			$this->openSellTx = array();
			$this->closeTs = 0;
			$this->closeBuyTx = array();
			$this->closeSellTx = array();
			$this->status = SEARCHING;
			$this->isSimpleTrade = false;
			$this->estProfit = 0;
			$this->actProfit = 0;
			$this->volume = 0;
		} else {
			if (is_array($trade)) {
				$this->tradeID = $trade['tradeID'];
			}
		}
	}
	
	public function openTrade($buyT, $sellT)
	{
		foreach($buyT as $t) {
			array_push($this->openBuyTx, $t);
		}
		foreach($sellT as $t) {
			array_push($this->openSellTx, $t);
		}
		$this->status = OPENING;
		
		// do confirmation of trades
		$this->confirmOpenTrade($this->getOpenTradeConfirmation());
	}
	
	public function confirmOpenTrade($confirm)
	{
		if ($confirm){
			$this->status = OPEN;
			iLog("[Trade] Trade OPEN!");
			$this->openTs = time();
		} else {
			$this->status = OPEN_ERR;
			iLog("[Trade] ERROR: Trade failed to open");
			$this->openTs = 0;
		}
		$this->_recordDB();
	}
	
	
	public function getOpenTradeConfirmation()
	{
		// do call to verify that all opening transactions have been completed.
		// for now, this is set to TRUE. we need to add in verification logic here
		return true;
	}
	
	
	// simple trade is 1-to-1
	public function openSimpleTrade($buyT, $sellT)
	{
		$this->isSimpleTrade = true;
		$this->openTrade(array($buyT), array($sellT));
	}
	
	public function closeTrade($buyT, $sellT)
	{
		foreach($buyT as $t) {
			array_push($this->closeBuyTx, $t);
		}
		foreach($sellT as $t) {
			array_push($this->closeSellTx, $t);
		}
		$this->status = CLOSING;
		
		// do confirmation of trades, then call...
		$this->confirmCloseTrade($this->getCloseTradeConfirmation());
	}
	
	public function confirmCloseTrade($confirm)
	{
		if ($confirm){
			$this->status = CLOSE;
			iLog("[Trade] Trade CLOSED!");
			$this->closeTs = time();
		} else {
			$this->status = CLOSE_ERR;
			iLog("[Trade] ERROR: Trade failed to close");
			$this->closeTs = 0;
		}
		$this->_recordDB();
	}
	
	public function getCloseTradeConfirmation()
	{
		// do call to verify that all closing transactions have been completed.
		// for now, this is set to TRUE. we need to add in verification logic here
		return true;
	}
	
	// simple trade is 1-to-1
	public function closeSimpleTrade($buyT, $sellT)
	{
		$this->isSimpleTrade = true;
		$this->closeTrade(array($buyT), array($sellT));
		$this->_recordDB();
	}
	
	private function _recordDB()
	{
		if ($this->tradeID == 0){
			$this->_addDB();
		} else {
			$this->_updateDB();
		}
	}
	
	private function _addDB()
	{
		global $DB;
		$query = ""; // ADD INSERT QUERY HERE FOR TRADE DB
		$res = $DB->query($query);
		if ($res) {
			$trade = getTradeDB($this->clientID, true);
			$this->tradeID = $trade['tradeID'];
		} else {
			iLog("[Trade] ERROR: Failed to add trade ID: {$this->tradeID} client: {$this->clientID}");
		}
	}
	
	private function _updateDB()
	{
		global $DB;
		$query = ""; // ADD UPDATE QUERY HERE FOR TRADE DB
		$res = $DB->query($query);
		if ($res) {
			// yay we did it!
		} else {
			iLog("[Trade] ERROR: Failed to update trade ID: {$this->tradeID} client: {$this->clientID}");
		}
	}
	
	public function getTradeDB($tradeID, $queryOnly=true)
	{
		global $DB;
		if ($tradeID && is_int($tradeID)) {
			$query = "SELECT * FROM trades WHERE tradeID = {$tradeID}";
			$res = $DB->query($query);
			if ($res) {
				if ($queryOnly) { return $res; }
				return new Trade($res);
			}
		}
		return NULL;
	}
	
	public function getNewestTradeDB($clientID, $queryOnly=true)
	{
		global $DB;
		if ($clientID && is_int($clientID)) {
			$query = "SELECT * FROM trades WHERE clientID = {$clientID} ORDER BY timestamp DESC LIMIT 1";
			$res = $DB->query($query);
			if ($res) {
				if ($queryOnly) { return $res; }
				return new Trade($res);
			}
		}
		return NULL;
	}
	
	public function getTradeID()
	{
		return $this->tradeID;
	}
	
	public function getClientID()
	{
		return $this->clientID;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function getOpenTimestamp()
	{
		return $this->openTs;
	}
	
	public function getCloseTimestamp()
	{
		return $this->closeTs;
	}
	
	public function getVolume()
	{
		return $this->volume;
	}
	
	public function getEstimatedProfit()
	{
		return $this->estProfit;
	}
	
	public function getActualProfit()
	{
		return $this->actProfit;
	}

}
?>