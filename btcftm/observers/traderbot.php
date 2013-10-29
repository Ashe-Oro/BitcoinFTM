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
	}

	public function endOpportunityFinder()
	{
		if (array_len($this->potentialTrades)) {
			usort($this->potentialTrades, array("TraderBot", "sortPotentialTrades"));
			$this->executeTrade($this->potentialTrades[0]);
		}
	}

	static public function sortPotentialTrades($a, $b) {
		if ($a[0] == $b[0]) { return 0; }
		return ($a[0] > $b[0]) ? 1 : -1;
	}

	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		global $config;
		if ($profit < $config['profitThresh'] || $perc < $config['percThresh']) {
			error_log("[TraderBot] Profit or profit percentage lower than thresholds");
			return;
		}

		if (!in_array($kask, $this->clients)) {
			error_log("[TraderBot] Can't automate this trade, client not available: {$kask}");
			return;
		}

		if (!in_array($kbid, $this->clients)) {
			error_log("[TraderBot] Can't automate this trade, client not available: {$kbid}");
			return;
		}

		$volume = min($config['maxTxVolume'], $volume);

		$this->updateBalance();

		$maxVolume = $this->getMinTradeableVolume($buyPrice, $this->clients[$kask]->usdBalance, $this->clients[$kbid]->btcBalance);

		if ($volume < $config['minTxVolume']) {
			error_log("[TraderBot] Can't automate this trade, minimum volume transaction not reached {$volume} {$config['minTxVolume']}");
			error_log("Balance on {$kask}: {$this->clients[$kask]->usdBalance} - Balance on {$kbid}: {$this->clients[$kbid]->btcBalance}");
			return;
		}

		$currentTime = time();
		if ($currentTime - $this->lastTrade < $this->tradeWait) {
			$dT = $currentTime - $this->lastTrade;
			error_log("[TraderBot] Can't automate this trade, last trade occurred {$dT} seconds ago");
			return;
                        }

		$pTrade = array("profit" => $profit,
						"volume" => $volume,
						"kask" => $kask,
						"kbid" => $kbid,
						"wBuyPrice" => $wBuyPrice,
						"wSellPrice" => $wSellPrice,
						"buyPrice" => $buyPrice,
						"sellPrice" => $sellPrice );
		array_push($this->potentialTrades, $pTrade);
	}

	public function getMinTradeableVolume($buyPrice, $usdBal, $btcBal)
	{
		global $config;
		$min1 = $usdBal / ((1 + $config['balanceMargin']) * $buyPrice);
		$min2 = $btcBal / (1 + $config['balanceMargin']);
		return min($min1, $min2);
	}

	public function updateBalance()
	{
		foreach($this->clients as $kclient) {
			$this->clients[$kclient]->getInfo();
		}
	}

	public function watchBalances()
	{
		return true;
	}

	public function executeTrade($volume, $kask, $kbid, $weightedBuyPrice, $weightedSellPrice, $buyPrice, $sellPrice)
	{
		$this->lastTrade = time();
		error_log("[TraderBot] ]Buy {$kask} {$volume} BTC and sell @{$kbid}");
		$this->clients[$kask]->buy($volume, $buyPrice);
		$this->clients[$kbid]->sell($volume, $sellPrice);
	}
}
?>