<?php
require_once("privatemtgox.php");

class PrivateMtGoxUSD extends PrivateMtGox
{
	public function __construct()
	{
		global $config;

		parent::__construct("USD");
		$this->tickerUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCUSD/public/ticker');
		$this->buyUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCUSD/private/order/add');
		$this->sellUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/BTCUSD/private/order/add');
		
		$this->getInfo();
	}

	public function getInfo()
	{
		$params = array("nonce" => $this->_createNonce());
		$response = $this->_sendRequest($this->infoUrl, $params);
		if ($response && isset($response['result']) && $response['result'] == 'success') {
			$this->btcBalance = $this->_fromIntAmount((int) $response['return']['Wallets']["BTC"]["Balance"]["value_int"]);
			$this->usdBalance = $this->_fromIntAmount((int) $response['return']['Wallets']["USD"]["Balance"]["value_int"]);
			iLog("[PrivateMtGoxUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
			return true;
		}
		return false;
	}
}

?>