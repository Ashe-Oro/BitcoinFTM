<?php
require_once("config/config.php");

class Arbitrer
{
	public $client = NULL;
	
	public $markets = array();
	public $market_names = "";

	public $observers = array();
	public $observer_names = "";
	
	public $depths = array();
	public $threadpool = NULL;

	public function __construct($client, $args)
	{
		$this->client = $client;
		/*global $config;
		$this->initMarkets($config->markets);
		$this->initObservers($config->observers);*/
	}
	
	public function initMarkets($markets)
	{
		iLog("[Arbitrer] Initializing markets...");
		if ($markets) {
			$this->market_names = $markets;
			$mLoaded = 0;
			foreach($markets as $market_name){
				$mFile = "./public_markets/".strtolower($market_name).".php";
				if (file_exists($mFile)){
					require_once($mFile);
					try {
						$market = new $market_name();
						array_push($this->markets, $market);
						$mLoaded++;
					} catch (Exception $e) {
						iLog("[Arbitrer] ERROR: Market construct function invalid - {$market_name} - ".$e->getMessage());
					}
				} else {
					iLog("[Arbitrer] ERROR: Market file not found - {$mFile}");
				}		
			}
			iLog("[Arbitrer] {$mLoaded}/".count($markets)." markets initialized.");
		} else {
			iLog("[Arbitrer] ERROR: No markets loaded.");
		}
	}

