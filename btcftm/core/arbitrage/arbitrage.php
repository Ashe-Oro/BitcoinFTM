<?php
require_once("./core/utils/currencyList.php");
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
	public $currencies = array();
	
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

		// load currency profiles from DB
		$this->currencies = new CurrencyList();
		
		// switch on historical mode here
		if (isset($args['history'])){
			$this->useHistorical = $args['history'];
		}
		
		// load the markets
		if (!isset($args['nomarkets'])){
			$this->_loadMarkets($args);
		}

		// set historical timestamp if applicable
		if (isset($args['timestamp']) && isset($args['period'])){
			$this->setTimestamp($args['timestamp'], $args['period']);
		}
		
		
		// load the MOB
		if (!isset($args['nomob'])){
			$this->_loadMOB();
		}
		
		// load the client list
		if (!isset($args['noclients'])){ // client list is now optional (speeds up JSON greatly!)
			$this->clients = ($clientsList == NULL) ? new ClientsList() : $clientsList;
			$cArray = $this->clients->getClientsList();
			
			foreach($cArray as $client) {
				iLog("[Arbitrage] Client loaded - username: ".$client->getUsername());
				// create an arbitrer for each client
				$arb = $this->createArbitrer($client, $args);
				$this->arbitrers[$client->getID()] = $arb;
			}
		}
	}
	
	private function _loadMarkets($args)
	{
		global $config;
		global $DB;
		
		// initializes arbitrer markets
		$markets = isset($args['markets']) ? $args['markets'] : NULL;
		
		// load markets from DB
		if (!$markets) {
			$markets = array();
			$res = $DB->query("SELECT * FROM markets ORDER BY id ASC");
			while($row = $DB->fetch_array_assoc($res)){
				array_push($markets, $row['name']);
			}
		}
		
		// initialize market objects
		if ($markets && count($markets)) {
			iLog("[Arbitrage] Loading ".count($markets)." markets...");
			$this->market_names = $markets;
			$mFolder = ($this->useHistorical) ? "history_markets" : "live_markets";
			$mPrefix = ($this->useHistorical) ? "history" : "";
			
			$mLoaded = 0;
			foreach($markets as $market_name){
				$mFile = "./core/public_markets/{$mFolder}/{$mPrefix}".strtolower($market_name)."usd.php";
				if (file_exists($mFile)){
					require_once($mFile);
					try {
						$market_name = ($this->useHistorical) ? "History{$market_name}USD" : $market_name."USD";
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
	
	public function getArbitrers()
	{
		return $this->arbitrers;
	}

	public function getArbitrer($clientID)
	{
		return ($this->arbitrers[$clientID]) ? $this->arbitrer[$clientID] : NULL;
	}

	public function updateMasterJSON($writeFile=false)
	{
		$json = $this->getMasterJSON();
		if ($writeFile){
			file_put_contents("json/master.json", $json);
		}
		return $json;
	}

	private function getMasterJSON()
	{
		$json = array(
			"timestamp" => 0, 
			"markets" 	=> array(), 
			"mob"				=> array(), 
			"yesterday" => array(
				"markets" => array(),
				"mob" => array()
			),
			"deltas" => array(
				"markets" => array(),
				"mob" => array()
			)
		);

		// add current market values to JSON
		foreach($this->markets as $mkt) {
			$t = $mkt->getCurrentTicker();
			$y = $mkt->getYesterdaysLastTicker();

			$sma10 = $mkt->getSMA(10);
			$sma25 = $mkt->getSMA(25);

			$json['markets'][$mkt->mname] = array(
				"name" => $mkt->mname,
				"currency" => $mkt->currency,
				"high" => $t->getHigh(),
				"low" => $t->getLow(),
				"last" => $t->getLast(),
				"bid" => $t->getBid(),
				"ask" => $t->getAsk(),
				"volume" => $t->getVolume(),
				"commission" => $mkt->commission,
				"sma10" => ($sma10) ? $sma10->getAvg() : -1,
				"sma25" => ($sma25) ? $sma25->getAvg() : -1
			);

			$json['yesterday']['markets'][$mkt->mname] = array(
				"name" => $mkt->mname,
				"currency" => $mkt->currency,
				"high" => $y->getHigh(),
				"low" => $y->getLow(),
				"last" => $y->getLast(),
				"bid" => $y->getBid(),
				"ask" => $y->getAsk(),
				"volume" => $y->getVolume()
			);

			$dhigh = $t->getHigh() - $y->getHigh();
			$dlow = $t->getLow() - $y->getLow();
			$dlast = $t->getLast() - $y->getLast();
			$dbid = $t->getBid() - $y->getBid();
			$dask = $t->getAsk() - $y->getAsk();
			$dsma10 = $t->getLast() - $sma10->getAvg();
			$dsma25 = $t->getLast() - $sma25->getAvg();
			$dvol = $t->getVolume() - $y->getVolume();

			$dhighPerc = $dhigh / $y->getHigh();
			$dlowPerc = $dlow / $y->getLow();
			$dlastPerc = $dlast / $y->getLast();
			$dbidPerc = $dbid / $y->getBid();
			$daskPerc = $dask / $y->getAsk();
			$dsma10Perc = ($sma10->getAvg() > 0) ? $dsma10 / $sma10->getAvg() : 0;
			$dsma25Perc = ($sma25->getAvg() > 0) ? $dsma25 / $sma25->getAvg() : 0;
			$dvolPerc = ($y->getVolume() > 0) ? $dvol / $y->getVolume() : 0;

			$json['deltas']['markets'][$mkt->mname] = array(
				"name" => $mkt->mname,
				"currency" => $mkt->currency,
				"high" => array(
					"spread" => $dhigh,
					"perc" => $dhighPerc*100
				),
				"low" => array(
					"spread" => $dlow,
					"perc" => $dlowPerc*100
				),
				"last" => array(
					"spread" => $dlast,
					"perc" => $dlastPerc*100
				),
				"bid" => array(
					"spread" => $dbid,
					"perc" => $dbidPerc*100
				),
				"ask" => array(
					"spread" => $dask,
					"perc" => $daskPerc*100
				),
				"sma10" => array(
					"spread" => $dsma10,
					"perc" => $dsma10Perc*100
				),
				"sma25" => array(
					"spread" => $dsma25,
					"perc" => $dsma25Perc*100
				),
				"volume" => array(
					"spread" => $dvol,
					"perc" => $dvolPerc*100
				),
			);
		}

		$matrix = $this->mob->getFullExchangeMatrix();
		foreach($matrix as $askname => $askM) {
			$aname = str_replace("History", "", $askname);
			$json['mob'][$aname] = array();
			foreach($askM as $bidname => $bidM) {
				$bname = str_replace("History", "", $bidname);
				$json['mob'][$aname][$bname] = $bidM['profit'];
			}
		}

		$this->setTimestamp(strtotime("midnight today -1 second"), $this->period);
		foreach($this->markets as $mkt) {
			$mkt->updateOrderBookData();
		}
		$this->_loadMOB();

		$yMatrix = $this->mob->getFullExchangeMatrix();
		foreach($yMatrix as $askname => $askM) {
			$aname = str_replace("History", "", $askname);
			$json['yesterday']['mob'][$aname] = array();
			foreach($askM as $bidname => $bidM) {
				$bname = str_replace("History", "", $bidname);
				$json['yesterday']['mob'][$aname][$bname] = $bidM['profit'];
			}
		}

		foreach($matrix as $askname => $askM) {
			$aname = str_replace("History", "", $askname);
			$json['deltas']['mob'][$aname] = array();
			foreach($askM as $bidname => $bidM) {
				$bname = str_replace("History", "", $bidname);

				$dSpread = $json['mob'][$aname][$bname] - $json['yesterday']['mob'][$aname][$bname];
				$dPerc = $dSpread / abs($json['yesterday']['mob'][$aname][$bname]);

				$json['deltas']['mob'][$aname][$bname] = array(
					"spread" => $dSpread,
					"perc" => $dPerc*100
				);
			}
		}

		$json['timestamp'] = time();

		return json_encode($json);
	}

	function printCurrency($amount, $abbr)
	{
		return $this->currencies->printCurrency($amount, $abbr);
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

				case "json":
					$this->updateMarketDepths();
					echo $this->updateMasterJSON();
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