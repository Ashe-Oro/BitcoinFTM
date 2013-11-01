<?php
require_once("observer.php");
require_once("./private_markets/privatemtgoxusd.php");
require_once("./private_markets/privatebitstampusd.php");

class TraderBot extends Observer
{
	public $clients = array();
	public $fc = NULL;
	
	public $tradeWait = 120;
	public $lastTrade = 0;
	public $potentialTrades = array();

	public function __create()
	{
		$clients['mtgoxusd'] = new PrivateMtGoxUSD();
		$clients['bitstampusd'] = new PrivateBitstampUSD();
	}

	public function beginOpportunityFinder($depths)
	{
		$this->potentialTrades = array();
		iLog("[TraderBot] Potential trade list cleared");
	}

	public function endOpportunityFinder()
	{
		if (count($this->potentialTrades)) {
			iLog("[TraderBot] ".count($this->potentialTrades)." potential trades FOUND!");
			usort($this->potentialTrades, array("TraderBot", "sortPotentialTrades"));
			$this->executeTrade($this->potentialTrades[0]);
		} else {
			iLog("[TraderBot] endOpportunityFinder - NO potential trades found");
		}
	}

	static public function sortPotentialTrades($a, $b) {
		if ($a['profit'] == $b['profit']) { return 0; }
		return ($a['profit'] > $b['profit']) ? 1 : -1;
	}

	/*** VERY IMPORTANT: DOUBLE CHECK THIS LOGIC!!! ***/
	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		global $config;
		iLog("[TraderBot] Searching for trade opportunities...");
		
		$askName = $kask['name'];
		$bidName = $kbid['name'];
		
		if ($profit < $config['profitThresh'] || $perc < $config['percThresh']) {
			iLog("[TraderBot] WARNING: Profit or profit percentage lower than thresholds - Profit: {$profit} (t: {$config['profitThresh']}) Perc: {$perc} (t: {$config['percThresh']})");
			return;
		}

		if (!isset($this->clients[$askName])) {
			iLog("[TraderBot] WARNING: Can't automate this trade, client not available: {$askName}");
			return;
		}

		if (!isset($this->clients[$bidName])) {
			iLog("[TraderBot] WARNING: Can't automate this trade, client not available: {$bidName}");
			return;
		}

		$volume = min($config['maxTxVolume'], $volume);

		$this->updateBalance();
		$maxVolume = $this->getMinTradeableVolume($buyPrice, $this->clients[$askName]->usdBalance, $this->clients[$bidName]->btcBalance);

		if ($volume < $config['minTxVolume']) {
			iLog("[TraderBot] WARNING: Can't automate this trade, minimum volume transaction not reached - Vol: {$volume} Min: {$config['minTxVolume']}");
			iLog("Balance on {$askName}: {$this->clients[$askName]->usdBalance} - Balance on {$bidName}: {$this->clients[$bidName]->btcBalance}");
			return;
		}

		$currentTime = time();
		if ($currentTime - $this->lastTrade < $this->tradeWait) {
			$dT = $currentTime - $this->lastTrade;
			iLog("[TraderBot] WARNING: Can't automate this trade, last trade occurred {$dT}s ago - must wait {$this->tradeWait}s");
			return;
        }

		$pTrade = array("profit" => $profit,
						"volume" => $volume,
						"kask" => $kask,
						"kbid" => $kbid,
						"askName" => $askName,
						"bidName" => $bidName,
						"wBuyPrice" => $wBuyPrice,
						"wSellPrice" => $wSellPrice,
						"buyPrice" => $buyPrice,
						"sellPrice" => $sellPrice );
		
		iLog("[TraderBot] Adding potential trade - Profit: {$profit}USD for {$volume}BTC - Buy {$askName} @{$buyPrice} ({$wBuyPrice}) - Sell {$bidName} @{$sellPrice} ({$wSellPrice})");
		
		array_push($this->potentialTrades, $pTrade);
	}

	/*** VERY IMPORTANT FUNCTION ***/
	/** Set out leniency here - balanceMargin == slippage **/
	public function getMinTradeableVolume($buyPrice, $usdBal, $btcBal)
	{
		global $config;
		$min1 = $usdBal / ((1 + $config['balanceMargin']) * $buyPrice);
		$min2 = $btcBal / (1 + $config['balanceMargin']);
		return min($min1, $min2);
	}

	public function updateBalance()
	{
		iLog("[TraderBot] Updating market balances...");
		foreach($this->clients as $kclient) {
			$this->clients[$kclient]->getInfo();
		}
	}

	public function watchBalances()
	{
		return true;
	}

	/**** VERY IMPORTANT: modify to have accurate buy/sell info ***/
	public function executeTrade($trade)
	{
		if ($trade) {
			if (isset($this->clients[$trade['askName']]) && isset($this->clients[$trade['bidName']])) {
				$this->lastTrade = time();
		
				iLog("[TraderBot] Executing Trade of {$trade['volume']}BTC - Buy {$trade['askName']} @{$trade['buyPrice']} - Sell {$trade['bidName']}  @{$kbid}");
				
				$this->clients[$trade['askName']]->buy($trade['volume'], $trade['buyPrice']);
				$this->clients[$trade['bidName']]->sell($trade['volume'], $trade['sellPrice']);
				
				/***** NEED TO UPDATE OUR MYSQL RECORDS HERE FOR TRADE *****/
			}
		}
	}
}
?>