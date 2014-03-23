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

				if (!isset($args['noarbitrers'])) {
					$arb = $this->createArbitrer($client, $args);
					$this->arbitrers[$client->getID()] = $arb;
				}
			}
		}
	}

	public function getMarket($mname)
	{
		return ($this->markets) ? $this->markets[$mname] : NULL;
	}

	public function getClient($cid)
	{
		return ($this->clients) ? $this->clients->getClient($cid) : NULL;
	}

	public function getClientPortfolio($cid)
	{
		if($c = $this->getClient($cid)){
			return $c->getPortfolio();
		}
		return NULL;
	}

	public function getClientPrivateMarket($cid, $mname)
	{
		if($c = $this->getClient($cid)){
			return $c->getPrivateMarket($mname);
		}
		return NULL;
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
			$res = $DB->query("SELECT * FROM markets WHERE active=1 ORDER BY id ASC");
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
						$this->markets[$market->mname] = $market;
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
				"high" => (float) ($t ? $t->getHigh() : -1),
				"low" => (float) ($t ? $t->getLow() : -1),
				"last" => (float) ($t ? $t->getLast() : -1),
				"bid" => (float) ($t ? $t->getBid() : -1),
				"ask" => (float) ($t ? $t->getAsk() : -1),
				"volume" => (float) ($t ? $t->getVolume() : -1),
				"commission" => (float) $mkt->commission,
				"sma10" => (float) (($sma10) ? $sma10->getAvg() : -1),
				"sma25" => (float) (($sma25) ? $sma25->getAvg() : -1)
			);

			$json['yesterday']['markets'][$mkt->mname] = array(
				"name" => $mkt->mname,
				"currency" => $mkt->currency,
				"high" => (float) ($y ? $y->getHigh() : -1),
				"low" => (float) ($y ? $y->getLow() : -1),
				"last" => (float) ($y ? $y->getLast() : -1),
				"bid" => (float) ($y ? $y->getBid() : -1),
				"ask" => (float) ($y ? $y->getAsk() : -1),
				"volume" => (float) ($y ? $y->getVolume() : -1)
			);

			$dhigh = (float) (($t && $y) ? ($t->getHigh() - $y->getHigh()) : -1);
			$dlow = (float) (($t && $y) ? ($t->getLow() - $y->getLow()) : -1);
			$dlast = (float) (($t && $y) ? ($t->getLast() - $y->getLast()) : -1);
			$dbid = (float) (($t && $y) ? ($t->getBid() - $y->getBid()) : -1);
			$dask = (float) (($t && $y) ? ($t->getAsk() - $y->getAsk()) : -1);
			$dsma10 = (float) (($t && $sma10) ? ($t->getLast() - $sma10->getAvg()) : -1);
			$dsma25 = (float) (($t && $sma25) ? ($t->getLast() - $sma25->getAvg()) : -1);
			$dvol = (float) (($t && $y) ? ($t->getVolume() - $y->getVolume()) : -1);

			$dhighPerc = (float) (($y && $y->getHigh() > 0) ? $dhigh / $y->getHigh() : 0);
			$dlowPerc = (float) (($y && $y->getLow() > 0) ? $dlow / $y->getLow() : 0);
			$dlastPerc = (float) (($y && $y->getLast() > 0) ? $dlast / $y->getLast() : 0);
			$dbidPerc = (float) (($y && $y->getBid() > 0) ? $dbid / $y->getBid() : 0);
			$daskPerc = (float) (($y && $y->getAsk() > 0) ? $dask / $y->getAsk() : 0);
			$dsma10Perc = (float) (($sma10 && $sma10->getAvg() > 0) ? $dsma10 / $sma10->getAvg() : 0);
			$dsma25Perc = (float) (($sma25 && $sma25->getAvg() > 0) ? $dsma25 / $sma25->getAvg() : 0);
			$dvolPerc = (float) (($y && $y->getVolume() > 0) ? $dvol / $y->getVolume() : 0);

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

		/*
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
		*/

		$json['timestamp'] = time();

		return json_encode($json);
	}

	public function updateChartJSON($starttime, $endtime, $period=PERIOD_1H, $value="avg", $nomtgox=false, $writeFile=false)
	{
		$json = $this->getChartJSON($starttime, $endtime, $period, $value, $nomtgox);
		if ($writeFile){
			file_put_contents("json/chart.json", $json);
		}
		return $json;
	}

	private function getChartJSON($starttime, $endtime, $period=PERIOD_1H, $value="avg", $nomtgox=false)
	{
		$json = array();
		$func = "get".ucfirst($value);
		foreach($this->markets as $mkt){
			if ($mkt->mname != 'MtGox' || !$nomtgox) { 
				$mktrow = array("key" => $mkt->mname, "color" => $mkt->getColor(), "values" => array());
				$tickers = $mkt->getHistorySamples($starttime, $endtime, $period);
				$tickers = array_reverse($tickers);
				//var_dump($tickers);
				foreach($tickers as $t){
					$tval = $t->$func();
					if ($tval > 0) {
						array_push($mktrow["values"], array((int) $t->getTimestamp(), (float) $tval));
					}
				}
				array_push($json, $mktrow);
			}
		}
		return json_encode($json);
	}

	function printCurrency($amount, $abbr, $precision=0)
	{
		return $this->currencies->printCurrency($amount, $abbr, $precision);
	}
	
	/**
	 * Executes a command
	 *
	 * @param	$cmds	{string}		arbitrage commands
	 */
	public function execCommand($cmds, $args="")
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

				case "chart-json":
					$this->updateMarketDepths();
					$starttime = isset($args['start']) ? $args['start'] : strtotime("-7 days");
					$endtime = isset($args['end']) ? $args['end'] : time();
					$period = (isset($args['period'])) ? $args['period'] : PERIOD_1H;
					$value = (isset($args['value'])) ? $args['value'] : 'avg';
					$nomtgox = (isset($args['nomtgox'])) ? ($args['nomtgox'] == 1) : false;
					echo $this->updateChartJSON($starttime, $endtime, $period, $value, $nomtgox);
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