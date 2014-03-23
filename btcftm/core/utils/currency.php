<?php
class Currency
{
	public $id;
	public $name;
	public $abbr;
	public $symbol;
	public $color;

	public $amount = 0;

	public $prefix;
	public $precision;

	public function __construct($amount, $args)
	{
		global $DB;

		if ($args){
			if (is_string($args)){
				$res = $DB->query("SELECT * FROM currencies WHERE abbr = '{$args}'");
				$args = $DB->fetch_array_assoc($res);
			}
			if (is_array($args) && isset($args['currency_id'])){
				$this->amount = $amount;

				$this->id = $args['currency_id'];
				$this->name = $args['name'];
				$this->abbr = $args['abbr'];
				$this->symbol = $args['symbol'];
				$this->prefix = $args['prefix'];
				$this->precision = $args['precision'];
				$this->color = $args['color'];
			}
		}
	}

	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	public function setPrecision($precision)
	{
		if ($precision > 0) {
			$this->precision = $precision;
		}
	}

	public function __toString()
	{
		$num = number_format($this->amount, $this->precision);
		return ($this->prefix) ? $this->symbol.$num : $num.$this->symbol ;
	}

	public function getColor()
	{
		return "#{$this->color}";
	}
}

function printCurrency($amount, $abbr, $precision=0, $hardcode=false)
{
	if (!$hardcode) {
		$c = new Currency($amount, $abbr); // DB-driven, but VERY slow when there are lots of calls to this function
		return "{$c}";
	} else {
		// I hate hardcoding but it's way faster that tons of DB calls
		
		$args['currency_id'] = 0; // not important for this function
		switch (strtolower($abbr)){
			case 'usd':
				$args['name'] = "US Dollar";
				$args['abbr'] = strtoupper($abbr);
				$args['symbol'] = '$';
				$args['prefix'] = true;
				$args['precision'] = 4;
				break;

			case 'eur':
				$args['name'] = "EU Euro";
				$args['abbr'] = strtoupper($abbr);
				$args['symbol'] = '&euro;';
				$args['prefix'] = true;
				$args['precision'] = 4;
				break;

			case 'btc':
				$args['name'] = "Bitcoin";
				$args['abbr'] = strtoupper($abbr);
				$args['symbol'] = '&nbsp;BTC';
				$args['prefix'] = false;
				$args['precision'] = 8;
				break;

			case 'ltc':
				$args['name'] = "Lightcoin";
				$args['abbr'] = strtoupper($abbr);
				$args['symbol'] = '&nbsp;LTC';
				$args['prefix'] = true;
				$args['precision'] = 8;
				break;
		}
		
		$prec = $precision > 0 ? $precision : $args['precision'];
		$num = number_format($amount, $prec);
		return ($args['prefix']) ? $args['symbol'].$num : $num.$args['symbol'];
	}
}
?>