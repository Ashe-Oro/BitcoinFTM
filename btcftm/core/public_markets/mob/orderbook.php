<?php
require_once("order.php");

abstract class OrderBook 
{
	protected $orders = NULL;
	protected $type = "";
	
	public function __construct($orders)
	{
		$this->orders = array();
		//var_dump($orders);
		foreach($orders as $o){
			$this->addOrder($o);
		}
		$this->_sortOrderBook();
	}
	
	abstract protected function _sortOrderBook();
	
	protected function _sortAndFormat($reverse=false)
	{
		if ($reverse) {
			usort($this->orders, array("OrderBook", "comparePrice"));
			$this->orders = array_reverse($this->orders);
		} else {
			usort($this->orders, array("OrderBook", "comparePrice"));
		}
		return $this->orders;
	}
	
	static public function comparePrice($a, $b)
	{
		if ($a->getPrice() == $b->getPrice()) {
			return 0;
		}
		return ($a->getPrice() > $b->getPrice()) ? -1 : 1;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getOrders()
	{
		return $this->orders;
	}
	
	public function getOrdersCount()
	{
		return count($this->orders);
	}
	
	public function getTopOrder()
	{
		return ($this->getOrdersCount()) ? $this->orders[0] : NULL;
	}
	
	public function addOrder($order)
	{
		if (is_array($order)){
			//iLog("[OrderBook] addOrder ARRAY {$order[0]} {$order[1]}");
			$this->orders[] = new Order($order[0], $order[1]);
		} else {
			//iLog("[OrderBook] addOrder OBJ ".$order->price.' '.$order->amount);
			$this->orders[] = new Order($order->price, $order->amount);
		}
	}
}
?>