<?php
require_once("trade.php");

/**
 * CLASS: SimpleTrade
 *
 * This class will contain single transactions, one buy and one sell, that comprise a simple arbitrage trade strategy
 */
class SimpleTrade
{
	public function __construct($trade=NULL)
	{
		parent::__construct($trade);
	}
}
?>