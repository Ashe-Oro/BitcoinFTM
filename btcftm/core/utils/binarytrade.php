<?php
require_once("trade.php");

/**
 * CLASS: BinaryTrade
 *
 * This class will contain multiple transactions, both buy and sell, that comprise a single binary trade strategy
 */
class BinaryTrade
{
	public function __construct($trade=NULL)
	{
		parent::__construct($trade);
	}
}
?>