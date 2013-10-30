<?php
abstract class Market
{
	public $name = '';
	public $currency = '';
	public $depthUpdated = 0;
	public $updateRate = 60;

	public $fc = NULL; // currency converter object, not yet needed

	public function __construct($currency)
	{
		$this->name = get_class($this);
		$this->currency = $currency;
	}

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

	public function convertToUSD()
	{
		if($this->currency == 'USD') { return; }
		// otherwise do other stuff, but we're only in USD right now
	}

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

	public function getTicker()
	{
		$depth = $this->getDepth();
		$res = array('ask' => 0, 'bid' => 0);
		if (array_len($depth['asks']) && array_len($depth['bids'])) {
			$res['ask'] = $depth['asks'][0];
			$res['bid'] = $depth['bids'][0];
		}
		return $res;
	}
	
	static public function comparePrice($a, $b)
	{
		if ($a['price'] == $b['price']) {
			return 0;
		}
		return ($a['price'] > $b['price']) ? 1 : -1;
	} 

	abstract public function updateDepth();
}
?>