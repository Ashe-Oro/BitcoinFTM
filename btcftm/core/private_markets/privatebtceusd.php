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
		
		$params['method'] = $method;
		$params['nonce'] = $this->_createNonce();
		// generate the POST data string
        $post_data = http_build_query($params, '', '&');

        // set up header
        $headers = array(
                        'Sign: '.$this->_getSignature($post_data),
                        'Key: '.$this->privatekey,
        );
		
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
		//TODO this nonce creation may need some tweaking.. it has to be exactly 10 in length and less then 4294967294
		//Below will have problems if executed too quickly in succession with the last call generating a nonce.
		return substr(round(microtime(true) * 1000), 0, 10);
	}
	
	protected function _getSignature($post_data)
	{
		return hash_hmac('sha512', $post_data, $this->secret);
	}

	protected function _buy($amount, $price)
	{
		iLog("[PrivateBTCeUSD] Create BUY limit order {$amount} @{$price}USD");
		
	   	//BTCe requires specifying the pair (what currencies are being traded), in this case we just do btc_usd
		$params = array('amount' => $amount, 'pair' => 'btc_usd', 'type' => 'buy', 'rate' => $price);
		try {
			$response = $this->_sendRequest(self::API_URL, $params, self::METHOD_TRADE);
			if ($response){
				if (isset($response['success']) && $response['success'] == 1) {
					alert('BUY'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				} else {
					iLog("[PrivateBTCeUSD] ERROR: Buy failed {$response['error']}");
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
				if(isset($response['success']) && $response['success'] == 1) {
					alert('SELL'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
					return true;
				} else {
					iLog("[PrivateBTCeUSD] ERROR: Sell failed {$response['error']}");
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
		
		if ($config['live']) { // LIVE TRADING USES LIVE DATA
			try {
				//no params need to be sent, that is the empty string param below
				$response = $this->_sendRequest(self::API_URL, "", self::METHOD_GET_INFO);
				if($response && isset($response['success']) && $response['success'] == 1) {
					$retVal = $response['return'];
					$funds = $retVal['funds'];
					
					$this->btcBalance = (float) $funds['btc'];
					$this->usdBalance = (float) $funds['usd'];
					iLog("[PrivateBTCeUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
					return true;
				}
				else {
					iLog("[PrivateBTCeUSD] ERROR: Get info failed - {$response}");
					return false;
				}			
			} catch (Exceptin $e){
				iLog("[PrivateBTCeUSD] ERROR: Get info failed - ".$e->getMessage());
				return false;			
			}
		}
		return false;
	}
}
?>