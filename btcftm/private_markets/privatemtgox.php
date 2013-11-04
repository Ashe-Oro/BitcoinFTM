<?php
require_once("privatemarket.php");

class PrivateMtGox extends PrivateMarket
{
	protected $orderUrl;
	protected $openOrdersUrl;
	protected $infoUrl;
	protected $withdrawUrl;
	protected $depositUrl;
	
	protected $tickerUrl = '';
	protected $buyUrl = '';
	protected $sellUrl = '';

	protected $privatekey;
	protected $secret;
	
	protected $ch = NULL;

	public function __construct($currency, $clientID, $key, $secret)
	{
		global $config;

		parent::__construct($currency, $clientID, $key, $secret);
		$this->orderUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/private/order/result');
		$this->openOrdersUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/private/orders');
		$this->infoUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/private/info');
		$this->withdrawUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/bitcoin/send_simple');
		$this->depositUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/bitcoin/address');
	}
	
	protected function _loadClient($clientID, $key, $secret)
	{
		$this->privatekey = $key;
		$this->secret = $secret;
	}

	protected function _createNonce()
	{
		$mt = explode(' ', microtime());
		return $mt[1].substr($mt[0], 2, 6);
	}

	protected function _changeCurrencyUrl($url, $currency)
	{
		return preg_replace('/BTC\w{3}/', 'BTC'.$currency, $url);
	}

	protected function _toIntPrice($price, $currency)
	{
		$retPrice = 0;
		$curArray = array("USD", "EUR", "GBP", "PLN", "CAD", "AUD", "CHF", "CNY","NZD", "RUB", "DKK", "HKD", "SGD", "THB");
        		if (in_array($currency, $curArray)) {
			$retPrice = (int) ($price * 100000);
		} else if (in_array($current, array("JPY", "SEK"))) {
			$retPrice = (int) ($price * 1000);
		}
		return $retPrice;
           	}

	protected function _toIntAmount($amount)
	{
		return (int) ($amount * 100000000);
	}

	protected function _fromIntAmount($amount)
	{
		return (float) ($amount / 100000000);
	}

	protected function _fromIntPrice($amount)
	{
		return ($amount / 100000);
	}

	protected function _sendRequest($url, $params, $extraHeaders=NULL)
	{
		$rUrl = $url['url'];
		iLog("[PrivateMtGox] Sending Request: {$rUrl}");
		
		if ($rUrl != $this->infoUrl['url'] && $rUrl != $this->tickerUrl['url']) {
			iLog("[PrivateMtGox] WARNING: Request not sent. Live sell and buy functions currently disabled.");
			$response = array();
			$response['result'] = 'error';
			$response['return'] = NULL;
			return $response; 
		}
		
		$params = array();
		// must have a unique incrementing nonce for every private request
		$params['nonce'] = $this->_createNonce();
		
		// generate the POST data string
        $post_data = http_build_query($params, '', '&');

        // set up header
        $headers = array(
			'Rest-Key : '.$this->privatekey,
			'Rest-Sign : '.base64_encode(hash_hmac('sha512', $post_data, base64_decode($this->secret), true))
        );
		
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
		
        $json = json_decode($res, true);
        if (!$json) {
            throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
		}
        return $json;
	}

	public function trade($amount, $ttype, $price=0)
	{
		if ($price) {
			$price = $this->_toIntPrice($price, $this->currency);
		}
		$amount = $this->_toIntAmount($amount);
		
		$this->buyUrl['url'] = $this->_changeCurrencyUrl($this->buyUrl['url'], $this->currency);

		$params = array("nonce" => $this->_createNonce(), "amount_int" => $amount, "type" => $ttype);
		if ($price) {
			$params["price_int"] = $price;
		}
		
		try {
			$response = $this->_sendRequest($buyUrl, $params);
			if ($response && isset($response['result']) && $response['result'] == 'success'){
				return $response['return'];
			}
		} catch (Exception $e) {
			iLog("[PrivateMtGox] ERROR: Trade failed - ".$e->getMessage());
		}
		return NULL;
	}

	protected function _buy($amount, $price)
	{
		iLog("[PrivateMtGox] Create BUY limit order {$amount} @{$price}USD");
		return $this->trade($amount, "bid", $price);
	}

	protected function _sell($amount, $price)
	{
		iLog("[PrivateMtGox] Create SELL limit order {$amount} @{$price}USD");
		return $this->trade($amount, "ask", $price);
	}

	public function withdraw($amount, $address)
	{
		$params = array("nonce" => $this->_createNonce(), 
					"amount_int" => $this->_toIntAmount($amount), 
					"address" => $address);
		
		try {
			$response = $this->_sendRequest($this->withdrawUrl, $params);
			if ($response && isset($response['result']) && $response['result'] == 'success') {
				return $response['return'];
			}
		} catch (Exception $e) {
			iLog("[PrivateMtGox] ERROR: Withdraw failed - ".$e->getMessage());
		}
		return NULL;
	}

	public function deposit()
	{
		$params = array("nonce" => $this->_createNonce());
		try {
			$response = $this->_sendRequest($this->depositUrl, $param);
			if ($response && isset($response['result']) && $response['result'] == 'success') {
				return $response['return'];
			}
		} catch (Exception $e) {
			iLog("[PrivateMtGox] ERROR: Deposit failed - ".$e->getMessage());
		}
		return NULL;
	}

}

?>