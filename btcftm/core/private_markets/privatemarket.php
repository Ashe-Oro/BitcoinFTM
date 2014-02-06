<?php
class PrivateMarket
{
	public $name = '';
	public $mname = '';
	public $publicmarketid = 0;
	public $currency = '';

	protected $btcBalance = 0;
	protected $eurBalance = 0;
	protected $usdBalance = 0;
	protected $ltcBalance = 0;

	protected $privatekey = '';
    protected $secret = '';
	
	public $clientId = 0;

	public $fc = NULL; // future currency converter

	public function __construct($currency, $clientID, $key, $secret)
	{
		$this->name = get_class($this);
		$this->mname = str_replace("Private", "", str_replace("USD", "", $this->name));
		$this->currency = $currency;
		$this->btcBalance = 0;
		$this->eurBalance = 0;
		$this->usdBalance = 0;
		$this->ltcBalance = 0;
		$this->clientId = $clientID;
		$this->_setPublicMarket();
		$this->_loadClient($clientID, $key, $secret);
	}

	protected function _setPublicMarket()
	{
		global $DB;
		$query = "SELECT id FROM markets WHERE name = '".strtolower($this->mname)."'";
		$res = $DB->query($query);
		if ($res){ 
			$row = $DB->fetch_array_assoc($res);
			$this->publicmarketid = $row['id'];
		}
	}

	protected function _str()
	{
		$str = "{$this->name}: [btc_balance: {$this->btc_balance}, eur_balance: {$this->eur_balance}, usd_balance: {$this->usd_balance}]";
		return $str;
	}

	public function buy($amount, $price)
	{
		$localPrice = $price;
		//$localPrice = $this->fc->convert($price, 'USD', $this->currency);

		iLog("[PrivateMarket] Buy {$amount}BTC at {$this->name} @{$localPrice}{$this->currency}");
		$this->_buy($amount, $localPrice);
	}

	public function sell($amount, $price)
	{
		$localPrice = $price;
		//$localPrice = $this->fc->convert($price, 'USD', $this->currency);

		iLog("[PrivateMarket] Sell {$amount} BTC at {$this->name} @{$localPrice}{$this->currency}");
		$this->_sell($amount, $localPrice);
	}

	protected function _buy($amount, $price)
	{
		throw NotImplementedError("{$this->name}");
	}

	protected function _sell($amount, $price)
	{
		throw NotImplementedError("");
	}

	public function deposit()
	{
		throw NotImplementedError("");
	}

	public function withdraw()
	{
		throw NotImplementedError("");
	}

	public function getInfo()
	{
		throw NotImplementedError("");
	}
	
	protected function _loadClient($clientID, $key, $secret)
	{
		throw NotImplementedError("");
	}
	
	public function getBalance($currency) {
		switch(strtolower($currency)){
			case 'btc': { return $this->btcBalance; }
			case 'usd': { return $this->usdBalance; }
			case 'eur': { return $this->eurBalance; }
			case 'ltc': { return $this->ltcBalance; }
		}
	}

	public function getAPISecret()
	{
		return $this->secret;
	}

	public function getAPIKey()
	{
		return $this->privatekey;
	}
}

class NotImplementedError extends Exception
{
	public function __construct($message='', $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>