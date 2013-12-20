<?php
require_once("market.php");

class DepthCalculator 
{
	private $asks = NULL;
	private $bids = NULL;
	
	public function __construct($asks, $bids)
	{
		$this->asks = $ask;
		$this->bids = $bid;
	}
	
	public function getDepthOpportunity()
	{
		$maxes = $this->getMaxDepth();
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
				$pArray = $this->getProfitFor($i, $j);
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
	
	public function getMaxDepth()
	{
		$i = 0;
		if (count($this->bids) != 0 && count($this->asks) != 0) {
			while(isset($this->asks[$i]["price"]) && $this->asks[$i]["price"] < $this->bids[0]["price"]) {
				if ($i >= count($this->asks) -1) {
					break;
				}
				$i++;
			}
		}

		$j = 0;
		if (count($kbid["bids"]) != 0 && count($kask["asks"]) != 0) {
			while(isset($this->bids[$j]["price"]) && $this->bids[$j]["price"] > $this->asks[0]["price"]) {
				if ($j >= count($this->bids) -1) {
					break;
				}
				$j++;
			}
		}

		iLog("[Arbitrer] Ask Depth: {$i} Bid Depth {$j}");
		return array("askDepth" => $i, "bidDepth" => $j);
	}
	
	public function getProfitFor($mi, $mj)
	{
		global $config;
		
		if (count($this->asks) && count($this->bids)) {
			if ($this->asks[$mi]["price"] >= $this->bids[$mj]["price"]) {
				return array(0, 0, 0, 0);
			}
			
			$maxAmountBuy = 0;
			for ($i = 0; $i <= $mi; $i++) {
				$maxAmountBuy += $this->asks[$i]["amount"];
			}

			$maxAmountSell = 0;
			for ($j = 0; $j <= $mj; $j++) {
				$maxAmountSell += $this->bids[$j]["amount"];
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

}