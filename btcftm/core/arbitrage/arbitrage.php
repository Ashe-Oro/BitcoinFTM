<?php
require_once("./core/utils/clientsList.php");
require_once("./core/public_markets/mob/mob.php");
require_once("arbitrer.php");

/**
 * CLASS: Arbitrage
 */
class Arbitrage
{
	public $arbitrers = array(); 
	public $clients = array();
	
	public $markets = array();
	public $mob = NULL;
	public $market_names = array();
	public $depths = array();
	
	private $timestamp = 0;
	
	private $useHistorical = false;
	
	/**
	 * CONSTRUCTOR: Creates new Arbitrage object
	 *
	 * @param	$clienstList {Object}	list of Clients
	 * @param	$args		{array}		arbitrage arguments
	 */
	public function __construct($clientsList=NULL, $args="")
	{
		global $config; 
		
		if ($config['echoLog']) { echo "<hr />\n"; }
		iLog("[Arbitrage] PHASE 1: ACQUIRE BITCOINS");
		
		// switch on historical mode here
		if (isset($args['history'])){
			$this->useHistorical = $args['history'];
		}
		
		// load the markets
		$this->_loadMarkets($args);
		
		
		// load the MOB
		$this->_loadMOB();
		
		// load the client list
		$this->clients = ($clientsList == NULL) ? new ClientsList() : $clientsList;
		$cArray = $this->clients->getClientsList();
		
		foreach($cArray as $client) {
			iLog("[Arbitrage] Client loaded - username: ".$client->getUsername());
			// create an arbitrer for each client
			$arb = $this->createArbitrer($client, $args);
			$this->arbitrers[$client->getID()] = $arb;
		}
	}
	
	private function _loadMarkets($args)
	{
		global $config;
		
		// initializes arbitrer markets
		$markets = isset($args['markets']) ? $args['markets'] : (isset($config['markets'])) ? $config['markets'] : NULL;
		
		if ($markets && count($markets)) {
			iLog("[Arbitrage] Loading ".count($markets)." markets...");
			$this->market_names = $markets;
			$mFolder = ($this->useHistorical) ? "history_markets" : "live_markets";
			$mPrefix = ($this->useHistorical) ? "history" : "";
			
			$mLoaded = 0;
			foreach($markets as $market_name){
				$mFile = "./core/public_markets/{$mFolder}/{$mPrefix}".strtolower($market_name).".php";
				if (file_exists($mFile)){
					require_once($mFile);
					try {
						$market_name = ($this->useHistorical) ? "history{$market_name}" : $market_name;
						$market = new $market_name();
						array_push($this->markets, $market);
						$mLoaded++;
						iLog("[Arbitrage] {$market_name} loaded.");
					} catch (Exception $e) {
						iLog("[Arbitrage] ERROR: Market construct function invalid - {$market_name} - ".$e->getMessage());
					}
				} else {
					iLog("[Arbitrage] ERROR: Market file not found - {$mFile}");
				}		
			}
			iLog("[Arbitrage] {$mLoaded}/".count($markets)." markets loaded.");
		} else {
		}
		
		iLog("[Arbitrage] ".count($markets)." markets loaded");
	}
	
	private function _loadMOB()
	{
		iLog("[Arbitrage] Loading the MOB...");
		$this->mob = new MOB($this->markets);
		iLog("[Arbitrage] MOB loaded!");
	}
	
	public function updateMarketDepths()
	{
		iLog("[Arbitrage] Updating market depths...");
		
		$this->depths = array();
		foreach($this->markets as $market){
			$this->depths[$market->name] = $this->_getMarketDepth($market);
		}

		iLog("[Arbitrage] Market depths updated");
				
		return $this->depths;
	}
	
	private function _getMarketDepth($market)
	{
		$this->depths[$market->name] = $market->updateMarketDepth();
		return $this->depths[$market->name];
	}
	
	public function getMarketTickers()
	{
		foreach($this->markets as $market) {
			$ticker = $market->getTicker();
			iLog("[Arbitrage] Ticker {$market->name} - Ask: {$ticker['ask']['price']} Bid: {$ticker['bid']['price']}");
		}
	}
	
	public function setTimestamp($timestamp, $period)
	{
		$this->timestamp = $timestamp;
		$this->period = $period;
		$this->updateMarketTimestamps();
	}
	
	public function updateMarketTimestamps()
	{
		if ($this->useHistorical){
			iLog("[Arbitrage] Updating market timestamps to ".date("d M Y H:i:s", $this->timestamp)."...");
			foreach($this->markets as $market){
				$market->updateTimestamp($this->timestamp, $this->period);
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
		
		$arb = new Arbitrer($client, $args); // register a new arbitrer
		
		iLog("[Arbitrage] New Arbitrer created for ".$client->getUsername());
		
		// initializes global arbitrer observers
		$obs = isset($args['observers']) ? $args['observers'] : (isset($config['observers'])) ? $config['observers'] : NULL;
		if ($obs) { $arb->initObservers($obs); }
		iLog("[Arbitrage] Observers loaded");
		
		$bots = $client->getTraderBots();
		if ($bots && count($bots)) { $arb->initTraderBots($bots); }
		iLog("[Arbitrage] Trader Bots loaded");
		
		return $arb;
	}
	
	public function getArbitrer()
	{
		return $this->arbitrers;
	}
	
	/**
	 * Executes a command
	 *
	 * @param	$cmds	{string}		arbitrage commands
	 */
	public function execCommand($cmds)
	{
		if (strlen($cmds)){
			iLog("[Arbitrage] Execute command: {$cmds} on ".count($this->arbitrers)." Arbitrers");
			switch($cmds) {
				case "watch":
					$this->updateMarketDepths();
					foreach($this->arbitrers as $clientID => $arb) {
						$arb->watchMarkets($this->markets, $this->mob);
					}
					break;
					
				case "sim":
					$start = (isset($args['start'])) ? strtotime($args['start']) : strtotime("-1 month");
					$end = (isset($args['end'])) ? strtotime($args['end']) : time();
					$period = (isset($args['period'])) ? $args['period'] : PERIOD_1H;
					$this->period = $period;
					
					$tick = $start;
					while($tick < $end) {
						iLog("<hr />");
						iLog("PHASE 1: ACQUIRE BITCOINS...");
						iLog("[Arbitrage] Running sim for ".date("d M Y H:i:s", $tick));
						
						$next = $tick + $period;
						$this->setTimestamp($tick, $period);
						//$this->updateMarketDepths();
						$this->_loadMob();
						
						foreach($this->arbitrers as $clientID => $arb){
							$arb->setTimestamp($tick, $this->period);
							$arb->watchMarkets($this->markets, $this->mob);
						}
						
						$tick = $next;
					}
					break;
					
				default:
					break;
			}
		}
	}
}
?>