<?php
class Order
{
	private $price = 0;
	private $amount = 0;
	
	public function __construct($price, $amount)
	{
		$this->price = $price;
		$this->amount = $amount;
	}
	
	public function getPrice()
	{
		return $this->price;
	}
	
	public function getAmount()
	{
		return $this->amount;
	}
	
	public function getValue()
	{
		return $this->price * $this->amount;
	}
}
?>