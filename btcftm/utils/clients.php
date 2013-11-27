<?php
require_once("portfolio.php");

class Client
{
	private $clientID;
	private $firstName = '';
	private $lastName = '';
	private $userName = '';
	private $portfolio = NULL;
	
	private $maxTxVolume = 10;
	private $minTxVolume = 1;
	private $balanceMargin = 0.05;
	private $profitThresh = 1;
	private $percThresh = 2;
	
	private $active = 0;
	private $trading = 0;
	private $bots = array();
	
	public function __construct($clientID)
	{
		global $DB;
		global $config;
		
		if (is_int($clientID) || is_string($clientID) || is_array($clientID)){
			try {
				$res = NULL;
				if (is_array($clientID)) {
					$client = $clientID;
				} else
				if (is_int($clientID)){
					$res = $DB->query("SELECT * FROM clients WHERE clientID = {$clientID} LIMIT 1");
				} else if (is_string($clientID)){
					$res = $DB->query("SELECT * FROM clients WHERE username = '{$clientID}' LIMIT 1");
				}
				
				if ($res) {
					$client = $DB->fetch_array_assoc($res);
				}
				
				if ($client) {
					iLog("[Clients] Initializing client - ID: {$client['clientid']}, username: {$client['username']}");
					$this->clientID = $client['clientid'];
					$this->firstName = $client['firstname'];
					$this->lastName = $client['lastname'];
					$this->userName = $client['username'];
					
					$this->active = $client['active'];
					$this->trading = $client['trading'];
					
					$this->_initTraderBots($client);
					$this->_initPortfolio($client);
				}
			} catch (Exception $e) {
				iLog("[Clients] ERROR: Couldn't initialize client {$cid} - ".$e->getMessage());
				throw new BadClientException("Client query failed");
			}
		} else {
			throw new BadClientException("No client ID");
		}
	}
	
	private function _initTraderBots($clientArray)
	{
		global $DB;
		$q = $DB->query("SELECT * FROM traderbots WHERE clientid = {$clientArray['clientid']} AND active = 1 ORDER BY traderbotid ASC");
		while($res = $DB->fetch_array_assoc($q)){
			array_push($this->bots, $res);
		}
	}
	
	private function _initPortfolio($clientArray)
	{
		$p = new Portfolio($this, $clientArray);
		$this->portfolio = $p;
	}
	
	public function getPortfolio()
	{
		return $this->portfolio;
	}
	
	public function getPrivateMarket($mname)
	{
		if ($pmarket = $this->portfolio->getPrivateMarket($mname)){
			return $pmarket;
		}
		return NULL;
	}
	
	public function getMarketBalance($mname, $currency)
	{
		if ($mkt = $this->getPrivateMarket($mname."USD")){
			return $mkt->getBalance($currency);
		}
		return -1;
	}
	
	public function getBalances() 
	{
		return $this->portfolio->getBalances();
	}
	
	public function updateBalances()
	{
		return $this->portfolio->updateBalances();
	}
	
	public function getID()
	{
		return $this->clientID;
	}
	
	public function getName()
	{
		return $this->firstName.' '.$this->lastName;
	}
	
	public function getUsername()
	{
		return $this->userName;
	}
	
	public function isActive()
	{
		return $this->active;
	}
	
	public function isTrading()
	{
		return $this->trading;
	}
	
	public function getTraderBots()
	{
		return $this->bots;
	}
}

class BadClientException extends Exception
{
	public function __construct($message='', $code=0, $previous=NULL)
	{
		parent::__construct("Bad Client Exception: ".$message, $code, $previous);
	}
}
?>