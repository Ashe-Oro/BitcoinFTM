<?php
require_once("./private_markets/privatemtgoxusd.php");
require_once("./private_markets/privatebitstampusd.php");

class Client
{
	private $clientID;
	private $firstName = '';
	private $lastName = '';
	private $userName = '';
	private $privateMarkets = array();
	
	private $maxTxVolume = 10;
	private $minTxVolume = 1;
	private $balanceMargin = 0.05;
	private $profitThresh = 1;
	private $percThresh = 2;
	
	public function __construct($clientID)
	{
		global $DB;
		global $config;
		
		if (is_int($clientID) || is_string($clientID)){
			try {
				if (is_int($clientID)){
					$res = $DB->query("SELECT * FROM clients WHERE clientID = {$clientID} LIMIT 1");
				} else if (is_string($clientID)){
					$res = $DB->query("SELECT * FROM clients WHERE username = '{$clientID}' LIMIT 1");
				}
				if ($client = $DB->fetch_array_assoc($res)) {
					iLog("[Clients] Initializing client - ID: {$client['clientid']}, username: {$client['username']}");
					$this->clientID = $client['clientid'];
					$this->firstName = $client['firstname'];
					$this->lastName = $client['lastname'];
					$this->userName = $client['username'];
					
					$this->maxTxVolume = $client['maxtxvolume'];
					$this->minTxVolume = $client['mintxvolume'];
					$this->balanceMargin = $client['balancemargin'];
					$this->profitThresh = $client['profitthresh'];
					$this->percThresh = $client['percthresh'];
					
					$this->_initPrivateMarkets($config['markets'], $client);
				}
			} catch (Exception $e) {
				iLog("[Clients] ERROR: Couldn't initialize client {$cid} - ".$e->getMessage());
				throw new BadClientException("Client query failed");
			}
		} else {
			throw new BadClientException("No client ID");
		}
	}
	
	private function _initPrivateMarkets($markets, $client)
	{
		foreach ($markets as $mname) {
			$lowername = strtolower($mname);
			$lowernameEx = str_replace("usd", "", $lowername);
			$pFile = "./private_markets/private{$lowername}.php";
			if (file_exists($pFile)){
				require_once($pFile);
				$pName = "private".$mname;
				try {
					$cid = (int) (isset($client["{$lowernameEx}id"])) ? $client["{$lowernameEx}id"] : $this->clientID;
					$ckey = (isset($client["{$lowernameEx}key"])) ? $client["{$lowernameEx}key"] : "";
					$csecret = (isset($client["{$lowernameEx}secret"])) ? $client["{$lowernameEx}secret"] : "";
					
					if ($cid && strlen($ckey) && strlen($csecret)) {
						$this->privateMarkets[$mname] = new $pName($cid, $ckey, $csecret);
					} else {
						//var_dump($client);
						if (!strlen($ckey)){
							iLog("[Clients] ERROR: Private market {$mname} missing key in client DB {$lowernameEx}key - ".$client["{$lowernameEx}key"]);
						} else
						if (!strlen($csecret)){
							iLog("[Clients] ERROR: Private market {$mname} missing secret in client DB - ".$client["{$lowernameEx}secret"]);
						}
						
					}
				} catch (Exception $e) {
					iLog("[Clients] ERROR: Private market construct function invalid - {$pmarket_name} - ".$e->getMessage());
				}
			} else {
				iLog("[Clients] ERROR: Private market file not found - {$pFile}");
			}
		}
	}
	
	public function getPrivateMarket($mname)
	{
		if (isset($this->privateMarkets[$mname])){
			return $this->privateMarkets[$mname];
		}
		return NULL;
	}
	
	public function getMarketBalance($mname, $currency)
	{
		if ($mkt = getPrivateMarket($mname)){
			return $mkt->getBalance($currency);
		}
		return -1;
	}
	
	public function getBalances() 
	{
		$balances = array();
		foreach($this->privateMarkets as $pname => $pmarket){
			$balances[$pname] = array("BTC" => $this->privateMarkets->getBalance("BTC"), "USD" => $this->privateMarkets->getBalance("USD"));
			iLog("[Clients] Balance at {$pname} - {$balances[$pname]['BTC']}BTC, {$balances[$pname]['USD']}USD");
		}
		return $balances;
	}
	
	public function updateBalances()
	{
		foreach($this->privateMarkets as $pname => $pmarket){
			$this->privateMarkets[$pname]->getInfo();
		}
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
	
	public function getMaxTxVolume()
	{
		return $this->maxTxVolume;
	}
	
	public function getMinTxVolume()
	{
		return $this->minTxVolume;
	}
	
	public function getBalanceMargin()
	{
		return $this->balanceMargin;
	}
	
	public function getProfitThresh()
	{
		return $this->profitThresh;
	}
	
	public function getPercThresh()
	{
		return $this->percThresh;
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