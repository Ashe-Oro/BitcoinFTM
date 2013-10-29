<?php
require_once("config.php");

class Arbitrer
{
	public $markets = array();
	public $market_names = "";

	public $observers = array();
	public $observer_names = "";
	
	public $depths = array();
	public $threadpool = NULL;

	public function __construct()
	{
		global $config;
		$this->initMarkets($config->markets);
		$this->initObservers($config->observers);
	}
	
	public function initMarkets($markets)
	{
		$this->market_names = $markets;
		foreach($markets as $market_name){
			/** UPDATE ONCE PUBLIC MARKETS ARE COMPLETE **
			exec('import public_markets.' + market_name.lower())
            market = eval('public_markets.' + market_name.lower() + '.' +
                          market_name + '()')
			

			array_push($this->markets->append(market));
			***/
            		
		}
	}

	public function initObservers($observers)
	{
		$this->observer_names = $observers;
		foreach($observers as $observer_name) {

			/*** UPDATE ONCE PUBLIC MARKETS ARE COMPLETE **
			 exec('import observers.' + observer_name.lower())
            		observer = eval('observers.' + observer_name.lower() + '.' +
                            		observer_name + '()')
           			
			array_push($this->observers, observer);
			***/
		}
	}

	public function getProfitFor($mi, $mj, $kask, $kbid
	{
		global $config;
		
		if (array_len($this->depths)) {
			if ($this->depths[$kask]["asks"][$mi]["price"] >= $this->depths[$kbid]["bids"][$mj]["price"]) {
				return array(0, 0, 0, 0);
			}

			$maxAmountBuy = 0;
			for ($i = 0; $i < $mi+1; $i++) {
				$maxAmountBuy += $this->depths[$kask]["asks"][$i]["amount"];
			}

			$maxAmountSell = 0;
			for ($j = 0; $j < $mj+1; $j++) {
				$maxAmountBuy += $this->depths[$kbid]["bids"][$j]["amount"];
			}

			$maxAmount = min($maxAmountBuy, $maxAmountSell, $config->maxTxVolume);

			$buyTotal = 0;
			$wBuyPrice = 0;
			for($i = 0; $i < $mi; $i++){
				$price = $this->depths[$kask]["asks"][$i]["price"];
				$amount = min($maxAmount, $buyTotal + $this->depths[$kask]["asks"][$i]["amount"]) - $buyTotal;

				if ($amount <= 0) { break; }
				
				$buyTotal += $amount;
				if ($wBuyPrice == 0){
					$wBuyPrice = $price;
				}  else {
					$wBuyPrice = (($wBuyPrice *($buyTotal - $amount)) + ($price * $amount))) / $buyTotal; 
				}
			}

			$sellTotal = 0;
			$wSellPrice = 0;
			for($j = 0; $j < $mj; $j++){
				$price = $this->depths[$kbid]["bids"][$j]["price"];
				$amount = min($maxAmount, $sellTotal + $this->depths[$kbid]["bids"][$j]["amount"]) - $sellTotal;

				if ($amount <= 0) { break; }
				
				$sellTotal += $amount;
				if ($wSellPrice == 0){
					$wSellPrice = $price;
				}  else {
					$wSellPrice = (($wSellPrice *($sellTotal - $amount)) + ($price * $amount))) / $sellTotal; 
				}
			}

			$profit = ($sellTotal * $wSellPrice) - ($buyTotal * $wBuyPrice);
			return array("profit" => $profit, "sellTotal" => $sellTotal, "wBuyPrice" => $wBuyPrice, "wSellPrice" => $wSellPrice);
		}

	}

	public function getMaxDepth($kask, $kbid)
	{
		$i = 0;
		if (array_len($this->depths[$kbid]["bids"]) != 0 && array_len($this->depths[$kask]["asks"]) != 0) {
			while(isset($this->depths[$kask]["asks"][$i]["price"]) && $this->depths[$kask]["asks"][$i]["price"] < $this->depths[$kbid]["bids"][0]["price"]) {
				if ($i >= array_len($this->depths[$kask]["asks"]) -1) {
					break;
				}
				$i++;
			}
		}

		$j = 0;
		if (array_len($this->depths[$kbid]["bids"]) != 0 && array_len($this->depths[$kask]["asks"]) != 0) {
			while(isset($this->depths[$kbid]["bids"][$j]["price"]) && $this->depths[$kbid]["bids"][$j]["price"] > $this->depths[$kask]["asks"][0]["price"]) {
				if ($j >= array_len($this->depths[$kbid]["bids"]) -1) {
					break;
				}
				$j++;
			}
		}

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
			for ($j = 0; $j < max_j; $j++) {
				$pArray = $this->getProfitFor($i, $j, $kask, $kbid);
				if ($pArray['profit'] >= 0 && $pArray['profit'] >= $bestProfit) {
					$bestProfit = $profit;
					$bestVolume = $volume;
					$best_i = $i;
					$best_j = $j;
					$best_wBuyPrice = $pArray['wBuyPrice'];
					$best_wSellPrice = $pArray['wSellPrice'];
				}
			}
		}

		$retArray = array('profit' => $bestProfit, 'volume' => $bestVolume, 'buyPrice' => $this->depths[$kask]["asks"][$best_i]["price"], 'sellPrice' => $this->depths[$kbid]["bids"][$best_j]["price"], 'wBuyPrice' => $best_wBuyPrice, 'wSellPrice' => $best_wSellPrice); 

	}

	public function arbitrageOpportunity($kask, $ask, $kbid, $bid)
	{
		$perc = (($bid['price'] - $ask['price']) / $bid['price']) * 100;
		$aArray = $this-> arbitrageDepthOpportunity($kask, $kbid);
		if ($aArray['volume'] == 0 || $aArray['buyPrice'] == 0) {
			return;
		}
		$perc2 = (1 - ($aArray['volume'] - ($aArray['profit'] / $aArray['buyPrice'])) / $aArray['volume']) * 100;
		foreach($this->observers as $observer) {
			$observer->opportunity($pArray['profit'], $pArray['volume'], $pArray['buyPrice'], $kask, $pArray['sellPrice'], $kbid, $perc2, $pArray['wBuyPrice'], $pArray['wSellPrice']);
		}
	}

	private function _getMarketDepth($market)
	{
		$this->depths[$market->name] = $market->getDepth();
		return $this->depths[$market->name];
	}

	public function updateDepths()
	{
		$depths = array();
		$futures = array();
		foreach($this->markets as $market){
			array_push($futures, $this->thread pool->submit($this->_getMarketDepth($market), $market, $depths);
		}

		/*** ADD SLEEP INTERVAL HERE
		wait(futures, timeout=20)
        		*/
		return $depths;
	}

	public function tickers()
	{
		foreach($this->markets as $market) {
			// write ticker message
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
		foreach($this->observers as $observer) {
			$observer->beginOpportunityFinder($this->depths);
		}

		$dClone = $this->depths;
		foreach($this->depths as $kmarket1) {
			foreach($dClone as $kmarket2) {
				if($kmarket1 == $kmarket2) { continue; }
				$market1 = $this->depths[$kmarket1];
				$market2 = $this->depths[$kmarket2];
				if (isset($market1['asks']) && isset($market2['bids']) && array_len($market1['asks']) > 0 && array_len($market2['bids']) > 0){
					if ($market1['asks'][0]['price'] < $market2['bids'][0]['price']) {
						$this->arbitrageOpportunity($kmarket1, $market1['asks'][0], $kmarket2, $market2['bids'][0];
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
		
		while(true) {
			$this->depths = $this->updateDepths();
			$this->tickers();
			$this->tick();
			sleep($config->refreshRate);
		}
	}
}
?>