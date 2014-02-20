<?php
require_once("privatemtgox.php");

class PrivateMtGoxEUR extends PrivateMtGox
{
	public function __construct($clientID, $key, $secret)
	{
		global $config;

		parent::__construct("EUR", $clientID, $key, $secret);
		$this->tickerUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCEUR/public/ticker');
		$this->buyUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCEUR/private/order/add');
		$this-> sellUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCEUR/private/order/add');
	}

	protected function _getLiveInfo()
	{
		$params = array("nonce" => $this->_createNonce());
		try {
			$response = $this->_sendRequest($this->infoUrl, $params);
			if ($response && isset($response['result']) && $response['result'] == 'success') {
				$this->btcBalance = $this->_fromIntAmount((int) $response['return']['Wallets']["BTC"]["Balance"]["value_int"]);
				$this->eurBalance = $this->_fromIntAmount((int) $response['return']['Wallets']["EUR"]["Balance"]["value_int"]);
				/iLog("[{$this->mname}] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
				return true;
			}
		} catch (Exception $e){
			iLog("[{$this->mname}] ERROR: Get info failed - ".$e->getMessage());
		}
		return false;
	}
}

?>