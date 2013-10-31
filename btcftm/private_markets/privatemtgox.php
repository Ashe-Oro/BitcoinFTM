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

	public function __construct($currency)
	{
		global $config;

		parent::__construct($currency);
		$this->orderUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/private/order/result');
		$this->openOrdersUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/private/orders');
		$this->infoUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/private/info');
		$this->withdrawUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/bitcoin/send_simple');
		$this->depositUrl = array('method' => 'POST', 'url' => 'https://mtgox.com/api/1/generic/bitcoin/address');

		$this->privatekey = $config['mtgox_key'];
		$this->secret = $config['mtgox_secret'];
	}

	protected function _createNonce()
	{
		return time() * 1000000;
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
		return (int) ($amount / 100000000);
	}

	protected function _fromIntPrice($amount)
	{
		return $amount / 100000;
	}

	protected function _sendRequest($url, $params, $extraHeaders=NULL)
	{
		$rUrl = $url['url'];
		iLog("[PrivateMtGox] Sending Request: {$rUrl}");
		$response = array();
		$response['result'] = 'success';
		$response['return'] = false;
		
		if ($rUrl != $this->infoUrl['url'] && $rUrl != $this->tickerUrl['url']) {
			iLog("[PrivateMtGox] WARNING: Request not sent. Live sell and buy functions currently disabled.");
			return $response; 
		}
		/*** PORT THIS OVER TO PHP ***
		urlparams = bytes(urllib.parse.urlencode(params), "UTF-8")
        secret_from_b64 = base64.b64decode(bytes(self.secret, "UTF-8"))
        hmac_secret = hmac.new(secret_from_b64, urlparams, hashlib.sha512)

        headers = {
            'Rest-Key': self.key,
            'Rest-Sign': base64.b64encode(hmac_secret.digest()),
            'Content-type': 'application/x-www-form-urlencoded', 
			*/
            //'Accept': 'application/json, text/javascript, */*; q=0.01',
            /*'User-Agent': 'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT)'
        }
        if extra_headers is not None:
            for k, v in extra_headers.items():
                headers[k] = v
        try:
            req = urllib.request.Request(url['url'],
                                         bytes(urllib.parse.urlencode(params),
                                               "UTF-8"), headers)
            response = urllib.request.urlopen(req)
            if response.getcode() == 200:
                jsonstr = response.read()
                return json.loads(str(jsonstr, "UTF-8"))
        except Exception as err:
            logging.error('Can\'t request MTGox, %s' % err)
		**/
		return $response;
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
		
		$response = $this->_sendRequest($buyUrl, $params);
		if ($response && isset($response['result']) && $response['result'] == 'success'){
			return $response['return'];
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
		
		$response = $this->_sendRequest($this->withdrawUrl, $params);
		if ($response && isset($response['result']) && $response['result'] == 'success') {
			return $response['return'];
		}
		return NULL;
	}

	public function deposit()
	{
		$params = array("nonce" => $this->_createNonce());
		$response = $this->_sendRequest($this->depositUrl, $param);
		if ($response && isset($response['result']) && $response['result'] == 'success') {
			return $response['return'];
		}
		return NULL;
	}

}

?>