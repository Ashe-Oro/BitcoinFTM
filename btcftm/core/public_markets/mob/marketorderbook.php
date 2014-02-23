<?php
require_once("askorderbook.php");
require_once("bidorderbook.php");

class MarketOrderBook
{
	protected $askOrderBook;
	protected $bidOrderBook;
	
	public function __construct($askArray=array(), $bidArray=array())
	{
		iLog("[MarketOrderBook] Creating new Market Order Book...");
		//var_dump($askArray);
		$this->askOrderBook = new AskOrderBook($askArray);
		$this->bidOrderBook = new BidOrderBook($bidArray);
	}
	
	public function getAskOrderBook()
	{
		return $this->askOrderBook;
	}
	
	public function getBidOrderBook()
	{
		return $this->bidOrderBook;
	}
	
	public function getAskTopOrder()
	{
		return $this->askOrderBook->getTopOrder();
	}
	
	public function getBidTopOrder()
	{
		return $this->bidOrderBook->getTopOrder();
	}

	function printOrderBooks($mname="", $tablecell=true)
	{
		$str = "";
		$str .= $this->askOrderBook->printOrderBook($mname, $tablecell);
		$str .= $this->bidOrderBook->printOrderBook($mname, $tablecell);
		return $str;
	}
}
?>