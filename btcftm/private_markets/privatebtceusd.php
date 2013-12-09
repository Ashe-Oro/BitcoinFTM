<?php
require_once('privatemarket.php');

class PrivateBTCeUSD extends PrivateMarket
{
	const API_URL = "https://btc-e.com/tapi/";
	const METHOD_GET_INFO = "getInfo";
	const METHOD_TRANS_HISTORY = "TransHistory";
	const METHOD_TRADE_HISTORY = "TradeHistory";
	const METHOD_ACTIVE_ORDERS = "ActiveOrders";
	const METHOD_TRADE = "Trade";
	const METHOD_CANCEL_ORDER = "CancelOrder";

    private $privatekey = '';
    private $secret = '';
	private $clientID = '';
	
	private $ch = NULL;

	public function __construct($clientID, $key, $secret)
	{
		parent::__construct("USD", $clientID, $key, $secret);
		$this->getInfo();
	}
	
	protected function _loadClient($clientID, $key, $secret)
	{
		$this->privatekey = $key;
		$this->secret = $secret;
		$this->clientID = $clientID;
	}

	protected function _sendRequest($url, $params=array(), $method, $extraHeaders=NULL)
	{
		$rUrl = $url;		
		$response = array();
		
		$response['result'] = 'success';
		$response['return'] = false;
		iLog("[PrivateBTCeUSD] Sending Request: {$rUrl} using method {$method}");
		
		if ($method == self::METHOD_TRADE) {
			iLog("[PrivateBTCeUSD] WARNING: Request not sent. Live sell and buy functions currently disabled.");
			return $response; 
		}
		
		// must have a unique incrementing nonce for every private request
		$nonce = $this->_createNonce();
		$params['method'] = $method;
		$params['nonce'] = $nonce;
		$params['key'] = $this->privatekey;
		$params['signature'] = $this->_getSignature($nonce);
		
		// generate the POST data string
        $post_data = http_build_query($params, '', '&');

        // set up header
        $headers = array();
		
		// our curl handle (initialize if required)
        if (is_null($this->ch)){
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; BTCe PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }
        curl_setopt($this->ch, CURLOPT_URL, $rUrl);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			
		// run the query
        $res = curl_exec($this->ch);
        if ($res === false) {
            throw new Exception('Could not get reply: ' . curl_error($this->ch));
		}
        $json = json_decode($res, true);
        if (!$json) {
            throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
		}
        return $json;
	}
	
	protected function _createNonce()
	{
		// SC: picked this up from http://stackoverflow.com/questions/19470698/php-implementation-of-bitstamp-api
		// generate a nonce as microtime, with as-string handling to avoid problems with 32bits systems
		$mt = explode(' ', microtime());
		return $mt[1] . substr($mt[0], 2, 6);
	}
	
	protected function _getSignature($nonce)
	{
		$message = $nonce.$this->clientID.$this->privatekey;
		return strtoupper(hash_hmac('sha256', $message, $this->secret));
	}

	protected function _buy($amount, $price)
	{
		iLog("[PrivateBTCeUSD] Create BUY limit order {$amount} @{$price}USD");
		
	   	//BTCe requires specifying the pair (what currencies are being traded), in this case we just do btc_usd
		$params = array('amount' => $amount, 'pair' => 'btc_usd', 'type' => 'buy', 'rate' => $price);
		try {
			$response = $this->_sendRequest(self::API_URL, $params, self::METHOD_TRADE);
			if ($response){
				if (isset($response['error'])) {
					iLog("[PrivateBTCeUSD] ERROR: Buy failed {$response['error']['message']}");
				} else {
					alert('BUY'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[PrivateBTCeUSD] ERROR: Buy failed - ".$e->getMessage());
		}
		return false;
	}

	protected function _sell($amount, $price)
	{	
		iLog("[PrivateBTCeUSD] Create SELL limit order {$amount} @{$price}USD");

	   	//BTCe requires specifying the pair (what currencies are being traded), in this case we just do btc_usd
		$params = array('amount' => $amount, 'pair' => 'btc_usd', 'type' => 'sell', 'rate' => $price);
		try { 
			$response = $this->_sendRequest(self::API_URL, $params, self::METHOD_TRADE);
			if ($response) {
				if(isset($response['error'])) {
					iLog("[PrivateBTCeUSD] ERROR: Sell failed {$response['error']['message']}");
				} else {
					alert('SELL'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[PrivateBTCeUSD] ERROR: Buy failed - ".$e->getMessage());
		}
		return false;
	}

	public function getInfo()
	{
		global $config;
		global $DB;
		
/*
EXAMPLE GET INFO RESPONSE:
	"success":1,
		"return":{
		"funds":{
			"usd":325,
			"btc":23.998,
			"sc":121.998,
			"ltc":0,
			"ruc":0,
			"nmc":0
		},
		"rights":{
			"info":1,
			"trade":1
		},
		"transaction_count":80,
		"open_orders":1,
		"server_time":1342123547
	}
*/
		//TODO Finish adjusting logic for BTCe info...
		if ($config['live']) { // LIVE TRADING USES LIVE DATA
			$params = array();
			try {
				$response = $this->_sendRequest(self::API_URL, $params, slef::METHOD_GET_INFO);
				if($response && isset($response['success']) && $response['success'] == 1) {
					$this->btcBalance = (float) $response['btc_available'];
					$this->usdBalance = (float) $response['usd_available'];
					iLog("[PrivateBTCeUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
					return true;
				} else if ($response) {
					iLog("[PrivateBTCeUSD] ERROR: Get info failed - {$response}");
					return false;
				}
			} catch (Exception $e) {
				iLog("[PrivateBTCeUSD] ERROR: Get info failed - ".$e->getMessage());
				return false;
			}
		} else {	// SIMULATED TRADING USES DATABASE DATA
			try {
				$result = $DB->query("SELECT * FROM clients WHERE bitstampkey = '{$this->privatekey}'");
				if ($client = $DB->fetch_array_assoc($result)){
					$this->btcBalance = $client['bitstampbtc'];
					$this->usdBalance = $client['bitstampusd'];
					iLog("[PrivateBTCeUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
					return true;
				}
			} catch (Exception $e){
				iLog("[PrivateBTCeUSD] ERROR: Get info failed - ".$e->getMessage());
				return false;
			}
		}
		return false;
	}
}
?>