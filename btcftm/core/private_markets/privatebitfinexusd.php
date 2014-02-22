<?php
require_once('privatemarket.php');

class PrivateBitfinexUSD extends PrivateMarket
{
	const API_URL = "https://api.bitfinex.com";
	const METHOD_BALANCES = "/v1/balances";
	const METHOD_NEW_ORDER = "/v1/order/new";
	
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
		
		iLog("[PrivateBitfinexUSD] Sending Request: {$rUrl}");

		$method = $params['method'];
		unset($params['method']);
		
		if ($method == METHOD_NEW_ORDER) {
			iLog("[PrivateBitfinexUSD] WARNING: Request not sent. Live sell and buy functions currently disabled.");
			return $response; 
		}
		
		// must have a unique incrementing nonce for every private request
		$nonce = $this->_createNonce();
		$payload['request'] = $method;
		$payload['nonce'] = $nonce;
		$payload['options'] = $params;

		// generate the POST data string
        $post_data = http_build_query($params, '', '&');

        //Payload must be in JSON format and base64 encoded
        $payload = json_encode($payload);
        $payload = base64_encode($payload);

        // set up header
        $headers = array(
            'X-BFX-APIKEY: '.$this->privatekey,
            'X-BFX-PAYLOAD: '.$payload,
            'X-BFX-SIGNATURE: '.$this->_getSignature($payload),
        );
		
		// our curl handle (initialize if required)
        if (is_null($this->ch)){
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; Bitfinex PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }
        curl_setopt($this->ch, CURLOPT_URL, $rUrl . $method);
        //curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
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
	
	protected function _getSignature($payload)
	{
		return hash_hmac('sha384', $payload, $this->secret);
	}

	protected function _buyLive($amount, $price, $crypto="BTC", $fiat="USD")
	{
		$symbol = "btcusd";
		$exchange = "bitfinex";
		$side = "buy";
		//Type of Limit means the trade when only happen when the desired price is reached: https://community.bitfinex.com/showwiki.php?title=Bitfinex+Documentation:Orders+type
		$type = "limit";

		$payload = array(
			'symbol' => $symbol,
			'amount' => $amount, 
			'price' => $price,
			'exchange' => $exchange,
			'side' => $side,
			'type' => $type,
			'is_hidden' => false
			);

		try {
			$response = $this->_sendRequest(self::API_URL, array("method" => self::METHOD_NEW_ORDER));
			if ($response){
				//TODO NOT SURE THERE"S A GOOD WAY TO CATCH THIS CASE, BUT LEAVING IT IN FOR NOW TO NOT FORGET IT
				if (isset($response['error'])) {
					iLog("[PrivateBitfinexUSD] ERROR: Buy failed {$response['error']['message']}");
				} else {
					alert('BUY'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[PrivateBitfinexUSD] ERROR: Buy failed - ".$e->getMessage());
		}

		return false;
	}

	protected function _sellLive($amount, $price, $crypto="BTC", $fiat="USD")
	{	
		iLog("[PrivateBitfinexUSD] Create SELL limit order {$amount} @{$price}USD");
		$params = array('amount' => $amount, 'price' => $price);
		try { 
			$response = $this->_sendRequest($this->sellUrl, $params);
			if ($response) {
				if(isset($response['error'])) {
					iLog("[PrivateBitfinexUSD] ERROR: Sell failed {$response['error']['message']}");
				} else {
					alert('SELL'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				}
			}
		} catch (Exception $e) {
			iLog("[PrivateBitfinexUSD] ERROR: Buy failed - ".$e->getMessage());
		}
		return false;
	}

	protected function _getLiveInfo()
	{
		global $DB;

		try {
			//no params need to be sent, that is the empty string param below
			$response = $this->_sendRequest(self::API_URL, array("method" => self::METHOD_BALANCES));

			//TODO find a better way to see if we have success then the size of the response array
			if($response && sizeof($response) == 6) {
				
				//TODO Automatically find the right array, don't just guess 0,1 etc
				$btcArr = $response[0];
				$btcAvailable = (float)$btcArr->{'available'};

				$usdArr = $response[1];
				$usdAvailable = (float)$usdArr->{'available'};
				
				$this->btcBalance = $btcAvailable;
				$this->usdBalance = $usdAvailable;
				iLog("[PrivateBitfinexUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
				return true;
			}
			else {
				iLog("[PrivateBitfinexUSD] ERROR: Get info failed - {$response}");
				return false;
			}			
		} catch (Exceptin $e){
			iLog("[PrivateBitfinexUSD] ERROR: Get info failed - ".$e->getMessage());
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
