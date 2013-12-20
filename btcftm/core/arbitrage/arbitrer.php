<?php
//require_once("./core/public_markets/depthCalculator.php");
require_once("./core/public_markets/mob/mob.php");

class Arbitrer
{
	public $client = NULL;

	public $observers = array();
	public $observer_names = "";
	
	public $bots = array();
	public $bots_data = NULL;
	
	public $markets = NULL;
	public $mob = NULL;
	
	public $threadpool = NULL;
	
	protected $timestamp = 0;

	public function __construct($client, $args)
	{
		$this->client = $client;
		$this->timestamp = time();
	}

	public function initObservers($observers)
	{
		iLog("[Arbitrer] Initializing observers...");
		if ($observers){
			$this->observer_names = $observers;
			$oLoaded = 0;
			foreach($observers as $observer_name) {
				$oFile = "./core/observers/".strtolower($observer_name).".php";
				if (file_exists($oFile)){
					require_once($oFile);
					try {
						$observer = new $observer_name($this->client);
						array_push($this->observers, $observer);
						$oLoaded++;
						iLog("[Arbitrer] {$observer_name} Observer created for ".$this->client->getUsername());
					} catch (Exception $e) {
						iLog("[Arbitrer] ERROR: Observer construct function invalid - {$observer_name} - ".$e->getMessage());
					}
				} else {
					iLog("[Arbitrer] ERROR: Observer file not found - {$oFile}");
				}
			}
			iLog("[Arbitrer] {$oLoaded}/".count($observers)." observers initialized.");
		}
	}
	
	public function getObserver($observer_name)
	{
		foreach($this->observers as $obs) {
			if ($obs->name == $observers) {
				return $obs;
			}
		}
		 return NULL;
	}
	
	
	public function initTraderBots($bots)
	{
		iLog("[Arbitrer] Initializing Trader Bots...");
		if ($bots){
			$this->bots_data = $bots;
			$bLoaded = 0;
			foreach($bots as $bdata) {
				$bFile = "./core/observers/".strtolower($bdata['type'])."bot.php";
				if (file_exists($bFile)){
					require_once($bFile);
					$bName = ucfirst($bdata['type'])."Bot";
					try {
						$newBot = new $bName($this->client, json_decode($bdata['settings']));
						array_push($this->bots, $newBot);
						iLog("[Arbitrer] {$bName} Trader Bot created for ".$this->client->getUsername());
						$bLoaded++;
					} catch (Exception $e) {
						iLog("[Arbitrer] ERROR: Trader Bot construct function invalid - {$bName} - ".$e->getMessage());
					}
				} else {
					iLog("[Arbitrer] ERROR: Trader Bot file not found - {$bFile}");
				}
			}
			iLog("[Arbitrer] {$bLoaded}/".count($bots)." Trader Bots initialized.");
		}
	}
	
	public function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}
	
	public function getTraderBots()
	{
		return $this->bots;
	}
	
	public function watchMarkets($markets, $mob)
	{
		global $config;
		$this->markets = $markets;
		$this->mob = $mob;
		
		if ($config['echoLog']) { echo "<hr />\n"; }
		iLog("[Arbitrer] PHASE 2: ???");
		iLog("[Arbitrer] Executing main loop at timestamp = {$this->timestamp} for ".$this->client->getUsername());
			
		$this->tick();
			
		iLog("[Arbitrer] Main loop complete for ".$this->client->getUsername());
	}
	
	public function tick()
	{
		global $config;
		
		$this->_beginOpportunityTick();
		
		if ($config['echoLog']) { echo "<hr />\n"; }
		iLog("[Arbitrer] PHASE 3: PROFIT!");
		$this->_opportunityTick();
		
		/***** 
		
		CUT ALL THIS SHIT OUT AND MOVE IT INTO TRADER BOT LOGIC NOW BITCHES !!!! 
		
		$dClone = $this->depths;
		//var_dump($this->depths);
		foreach($this->depths as $km1 => $kmarket1) {
			foreach($dClone as $km2 => $kmarket2) {
				if($km1 == $km2) { continue; }
				$kmarket1['name'] = $km1;
				$kmarket2['name'] = $km2;
				
				$asks = $kmarket1['asks'];
				$bids = $kmarket2['bids'];
				
				if (isset($asks) && isset($bids) && count($asks) > 0 && count($bids) > 0){
					$topAsk = $asks[0];
					$topBid = $bids[0];
					if ($topAsk['price'] < $topBid['price']) {
						$this->arbitrageOpportunity($kmarket1, $topAsk, $kmarket2, $topBid);
					}
				}
			}
		}
		******/
		
		$this->_endOpportunityTick();
	}
	
	private function _beginOpportunityTick()
	{
		foreach($this->observers as $observer) {
			$observer->beginOpportunityFinder($this->markets, $this->mob);
		}
		foreach($this->bots as $bot) {
			$bot->beginOpportunityFinder($this->markets, $this->mob);
		}
	}
	
	private function _opportunityTick()
	{
		foreach($this->observers as $observer) {
			$observer->opportunityFinder($this->markets, $this->mob);
		}
		foreach($this->bots as $bot) {
			$bot->opportunityFinder($this->markets, $this->mob);
		}
	}
	
	private function _endOpportunityTick()
	{
		foreach($this->observers as $observer) {
			$observer->endOpportunityFinder($this->markets, $this->mob);
		}
		foreach($this->bots as $bot) {
			$bot->endOpportunityFinder($this->markets, $this->mob);
		}
	}

	/***** NOPE, CUT THIS SHIT OUT TOO! THIS IS TRADER BOT OR MOB LOGIC ONLY BITCHES!!!!

	public function arbitrageOpportunity($kask, $ask, $kbid, $bid)
	{
		iLog("[Arbitrer] Arbitraging opportunity - Buy {$kask['name']} @{$ask['price']} Sell {$kbid['name']} @{$bid['price']}");
		$perc = (($bid['price'] - $ask['price']) / $bid['price']) * 100;
		
		$dCalc = new DepthCalculator($kask, $kbid);
		
		$aArray = $dCalc->getDepthOpportunity();
		
		if ($aArray['volume'] == 0 || $aArray['buyPrice'] == 0) {
			iLog("[Arbitrer] No opportunities found - Volume: {$aArray['volume']} - Buy Price: {$aArray['buyPrice']}");
			return;
		}
		
		$perc2 = (1 - ($aArray['volume'] - ($aArray['profit'] / $aArray['buyPrice'])) / $aArray['volume']) * 100;
		foreach($this->observers as $observer) {
			$observer->opportunity($aArray['profit'], $aArray['volume'], $aArray['buyPrice'], $kask, $aArray['sellPrice'], $kbid, $perc2, $aArray['wBuyPrice'], $aArray['wSellPrice']);
		}
		foreach($this->bots as $bot) {
			$bot->opportunity($aArray['profit'], $aArray['volume'], $aArray['buyPrice'], $kask, $aArray['sellPrice'], $kbid, $perc2, $aArray['wBuyPrice'], $aArray['wSellPrice']);
		}
	}
	
	*******/

}
?>