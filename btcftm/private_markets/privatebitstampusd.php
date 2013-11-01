<?php
require_once('privatemarket.php');

class PrivateBitstampUSD extends PrivateMarket
{
	private $balanceUrl = "https://www.bitstamp.net/api/balance/";
    private $buyUrl = "https://www.bitstamp.net/api/buy/";
    private $sellUrl = "https://www.bitstamp.net/api/sell/";

    private $privatekey = '';
    private $secret = '';

	public function __construct()
	{
		parent::__construct("USD");
		$this->privatekey = $config['bitstamp_key'];
		$this->secret = $config['bitstamp_secret'];
		
		$this->getInfo();
	}

	protected function _sendRequest($url, $params, $extraHeaders=NULL)
	{
		$rUrl = $url;		
		$response = array();
		$response['result'] = 'success';
		$response['return'] = false;
		iLog("[PrivateBitstampUSD] Sending Request: {$rUrl}");
		
		
		if ($rUrl == $this->buyUrl['url'] || $rUrl == $this->sellUrl['url']) {
			iLog("[PrivateBitstampUSD] WARNING: Request not sent. Live sell and buy functions currently disabled.");
			return $response; 
		}
		
		$headers = array(
            'Content-type' => 'application/json',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT'
        );
			
		
        /*
        if extra_headers is not None:
            for k, v in extra_headers.items():
                headers[k] = v

        params['user'] = self.username
        params['password'] = self.password
        postdata = urllib.parse.urlencode(params).encode("utf-8")
        req = urllib.request.Request(url, postdata, headers=headers)
        response = urllib.request.urlopen(req)
        code = response.getcode()
        if code == 200:
            jsonstr = response.read().decode('utf-8')
            return json.loads(jsonstr)
        return None
        */
        return NULL;
	}

	protected function _buy($amount, $price)
	{
		iLog("[PrivateBitstampUSD] Create BUY limit order {$amount} @{$price}USD");
		$params = array('amount' => $amount, 'price' => $price);
		$response = $this->_sendRequest($this->buyUrl, $params);
		if (isset($response['error'])) {
			iLog("[PrivateBitstampUSD] ERROR: Buy failed {$response['error']}");
		}
	}

	protected function _sell($amount, $price)
	{
		iLog("[PrivateBitstampUSD] Create SELL limit order {$amount} @{$price}USD");
		$params = array('amount' => $amount, 'price' => $price);
		$response = $this->_sendRequest($this->sellUrl, $params);
		if ($response && isset($response['error'])) {
			iLog("[PrivateBitstampUSD] ERROR: Sell failed {$response['error']}");
		}
	}

	public function getInfo()
	{
		$params = array();
		$response = $this->_sendRequest($this->balanceUrl, $params);
		if($response) {
			$this->btcBalance = (float) $response['btc_available'];
			$this->usdBalance = (float) $response['usd_available'];
			iLog("[PrivateBitstampUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
			return true;
		}
		return false;
	}
}
?>