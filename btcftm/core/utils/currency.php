<?php
class Currency
{
	public $id;
	public $name;
	public $abbr;
	public $symbol;

	public $amount = 0;

	private $prefix;
	private $precision;

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
			}
		}
	}

	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	public function __toString()
	{
		$num = number_format($this->amount, $this->precision);
		return ($this->prefix) ? $this->symbol.$num : $num.$this->symbol ;
	}
}

function printCurrency($amount, $abbr)
{
	$c = new Currency($amount, $abbr);
	return "{$c}";
}
?>