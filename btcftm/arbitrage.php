<?php
require_once("arbitrer.php");

/**
 * CLASS: Arbitrage
 */
class Arbitrage
{
	public $arbitrer = NULL; 
	public $client = NULL;
	public $pmarkets = array();
	
	/**
	 * CONSTRUCTOR: Creates new Arbitrage object
	 *
	 * @param	$args	{array}		arbitrage arguments
	 */
	public function __construct($client, $args="")
	{
		global $config; 
		
		if ($config['echoLog']) { echo "<hr />\n"; }
		iLog("[Arbitrage] PHASE 1: ACQUIRE BITCOINS");
		iLog("[Arbitrage] Config loaded - refreshRate: {$config['refreshRate']}, marketExpirationTime: {$config['marketExpirationTime']}");
		
		$this->client = $client;
		iLog("[Arbitrage] Client loaded - username: ".$client->getUsername());
		iLog("[Arbitrage] Client settings loaded - maxTxVolume: ".$client->getMaxTxVolume()." minTxVolume: ".$client->getMinTxVolume()." balanceMargin: ".$client->getBalanceMargin()." profitThresh: ".$client->getProfitThresh()." percThresh: ".$client->getPercThresh());
		
		$this->createArbitrer($client, $args);
	}

	/**
	 * Executes a command
	 *
	 * @param	$cmds	{string}		arbitrage commands
	 */
	public function execCommand($cmds)
	{
		if (strlen($cmds)){
			iLog("[Arbitrage] Execute command: {$cmds}");
			switch($cmds) {
				case "watch":
					$this->arbitrer->loop();
					break;
					
				case "replay-history":
					/** DO NOTHING NOW
					if (isset($args['replay_history'])) {
						$this->arbitrer->replayHistory($args['replay_history']);
					}
					*/
					break;
					
				case "get-balance":
					/** DO NOTHING NOW 
					if (isset($args['markets'])) {
						$this->getBalance($args['markets']);
					}
					**/
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
	public function createArbitrer($client, $args)
	{
		global $config;
		
		$this->arbitrer = new Arbitrer($client, $args); // register a new arbitrer
		
		iLog("[Arbitrage] New Arbitrer created");
		
		// initializes arbitrer observers
		$obs = isset($args['observers']) ? $args['observers'] : (isset($config['observers'])) ? $config['observers'] : NULL;
		if ($obs) { $this->arbitrer->initObservers($obs); }
		
		iLog("[Arbitrage] Observers loaded");
		
		// initializes arbitrer markets
		$markets = isset($args['markets']) ? $args['markets'] : (isset($config['markets'])) ? $config['markets'] : NULL;
		if ($markets) { 
			$this->arbitrer->initMarkets($markets); 
			//$this->pmarkets = $this->getBalance($markets);
		}
		
		iLog("[Arbitrage] Markets loaded");
	}
	
	public function getArbitrer()
	{
		return $this->arbitrer;
	}
}
?>