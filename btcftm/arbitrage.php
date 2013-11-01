<?php
require_once("arbitrer.php");

/**
 * CLASS: Arbitrage
 */
class Arbitrage
{
	public $arbitrer = NULL; 
	public $pmarkets = array();
	
	/**
	 * CONSTRUCTOR: Creates new Arbitrage object
	 *
	 * @param	$args	{array}		arbitrage arguments
	 */
	public function __construct($args)
	{
		global $config; 
		
		iLog("[Arbitrage] PHASE 1: ACQUIRE BITCOINS");
		iLog("[Arbitrage] Config loaded - refreshRate: {$config['refreshRate']}, marketExpirationTime: {$config['marketExpirationTime']}");
		iLog("[Arbitrage] Config params set - maxTxVolume: {$config['maxTxVolume']}, minTxVolume: {$config['minTxVolume']}, balanceMargin: {$config['balanceMargin']}, profitThresh: {$config['profitThresh']}, percThresh: {$config['percThresh']}");
		$this->createArbitrer($args);
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
		global $config;
		
		$this->arbitrer = new Arbitrer(); // register a new arbitrer
		
		iLog("[Arbitrage] New Arbitrer created");
		
		// initializes arbitrer observers
		$obs = isset($args['observers']) ? $args['observers'] : (isset($config['observers'])) ? $config['observers'] : NULL;
		if ($obs) { $this->arbitrer->initObservers($obs); }
		
		iLog("[Arbitrage] Observers loaded");
		
		// initializes arbitrer markets
		$markets = isset($args['markets']) ? $args['markets'] : (isset($config['markets'])) ? $config['markets'] : NULL;
		if ($markets) { 
			$this->arbitrer->initMarkets($markets); 
			$this->pmarkets = $this->getBalance($markets);
		}
		
		iLog("[Arbitrage] Markets loaded");
	}
	
	public function getBalance($markets)
	{
		iLog("[Arbitrage] Getting private market balances...");
		if ($markets) {
            $pmarketsi = array();
            foreach ($markets as $pmarket_name) {
				$pFile = "./private_markets/private".strtolower($pmarket_name).".php";
				if (file_exists($pFile)){
					require_once($pFile);
					$pName = "private".$pmarket_name;
					try {
						$pmarket = new $pName();
						array_push($pmarketsi, $pmarket);
						iLog("[Arbitrage] Balance for {$pmarket_name} - USD: ".$pmarket->getBalance('USD')." BTC: ".$pmarket->getBalance('BTC'));
					} catch (Exception $e) {
						iLog("[Arbitrage] ERROR: Private market construct function invalid - {$pmarket_name} - ".$e->getMessage());
					}
				} else {
					iLog("[Arbitrage] ERROR: Private market file not found - {$pFile}");
				}
			}
			return $pmarketsi;
		} else {
			iLog("[Arbitrage] ERROR: No private markets set.");
		}
		return NULL;
	}
	
	public function getArbitrer()
	{
		return $this->arbitrer;
	}
}
?>