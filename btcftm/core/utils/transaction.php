<?php
/**
 * CLASS: Transaction
 *
 * This class represents a single transaction at a single market
 */
class Transaction
{
	private $txid;
	private $clientid;
	private $timestamp;
	private $status;
	private $market; 
	private $currency;
	private $type; // buy or sell
	private $volume;
	private $price;
	private $wPrice; // weighted price
						
	public function __construct($timestamp, $clientid=0, $market=NULL, $type="", $volume=0, $price=0, $wPrice=0, $currency="USD")
	{
		if (is_object($timestamp)){
			$t = $timestamp;
			$this->txid = $t->txid;
			$this->clientid = $t->clientid;
			$this->timestamp = $t->timestamp;
			$this->market = $t->market;
			$this->type = $t->type;
			$this->volume = $t->volume;
			$this->price = $t->price;
			$this->wPrice = $t->wPrice;
			$this->currency = $t->currency;
		} else
		if (is_array($timestamp)){
			$t = $timestamp;
			$this->txid = $t['txid'];
			$this->clientid = $t['clientid'];
			$this->timestamp = $t['timestamp'];
			$this->market = $t['market'];
			$this->type = $t['type'];
			$this->volume = $t['volume'];
			$this->price = $t['price'];
			$this->wPrice = $t['wPrice'];
			$this->currency = $t['currency'];
		} else {
			$this->txid = 0;
			$this->clientid = $clientid;
			$this->timestamp = $timestamp;
			$this->market = $market;
			$this->type = $type;
			$this->volume = $volume;
			$this->price = $price;
			$this->wPrice = $wPrice;
			$this->currency = $currency;
		}
	}
	
	private function _recordDB()
	{
		if ($this->txid == 0){
			$this->_addTransactionDB();
		} else {
			$this->_updateTransactionDB();
		}
	}
	
	private function _addDB()
	{
		global $DB;
		$query = ""; // ADD INSERT QUERY HERE FOR TRANSACTION DB
		$res = $DB->query($query);
		if ($res) {
			$transaction = getTransactionDB($this->clientID, true);
			$this->txid = $transaction['txid'];
		} else {
			iLog("[Transaction] ERROR: Failed to add transaction ID: {$this->tradeID} client: {$this->clientID}");
		}
	}
	
	private function _updateDB()
	{
		global $DB;
		$query = ""; // ADD UPDATE QUERY HERE FOR TRANSACTION DB
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
	
	public function getTransactionID()
	{
		return $this->txid;
	}
	
	public function getClientID()
	{
		return $this->clientid;
	}
	
	public function getMarket()
	{
		return $this->market;
	}
	
	public function getTransactionType()
	{
		return $this->type;
	}
	
	public function getVolume()
	{
		return $this->volume;
	}
	
	public function getPrice()
	{
		return $this->price;
	}
	
	public function getWeightedPrice()
	{
		return $this->wPrice;
	}
	
	public function getCurrency()
	{
		return $this->currency;
	}
}
?>