	public function initObservers($observers)
	{
		iLog("[Arbitrer] Initializing observers...");
		if ($observers){
			$this->observer_names = $observers;
			$oLoaded = 0;
			foreach($observers as $observer_name) {
				$oFile = "./observers/".strtolower($observer_name).".php";
				if (file_exists($oFile)){
					require_once($oFile);
					try {
						$observer = new $observer_name($this->client);
						array_push($this->observers, $observer);
						$oLoaded++;
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
	
	public function getMarket($market_name)
	{
		foreach($this->markets as $mkt) {
			if ($mkt->name == $market_name) {
				return $mkt;
			}
		}
		 return NULL;
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

	public function getProfitFor($mi, $mj, $kask, $kbid)
	{
		global $config;
		
		if (count($kask) && count($kbid)) {
			if ($kask["asks"][$mi]["price"] >= $kbid["bids"][$mj]["price"]) {
				return array(0, 0, 0, 0);
			}
			
			$maxAmountBuy = 0;
			for ($i = 0; $i <= $mi; $i++) {
				$maxAmountBuy += $kask["asks"][$i]["amount"];
			}

			$maxAmountSell = 0;
			for ($j = 0; $j <= $mj; $j++) {
				$maxAmountSell += $kbid["bids"][$j]["amount"];
			}

			$maxAmount = min(min($maxAmountBuy, $maxAmountSell), $this->client->getMaxTxVolume());

			$buyTotal = 0;
			$wBuyPrice = 0;
			for($i = 0; $i <= $mi; $i++){
				$price = $kask["asks"][$i]["price"];
				$amount = min($maxAmount, $buyTotal + $kask["asks"][$i]["amount"]) - $buyTotal;

				if ($amount <= 0) { break; }
				
				$buyTotal += $amount;
				
				if ($wBuyPrice == 0){
					$wBuyPrice = $price;
				}  else {
					/*** STANDARD MOVING AVG ***/
					$wBuyPrice = (($wBuyPrice *($buyTotal - $amount)) + ($price * $amount)) / $buyTotal; 
				}
			}

			$sellTotal = 0;
			$wSellPrice = 0;
			for($j = 0; $j <= $mj; $j++){
				$price = $kbid["bids"][$j]["price"];
				$amount = min($maxAmount, $sellTotal + $kbid["bids"][$j]["amount"]) - $sellTotal;

				if ($amount <= 0) { break; }
				
				$sellTotal += $amount;
				if ($wSellPrice == 0){
					$wSellPrice = $price;
				}  else {
					/*** STANDARD MOVING AVG ***/
					$wSellPrice = (($wSellPrice *($sellTotal - $amount)) + ($price * $amount)) / $sellTotal; 
				}
			}

			$profit = ($sellTotal * $wSellPrice) - ($buyTotal * $wBuyPrice);
			//iLog("[Arbitrer] Get Profit - profit: {$profit} sellTotal: {$sellTotal} wBuyPrice: {$wBuyPrice} wSellPrice: {$wSellPrice}");
			return array("profit" => $profit, "sellTotal" => $sellTotal, "wBuyPrice" => $wBuyPrice, "wSellPrice" => $wSellPrice);
		}
		return array(0, 0, 0, 0);
	}

	public function getMaxDepth($kask, $kbid)
	{
		$i = 0;
		if (count($kbid["bids"]) != 0 && count($kask["asks"]) != 0) {
			while(isset($kask["asks"][$i]["price"]) && $kask["asks"][$i]["price"] < $kbid["bids"][0]["price"]) {
				if ($i >= count($kask["asks"]) -1) {
					break;
				}
				$i++;
			}
		}

		$j = 0;
		if (count($kbid["bids"]) != 0 && count($kask["asks"]) != 0) {
			while(isset($kbid["bids"][$j]["price"]) && $kbid["bids"][$j]["price"] > $kask["asks"][0]["price"]) {
				if ($j >= count($kbid["bids"]) -1) {
					break;
				}
				$j++;
			}
		}

		iLog("[Arbitrer] Ask Depth: {$i} Bid Depth {$j}");
		return array("askDepth" => $i, "bidDepth" => $j);
	}

	public function arbitrageDepthOpportunity($kask, $kbid)
	{
		$maxes = $this->getMaxDepth($kask, $kbid);
		$max_i = $maxes['askDepth'];
		$max_j = $maxes['bidDepth'];

		$bestProfit = 0;
		$best_i = 0;
		$best_j = 0;
		$best_wBuyPrice = 0;
		$best_wSellPrice = 0;
		$bestVolume = 0;
		
		for ($i = 0; $i <= $max_i; $i++) {
			for ($j = 0; $j <= $max_j; $j++) {
				$pArray = $this->getProfitFor($i, $j, $kask, $kbid);
				if ($pArray['profit'] > 0 && $pArray['profit'] > $bestProfit) {
					$bestProfit = $pArray['profit'];
					$bestVolume = $pArray['sellTotal'];
					$best_i = $i;
					$best_j = $j;
					$best_wBuyPrice = $pArray['wBuyPrice'];
					$best_wSellPrice = $pArray['wSellPrice'];
				}
			}
		}
		
		

		$retArray = array(	'profit' => $bestProfit, 
						  	'volume' => $bestVolume, 
							'buyXchg' => $kask['name'],
							'buyPrice' => $kask['asks'][$best_i]["price"], 
							'sellXchg' => $kbid['name'],
							'sellPrice' => $kbid["bids"][$best_j]["price"], 
							'wBuyPrice' => $best_wBuyPrice, 
							'wSellPrice' => $best_wSellPrice); 
		
		iLog("[Arbitrer] Best profit opportunity: {$bestProfit}USD {$bestVolume}BTC - Buy {$retArray['buyXchg']} @{$retArray['buyPrice']} (wBuy @{$best_wBuyPrice}) - Sell {$retArray['sellXchg']} @{$retArray['sellPrice']} (wSell @{$best_wSellPrice})");
		
		return $retArray;

	}

	public function arbitrageOpportunity($kask, $ask, $kbid, $bid)
	{
		iLog("[Arbitrer] Arbitraging opportunity - Buy {$kask['name']} @{$ask['price']} Sell {$kbid['name']} @{$bid['price']}");
		$perc = (($bid['price'] - $ask['price']) / $bid['price']) * 100;
		
		$aArray = $this->arbitrageDepthOpportunity($kask, $kbid);
		if ($aArray['volume'] == 0 || $aArray['buyPrice'] == 0) {
			iLog("[Arbitrer] No opportunities found - Volume: {$aArray['volume']} - Buy Price: {$aArray['buyPrice']}");
			return;
		}
		
		$perc2 = (1 - ($aArray['volume'] - ($aArray['profit'] / $aArray['buyPrice'])) / $aArray['volume']) * 100;
		foreach($this->observers as $observer) {
			$observer->opportunity($aArray['profit'], $aArray['volume'], $aArray['buyPrice'], $kask, $aArray['sellPrice'], $kbid, $perc2, $aArray['wBuyPrice'], $aArray['wSellPrice']);
		}
	}

	private function _getMarketDepth($market)
	{
		$this->depths[$market->name] = $market->getDepth();
		return $this->depths[$market->name];
	}

	public function updateDepths()
	{
		iLog("[Arbitrer] Updating market depths...");
		
		$depths = array();
		foreach($this->markets as $market){
			$depths[$market->name] = $this->_getMarketDepth($market);
		}

		iLog("[Arbitrer] Market depths updated");
				
		return $depths;
	}

	public function tickers()
	{
		foreach($this->markets as $market) {
			$ticker = $market->getTicker();
			iLog("[Arbitrer] Ticker {$market->name} - Ask: {$ticker['ask']['price']} Bid: {$ticker['bid']['price']}");
		}
	}

	public function replayHistory($directory)
	{
		/**** HANDLE HISTORY LOADING FROM FILE
		import os
        import json
        import pprint
        files = os.listdir(directory)
        files.sort()
        for f in files:
            depths = json.load(open(directory + '/' + f, 'r'))
            self.depths = {}
            for market in self.market_names:
                if market in depths:
                    self.depths[market] = depths[market]
            self.tick()
		***/
	}

	public function tick()
	{
		global $config;
		
		iLog("[Arbitrer] Tick @timestamp = ".time());
		foreach($this->observers as $observer) {
			$observer->beginOpportunityFinder($this->depths);
		}

		if ($config['echoLog']) { echo "<hr />\n"; }
		iLog("[Arbitrer] PHASE 3: PROFIT!");
		$dClone = $this->depths;
		//var_dump($this->depths);
		foreach($this->depths as $km1 => $kmarket1) {
			foreach($dClone as $km2 => $kmarket2) {
				if($km1 == $km2) { continue; }
				$kmarket1['name'] = $km1;
				$kmarket2['name'] = $km2;
				if (isset($kmarket1['asks']) && isset($kmarket2['bids']) && count($kmarket1['asks']) > 0 && count($kmarket2['bids']) > 0){
					if ($kmarket1['asks'][0]['price'] < $kmarket2['bids'][0]['price']) {
						$this->arbitrageOpportunity($kmarket1, $kmarket1['asks'][0], $kmarket2, $kmarket2['bids'][0]);
					}
				}
			}
		}

		foreach($this->observers as $observer) {
			$observer->endOpportunityFinder();
		}
	}

	public function loop()
	{
		global $config;
		
		//while(true) {
			if ($config['echoLog']) { echo "<hr />\n"; }
			iLog("[Arbitrer] PHASE 2: ???");
			iLog("[Arbitrer] Executing main loop at timestamp = ".time());
			$this->depths = $this->updateDepths();
			$this->tickers();
			$this->tick();
			
			iLog("[Arbitrer] Main loop complete for ".$this->client->getUsername());
			
			/*** DISABLE THIS - USE CRON JOB INSTEAD !!! ***/
			//sleep($config['refreshRate']);
		//}
	}
}
?>