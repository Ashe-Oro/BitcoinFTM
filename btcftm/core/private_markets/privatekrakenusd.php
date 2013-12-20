<?php
require_once('privatemarket.php');

class PrivateKrakenUSD extends PrivateMarket
{
	
	const API_URL = "https://api.kraken.com";
	const METHOD_BALANCE = "Balance";
	const METHOD_TRADE_BALANCE = "TradeBalance";
	//const PRIVATE = "private";

	private $apiVersion = 0;

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

	protected function _sendRequest($url, $params=array(), $extraHeaders=NULL)
	{
		$rUrl = $url;		
		$response = array();
		
		$response['result'] = 'success';
		$response['return'] = false;
		iLog("[PrivateKrakenUSD] Sending Request: {$rUrl}");
		
		
		if ($rUrl == $this->buyUrl['url'] || $rUrl == $this->sellUrl['url']) {
			iLog("[PrivateKrakenUSD] WARNING: Request not sent. Live sell and buy functions currently disabled.");
			return $response; 
		}
		
		// must have a unique incrementing nonce for every private request
		$nonce = $this->_createNonce();
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
            curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; Kraken PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }
        curl_setopt($this->ch, CURLOPT_URL, $rUrl . '/' . $version . '/' . "private" . '/' . $method);
        curl_setopt($this->ch, CURLOPT_URL, $rUrl);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, FALSE);			

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
		iLog("[PrivateKrakenUSD] Create BUY limit order {$amount} @{$price}USD");
		$params = array('amount' => $amount, 'price' => $price);
		try {
			$response = $this->_sendRequest($this->buyUrl, $params);
			if ($response){
				if (isset($response['error'])) {
					iLog("[PrivateKrakenUSD] ERROR: Buy failed {$response['error']['message']}");
				} else {
					alert('BUY'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[PrivateKrakenUSD] ERROR: Buy failed - ".$e->getMessage());
		}
		return false;
	}

	protected function _sell($amount, $price)
	{	
		iLog("[PrivateKrakenUSD] Create SELL limit order {$amount} @{$price}USD");
		$params = array('amount' => $amount, 'price' => $price);
		try { 
			$response = $this->_sendRequest($this->sellUrl, $params);
			if ($response) {
				if(isset($response['error'])) {
					iLog("[PrivateKrakenUSD] ERROR: Sell failed {$response['error']['message']}");
				} else {
					alert('SELL'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[PrivateKrakenUSD] ERROR: Buy failed - ".$e->getMessage());
		}
		return false;
	}

	public function getInfo()
	{
		global $config;
		global $DB;
		
		$response = $this->_sendRequest(self::API_URL, "", self::METHOD_BALANCE);

		//TODO fill out info
		return false;
	}
}
?>