<?php
require_once('privatemarket.php');

class PrivateBitstampUSD extends PrivateMarket
{
	private $balanceUrl = "https://www.bitstamp.net/api/balance/";
    private $buyUrl = "https://www.bitstamp.net/api/buy/";
    private $sellUrl = "https://www.bitstamp.net/api/sell/";
	
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
		iLog("[{$this->mname}] Sending Request: {$rUrl}");
		
		
		if ($rUrl == $this->buyUrl['url'] || $rUrl == $this->sellUrl['url']) {
			iLog("[{$this->mname}] WARNING: Request not sent. Live sell and buy functions currently disabled.");
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
            curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; Bitstamp PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
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
        $json = json_decode($res);
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

	protected function _buyLive($amount, $price, $crypto="BTC", $fiat="USD")
	{
		iLog("[{$this->mname}] Create BUY limit order {$amount} @{$price}USD");
		$params = array('amount' => $amount, 'price' => $price);
		try {
			$response = $this->_sendRequest($this->buyUrl, $params);
			if ($response){
				if (isset($response['error'])) {
					iLog("[{$this->mname}] ERROR: Buy failed {$response['error']['message']}");
				} else {
					alert('BUY'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[{$this->mname}] ERROR: Buy failed - ".$e->getMessage());
		}
		return false;
	}

	protected function _sellLive($amount, $price, $crypto="BTC", $fiat="USD")
	{	
		iLog("[{$this->mname}] Create SELL limit order {$amount} @{$price}USD");
		$params = array('amount' => $amount, 'price' => $price);
		try { 
			$response = $this->_sendRequest($this->sellUrl, $params);
			if ($response) {
				if(isset($response['error'])) {
					iLog("[{$this->mname}] ERROR: Sell failed {$response['error']['message']}");
				} else {
					alert('SELL'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[{$this->mname}] ERROR: Buy failed - ".$e->getMessage());
		}
		return false;
	}

	public function _getLiveInfo()
	{
		global $DB;
		
		$params = array();
		try {
			$response = $this->_sendRequest($this->balanceUrl, $params);
			if($response && isset($response['btc_available']) && isset($response['usd_available'])) {
				$this->btcBalance = (float) $response['btc_available'];
				$this->usdBalance = (float) $response['usd_available'];
				iLog("[PrivateBitstampUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
				return true;
			} else if ($response && isset($response['error'])) {
				iLog("[PrivateBitstampUSD] ERROR: Get info failed - {$response['error']}");
				return false;
			}
		} catch (Exception $e) {
			iLog("[PrivateBitstampUSD] ERROR: Get info failed - ".$e->getMessage());
			return false;
		}
		return false;
	}

	protected function _withdrawLive($amount, $currency)
	{
		// implement eventually
	}
  protected function _depositLive($amount, $currency)
  {
  	// implement eventually
  }
}
?>