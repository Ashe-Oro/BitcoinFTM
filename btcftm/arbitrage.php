<?php
require_once("arbitrer.php");

/**
 * CLASS: Arbitrage
 */
class Arbitrage
{
	public $arbitrer = NULL; 
	
	/**
	 * CONSTRUCTOR: Creates new Arbitrage object
	 *
	 * @param	$args	{array}		arbitrage arguments
	 */
	public function __construct($args)
	{
		
	}

	/**
	 * Executes a command
	 *
	 * @param	$args	{array}		arbitrage arguments
	 */
	public function execCommand($args)
	{
		$cmds = '';
		if (isset($args['commands'])){
			$cmds = $args['commands'];
		}
		
		if (strlen($cmds)){
			switch($cmds) {
				case "watch":
					$this->arbitrer->loop();
					break;
					
				case "replay-history":
					if (isset($args['replay_history'])) {
						$this->arbitrer->replayHistory($args['replay_history']);
					}
					break;
					
				case "get-balance":
					if (isset($args['markets'])) {
						$this->getBalance($args['markets']);
					}
					break;
					
				default:
					break;
			}
		}
	}

	/**
	 * Creates an arbitrer for arbitrage and registers observers/markets
	 *
	 * @param	$args	{array}		arbitrage arguments
	 */
	public function createArbitrer($args)
	{
		$this->arbitrer = new Arbitrer(); // register a new arbitrer
		
		// initializes arbitrer observers
		$obs = isset($args['observers']) ? $args['observers'] : NULL;
		if ($obs) { $this->arbitrer->initObservers($obs.split(",")); }
		
		// initializes arbitrer markets
		$markets = isset($args['markets']) ? $args['markets'] : NULL;
		if ($markets) { $this->arbitrer->initMarkets($markets.split(",")); }
	}
	
	public function getBalance($markets)
	{
		if ($markets && strlen($markets)) {
			$pmarkets = $markets.split(",");
            $pmarketsi = array();
            foreach ($pmarkets as $pmarket) {
				/* *** REPLACE THIS WITH PHP EVAL ONCE PRIVATE MARKET CLASSES ARE FINISHED
                exec('import private_markets.' + pmarket.lower())
                market = eval('private_markets.' + pmarket.lower()
                              + '.Private' + pmarket + '()')
                pmarketsi.append(market)
            for market in pmarketsi:
                print(market)
				*/
			}
		}
	}
}
?>