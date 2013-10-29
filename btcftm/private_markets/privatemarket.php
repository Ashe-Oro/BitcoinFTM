<?php
class PrivateMarket
{
	public $name = '';
	public $currency = '';
	public $btcBalance = 0;
	public $eurBalance = 0;
	public $usdBalance = 0;

	public $fc = NULL; // future currency converter

	public function __construct($currency)
	{
		$this->name = get_class($this);
		$this->currency = $currency;
		$this->btcBalance = 0;
		$this->eurBalance = 0;
		$this->usdBalance = 0;
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

		iLog("Buy {$amount} BTC at {$localPrice} {$this->currency} @{$this->name}");
		$this->_buy($amount, $localPrice);
	}

	public function sell($amount, $price)
	{
		$localPrice = $price;
		//$localPrice = $this->fc->convert($price, 'USD', $this->currency);

		iLog("Sell {$amount} BTC at {$localPrice} {$this->currency} @{$this->name}");
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
}

class NotImplementedError extends Exception
{
	public function __construct($message='', $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>