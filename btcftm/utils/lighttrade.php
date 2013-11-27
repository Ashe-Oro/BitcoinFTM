<?php
require_once("trade.php");

/**
 * CLASS: LightTrade
 *
 * This class will contain multiple transactions, both buy and sell, that comprise a single BTC<->LTC trade strategy
 */
class LightTrade
{
	public function __construct($trade=NULL)
	{
		parent::__construct($trade);
	}
}
?>