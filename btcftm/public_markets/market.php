<?php
require_once("ticker.php");

/**
 * CLASS: Market
 *
 * Represents an exchange market in a given currency
 */
abstract class Market
{
	public $name = '';
	public $currency = '';
	public $depthUpdated = 0;
	public $updateRate = 60;

	public $fc = NULL; // currency converter object, not yet needed

	/**
	 * Creates a new Market
	 *
	 * @param	{string}	currency	market currency (ie USD)
	 */
	public function __construct($currency)
	{
		$this->name = get_class($this);
		$this->currency = $currency;
	}

	/**
	 * Attempts to get the market depth based on the market order book
	 */
	public function getDepth()
	{
		global $config;

		$timeDiff = time() - $this->depthUpdated;
		if ($timeDiff > $this->updateRate) {
			$this->askUpdateDepth();
		}
		$timeDiff = time() - $this->depthUpdated;
		if ($timeDiff > $config['marketExpirationTime']) {
			iLog("[Market] WARNING: Market {$this->name} order book is expired");
			$this->depth = array('asks' => array('price' => 0, 'amount' => 0), 'bids' => array('price' => 0, 'amount' => 0));
		}
		return $this->depth;
	}

	/**
	 * Converts other currencies to USD
	 */
	public function convertToUSD()
	{
		if($this->currency == 'USD') { return; }
		// otherwise do other stuff, but we're only in USD right now
	}

	/**
	 * Updates market depth
	 */
	public function askUpdateDepth()
	{
		try {
			$this->updateDepth();
			$this->convertToUSD();
			$this->depthUpdated = time();
		} catch (Exception $e) {
			iLog("[Market] Can't update market: {$this->name} - {$e->getMessage()}");
		}
	}

	/**
	 * Gets the best ask/bid pairs from the order book
	 */
	public function getTicker()
	{
		$depth = $this->getDepth();
		$res = array('ask' => 0, 'bid' => 0);
		if (count($depth['asks']) && count($depth['bids'])) {
			$res['ask'] = $depth['asks'][0];
			$res['bid'] = $depth['bids'][0];
		}
		return $res;
	}
	
	/**
	 * Compares two objects with prices against each other
	 *
	 * @param	{object}	a	first price object
	 * @param	{object}	b	second price object
	 * @return	{int}			1 if a > b, -1 if a < b, 0 if a == b
	 */
	static public function comparePrice($a, $b)
	{
		if ($a['price'] == $b['price']) {
			return 0;
		}
		return ($a['price'] > $b['price']) ? 1 : -1;
	} 

	/** ABSTRACT FUNCTIONS **/
	abstract public function updateDepth();
	abstract public function getCurrentTicker();
}
?>