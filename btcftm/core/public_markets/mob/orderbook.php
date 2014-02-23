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
			//var_dump($order);
			//iLog("[OrderBook] addOrder ARRAY {$order[0]} {$order[1]}");
			$this->orders[] = new Order($order[0], $order[1]);
		} else {
			//iLog("[OrderBook] addOrder OBJ ".$order->price.' '.$order->amount);
			$this->orders[] = new Order($order->price, $order->amount);
		}
	}

	public function printOrderBook($mname="", $tablecell=true)
  {
  	//$this->_sortOrderBook();
    $td = $tablecell ? "td" : "div";
    $str = "";
    $dark = ($this->type == 'ask') ? 1 : 2; 
  	$str .= "<{$td} class='orderbook-list-col mkt-bg-dark{$dark}-{$mname} {$this->type}-list-price {$this->type}-list-price-{$mname}'><div class='orderbook-list-wrapper orderbook-list-wrapper-{$mname}'>";
	  foreach($this->orders as $o){
	    $str .= "<span class='orderbook-list-item {$this->type}-list-price-item {$this->type}-list-price-item-{$mname}' data-price='".$o->getPrice()."'>";
	    $str .= printCurrency($o->getPrice(), "USD", 2, true)."</span>";
	  }
  	$str .= "</div></{$td}>";
  	$dark = ($this->type == 'ask') ? 3 : 4; 
	  $str .= "<{$td} class='orderbook-list-col mkt-bg-dark{$dark}-{$mname} {$this->type}-list-volume {$this->type}-list-volume-{$mname}'><div class='orderbook-list-wrapper orderbook-list-wrapper-{$mname}'>";
	  foreach($this->orders as $o){
	    $str .= "<span class='orderbook-list-item {$this->type}-list-volume-item {$this->type}-list-volume-item-{$mname}' data-volume='".$o->getAmount()."'>";
	    $str .= printCurrency($o->getAmount(), "BTC", 2, true)."</span>";
	  }
	  $str .= "</div></{$td}>";
	  return $str;
  }
}
?>