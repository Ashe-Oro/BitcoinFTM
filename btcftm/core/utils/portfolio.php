<?php
require_once("trade.php");

class Portfolio 
{
	private $portfolioID = 0;
	private $trades = array(); // array of trades
	private $pmarketData = array();
	public $privateMarkets = array(); // array of private market accounts
	private $tradeCount = 0;
	private $client = NULL;
	
	public function __construct($client, $clientArray, $portfolio=NULL)
	{
		global $config;
		global $DB;
		
		$this->client = $client;
		$markets = array();

		$res = $DB->query("SELECT * FROM privatemarkets LEFT OUTER JOIN markets ON privatemarkets.marketid = markets.id WHERE privatemarkets.clientid = ".$this->client->getID());
		while ($pmkts = $DB->fetch_array_assoc($res)){
			$this->pmarketData[$pmkts['name']] = $pmkts;
			array_push($markets, $pmkts['name']);
		}
		
		if ($portfolio) {
			// load portfolio from DB here
		}
		
		iLog("[Portfolio] Loading Portfolio for {$client->getUsername()}...");
		$this->_initPrivateMarkets($markets, $clientArray);
	}
	
	private function _initPrivateMarkets($markets, $cArray)
	{		
		foreach ($markets as $mname) {
			$lowername = strtolower($mname);
			$pFile = "./core/private_markets/private{$lowername}usd.php";
			if (file_exists($pFile)){
				require_once($pFile);
				$pName = "Private".$mname."USD";
				try {
					$cid = (int) (isset($cArray["{$lowername}id"])) ? $cArray["{$lowername}id"] : $this->client->getID();
					$ckey = $this->getAPIKey($cid, $mname);
					$csecret = $this->getAPISecret($cid, $mname);
					
					if ($cid && strlen($ckey) && strlen($csecret)) {
						$this->privateMarkets[$mname] = new $pName($cid, $ckey, $csecret);
					} else {
						if (!strlen($ckey)){	
							iLog("[Portfolio] ERROR: Private market {$mname} missing key in client DB {$lowername}key");
						} else
						if (!strlen($csecret)){
							iLog("[Portfolio] ERROR: Private market {$mname} missing secret in client DB");
						}
						
					}
				} catch (Exception $e) {
					iLog("[Portfolio] ERROR: Private market construct function invalid - {$pmarket_name} - ".$e->getMessage());
				}
			} else {
				iLog("[Portfolio] ERROR: Private market file not found - {$pFile}");
			}
		}
	}
	
	private function getAPIKey($cid, $market) {
		global $DB;

		if (isset($this->pmarketData[$market])){
			return $this->pmarketData[$market]['apiKey'];
		}

		try {
			$mid = $this->getMarketId($market);
			if(isset($mid) && $mid != false && $mid != "") {
				$result = $DB->query("SELECT apiKey FROM privatemarkets WHERE clientid = {$cid} and marketid = {$mid}");
				if ($apiKey = $DB->fetch_array_assoc($result)){
					$apiKey = $apiKey['apiKey'];
					return $apiKey;
				}
				else {
					return false;
				}
			}
		} catch (Exception $e){
			iLog("[Portfolio] ERROR: Failed Getting API Key for clientID {$cid} and market {$market} - ".$e->getMessage());
			return false;
		}

		return false;

	}

	private function getAPISecret($cid, $market) {
		global $DB;

		if (isset($this->pmarketData[$market])){
			return $this->pmarketData[$market]['apiSecret'];
		}

		try {
			$mid = $this->getMarketId($market);
			if(isset($mid) && $mid != false && $mid != "") {
				$result = $DB->query("SELECT apiSecret FROM privatemarkets WHERE clientid = {$cid} and marketid = {$mid}");
				if ($apiSecret = $DB->fetch_array_assoc($result)){
					$apiSecret = $apiSecret['apiSecret'];
					return $apiSecret;
				}
				else {
					return false;
				}
			}
		} catch (Exception $e){
			iLog("[Portfolio] ERROR: Failed Getting API Key for clientID {$cid} and market {$market} - ".$e->getMessage());
			return false;
		}

		return false;

	}

	private function getMarketId($market) {
		global $DB;

		try {
			$result = $DB->query("SELECT id FROM markets WHERE name='{$market}'");
			if($mid = $DB->fetch_array_assoc($result)){
				$mid = $mid["id"];
				return $mid;
			}
		} catch (Exception $e){
			iLog("[Portfolio] ERROR: Failed Getting Market ID for Market {$market} - " . $e->getMassage());
			return false;
		}
		return false;
	}

	public function getPrivateMarket($mname)
	{
		if (isset($this->privateMarkets[$mname])){
			return $this->privateMarkets[$mname];
		}
		return NULL;
	}
	
	public function getPrivateMarkets()
	{
		return $this->privateMarkets;
	}
	
	public function getBalances() 
	{
		$balances = array();
		foreach($this->privateMarkets as $pname => $pmarket){
			$balances[$pname] = array("BTC" => $this->privateMarkets->getBalance("BTC"), "USD" => $this->privateMarkets->getBalance("USD"));
			iLog("[Portfolio] Balance at {$pname} - {$balances[$pname]['BTC']}BTC, {$balances[$pname]['USD']}USD");
		}
		return $balances;
	}
	
	public function updateBalances()
	{
		foreach($this->privateMarkets as $pname => $pmarket){
			$this->privateMarkets[$pname]->getInfo();
		}
	}
	
	public function addTrade($trade)
	{
		$this->trades[$trade->getTradeID()] = $trade;
		$this->tradeCount++;
	}
	
	public function removeTrade($tradeID)
	{
		if (isset($this->trades[$tradeID])) {
			unset($this->trades[$tradeID]);
			$this->tradeCount--;
		}
	}
	
	public function getTradeCount()
	{
		return $this->tradeCount;
	}
	
	public function getTrades()
	{
		return $this->trades;
	}
	
	public function getCurrentTrade()
	{
		$ts = 0;
		$cur = NULL;
		foreach($this->trades as $tradeID => $trade){
			if ($trade->getTimestamp() > $ts) {
				$ts = $trade->getTimestamp();
				$cur = $trade;
			}
		}
		return $cur;
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