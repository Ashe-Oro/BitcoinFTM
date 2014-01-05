<?php
require_once("privatemtgox.php");

class PrivateMtGoxUSD extends PrivateMtGox
{
	public function __construct($clientID, $key, $secret)
	{
		global $config;
		parent::__construct("USD", $clientID, $key, $secret);
		$this->tickerUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCUSD/public/ticker');
		$this->buyUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCUSD/private/order/add');
		$this->sellUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCUSD/private/order/add');
		
		$this->getInfo();
	}

	public function getInfo()
	{
		global $config;
		global $DB;
		if ($config['live']){ // LIVE TRADING USES LIVE DATA
			$params = array("nonce" => $this->_createNonce());
			try {
				$response = $this->_sendRequest($this->infoUrl, $params);
				if ($response && isset($response['result']) && $response['result'] == 'success') {
					$this->btcBalance = $this->_fromIntAmount((int) $response['return']['Wallets']["BTC"]["Balance"]["value_int"]);
					$this->usdBalance = $this->_fromIntPrice((int) $response['return']['Wallets']["USD"]["Balance"]["value_int"]);
					iLog("[PrivateMtGoxUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
					return true;
				}
			} catch (Exception $e) {
				iLog("[PrivateMtGoxUSD] ERROR: Get info failed - ".$e->getMessage());
			} 
		}else {	// SIMULATED TRADING USES DATABASE DATA
			try {
				$result = $DB->query("SELECT * FROM privatemarkets WHERE apiKey = '{$this->privatekey}' AND clientid = '{$this->clientId}'");
				if ($client = $DB->fetch_array_assoc($result)){
					$this->btcBalance = $client['btc'];
					$this->usdBalance = $client['usd'];
					iLog("[PrivateMtGoxUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
					return true;
				}
			} catch (Exception $e){
				iLog("[PrivateMtGoxUSD] ERROR: Get info failed - ".$e->getMessage());
				return false;
			}
		}
		return false;
	}
}

?>