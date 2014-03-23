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
	private $marketid; 

	private $type; // buy, sell, stop, buy-limit, sell-limit, stop-limit
	private $volume;
	private $price;
	private $fiat; 
	private $currency;
						
	public function __construct($timestamp, $clientid=0, $marketid=0, $type="", $volume=0, $price=0, $pre=0, $com=0, $honey=0, $final=0, $fiat="USD", $crypt="BTC")
	{
		if (is_object($timestamp)){
			$t = $timestamp;
			$this->txid = $t->txid;
			$this->clientid = $t->clientid;
			$this->timestamp = $t->timestamp;
			$this->marketid = $t->marketid;
			$this->type = $t->type;
			$this->volume = $t->volume;
			$this->price = $t->price;
			$this->pre = $t->pre;
			$this->com = $t->com;
			$this->honey = $t->honey;
			$this->final = $t->final;
			$this->fiat = strtolower($t->fiat);
			$this->crypt = strtolower($t->crypt);
		} else
		if (is_array($timestamp)){
			$t = $timestamp;
			$this->txid = $t['txid'];
			$this->clientid = $t['clientid'];
			$this->timestamp = $t['timestamp'];
			$this->marketid = $t['marketid'];
			$this->type = $t['type'];
			$this->volume = $t['volume'];
			$this->price = $t['price'];
			$this->pre = $t['pre'];
			$this->com = $t['com'];
			$this->honey = $t['honey'];
			$this->final = $t['final'];
			$this->fiat = strtolower($t['fiat']);
			$this->crypt = strtolower($t['crypt']);
		} else {
			$this->txid = 0;
			$this->clientid = $clientid;
			$this->timestamp = $timestamp;
			$this->marketid = $marketid;
			$this->type = $type;
			$this->volume = $volume;
			$this->price = $price;
			$this->pre = $pre;
			$this->com = $com;
			$this->honey = $honey;
			$this->final = $final;
			$this->fiat = strtolower($fiat);
			$this->crypt = strtolower($crypt);
		}
	}

	public function record()
	{
		$this->_recordDB();
	}
	
	private function _recordDB()
	{
		if ($this->txid == 0){
			return $this->_addDB();
		} else {
			return $this->_updateDB();
		}
	}
	
	private function _addDB()
	{
		global $DB;
		global $config;

		$ts = time();

		$query = "INSERT INTO transactions SET clientid = {$this->clientid}, marketid = {$this->marketid}, timestamp = {$ts}, type = '{$this->type}', volume = {$this->volume},  price = {$this->price}, pre = {$this->pre}, com = {$this->com}, honey = {$this->honey}, final = {$this->final}, fiat = '{$this->fiat}', crypt = '{$this->crypt}', live = {$config['live']}"; 
		
		$res = $DB->query($query);
		if ($res) {
			$transaction = $this->getLastClientTransaction($this->clientid, true);
			$this->txid = $transaction['txid'];
			return true;
		} else {
			iLog("[Transaction] ERROR: Failed to add transaction for client: {$this->clientid}");
		}
		return false;
	}
	
	private function _updateDB()
	{
		global $DB;
		global $config;

		$ts = time();

		$query = "UPDATE transactions SET timestamp = {$ts}, type = '{$this->type}', volume = {$this->volume},  price = {$this->price}, pre = {$this->pre}, com = {$this->com}, honey = {$this->honey}, final = {$this->final}, fiat = '{$this->fiat}', crypt = '{$this->crypt}', live = {$config['live']} WHERE clientid = {$this->clientid} AND marketid = {$this->marketid} AND txid = {$this->txid}"; 
		
		$res = $DB->query($query);
		if ($res) {
			$transaction = $this->getLastTransaction($this->clientid, true);
			$this->timestamp = $transaction['timestamp'];
			return true;
		} else {
			iLog("[Transaction] ERROR: Failed to update transaction ID: {$this->txid} client: {$this->clientid}");
		}
		return false;
	}

	public function getLastClientTransaction($clientid, $queryOnly=true)
	{
		global $DB;
		if ($clientid && is_int($clientid)) {
			$query = "SELECT * FROM transactions WHERE clientid = {$clientid} ORDER BY timestamp DESC LIMIT 1";
			$res = $DB->query($query);
			if ($res) {
				$row = $DB->fetch_array_assoc($res);
				if ($queryOnly) { return $row; }
				return new Transaction($row);
			}
		}
		return NULL;
	}
	
	public function getTransactionDB($txid, $queryOnly=true)
	{
		global $DB;
		if ($txid && is_int($txid)) {
			$query = "SELECT * FROM transactions WHERE txid = {$txid} LIMIT 1";
			$res = $DB->query($query);
			if ($res) {
				$row = $DB->fetch_array_assoc($res);
				if ($queryOnly) { return $row; }
				return new Transaction($row);
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
	
	public function getMarketID()
	{
		return $this->marketid;
	}
	
	public function getType()
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
	
	public function getFiat()
	{
		return $this->fiat;
	}
	
	public function getCrypt()
	{
		return $this->crypt;
	}
}
?>