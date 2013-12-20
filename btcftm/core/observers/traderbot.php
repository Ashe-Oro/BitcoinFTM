<?php
require_once("observer.php");

class TraderBot extends Observer
{
	public $fc = NULL;
	
	public $tradeWait = 120;
	public $lastTrade = 0;
	public $settings;
	
	public $potentialTrades = array();
	public $currentTrade = NULL;

	public function __construct($client, $settings=NULL)
	{
		parent::__construct($client);
		$this->_loadSettings($settings);
		$this->currentTrade = $this->client->getPortfolio()->getCurrentTrade();
	}
	
	protected function _loadSettings($settings)
	{
		$default = array("minTxVolume" => 0.01, "maxTxVolume" => 1, "balanceMargin" => 0.005, "profitThresh" => 5, "percThresh" => 2);
		if ($settings) {
			array_merge($default, $settings);
		}
		$this->settings = $default;
	}

	public function beginOpportunityFinder($markets, $mob)
	{
		$this->potentialTrades = array();
		iLog("[TraderBot] beginOpportunityFinder - Potential trade list cleared");
	}
	
	public function opportunityFinder($markets, $mob)
	{
		iLog("[TraderBot] opportunityFinder...");
		$matrix = $mob->getExchangeMatrix();
		if ($config['live']) {
			$this->updatePrivateMarketBalance(); // only do this on live trading
		}
		
		$ops = array();
		foreach($matrix as $mname => $mx){
			if ($mx['profit'] > 0){		// for now, only consider positive profit
				if ($this->privateMarketHasCapital($mname)){
					$askMarket = $this->getPrivateMarket($mname);
					$bidMarket = $this->getPrivateMarket($mx['market']);
					
					$usd = $askMarket->getBalance('USD');
					$btc = $bidMarket->getBalance('BTC');
					
					$trade = $this->getTradeOpportunity($mname, $mx, $usd, $btc);
					$ops[$mname] = array("market" => $mx['market'], "trade" => $trade);
					iLog("[TraderBot] Trade opportunity for {$mname} -> {$mx['market']} -  profit: {$trade['profit']}USD");
				}
			}
		}
		
		// for existing opportunities, get depth opportunity for each one
		// sort by best depth opportunity
		
	}
	
	public function endOpportunityFinder($markest, $mob)
	{
		if (count($this->potentialTrades)) {
			iLog("[TraderBot] ".count($this->potentialTrades)." potential trades FOUND!");
			//usort($this->potentialTrades, array("TraderBot", "sortPotentialTrades"));
			$this->executeTrade($this->potentialTrades[0]);
		} else {
			iLog("[TraderBot] endOpportunityFinder - NO potential trades found");
		}
	}

	static public function sortPotentialTrades($a, $b) {
		if ($a['profit'] == $b['profit']) { return 0; }
		return ($a['profit'] > $b['profit']) ? 1 : -1;
	}
	
	public function getTradeOpportunity($mname, $mx, $usd, $btc)
	{
		$profit = $mx['profit'];
		$volume = 0;
		
		$ret = array('profit' => $profit, $volume => $volume);
		
		return $ret;
	}
	
	public function privateMarketHasCapital($mname)
	{
		$balance = $this->getPrivateMarketBalances($mname);
		return ($balance['usd'] != 0 || $balance['btc'] != 0);
	}
	
	public function getPrivateMarketBalances($mname)
	{
		iLog("[TraderBot] Getting market balances for {$mname}...");
		$pmarket = $this->getPrivateMarket($mname);
		$balance = array('usd' => 0, 'btc' => 0);
		if ($pmarket){
			$balance['usd'] = $pmarket->getBalance("USD");
			$balance['btc'] = $pmarket->getBalance("BTC");
			iLog("[TraderBot] Market balance for {$mname}: {$balance['usd']}USD  {$balance['btc']}BTC.");
		}
		return $balance;
	}


	/***** THIS FUNCTION IS DEPRECIATED BUT I NEED TO KEEP IT AROUND FOR FUTURE REFERENCE!! *****/

	/*** VERY IMPORTANT: DOUBLE CHECK THIS LOGIC!!! ***/
	public function opportunity($profit, $volume, $buyprice, $kask, $sellprice, $kbid, $perc, $wBuyPrice, $wSellPrice)
	{
		global $config;
		iLog("[TraderBot] Searching for trade opportunities...");
		
		$askName = $kask['name'];
		$bidName = $kbid['name'];
		
		$askMarket = $this->getPrivateMarket($askName);
		if ($askMarket == NULL) {
			iLog("[TraderBot] WARNING: Can't automate this trade, client not available: {$askName}");
			return;
		}

		$bidMarket = $this->getPrivateMarket($bidName);
		if ($bidMarket == NULL) {
			iLog("[TraderBot] WARNING: Can't automate this trade, client not available: {$bidName}");
			return;
		}

		$finalVolume = min($this->client->getMaxTxVolume(), $volume);

		if ($config['live']) {
			$this->updatePrivateMarketBalance(); // only do this on live trading
		}
		
		$askBalance = $askMarket->getBalance("USD");
		$bidBalance = $bidMarket->getBalance("BTC");
		$maxVolume = $this->getMinTradeableVolume($buyprice, $askBalance, $bidBalance);
		$finalVolume = min($finalVolume, min($maxVolume, $this->client->getMaxTxVolume()));

		$minTxVol = $this->client->getMinTxVolume();
		if ($finalVolume < $minTxVol) {
			iLog("[TraderBot] WARNING: Can't automate this trade, minimum volume transaction not reached - Vol: {$volume} Min: {$minTxVol}");
			iLog("[TraderBot] Balance on {$askName}: {$askBalance} - Balance on {$bidName}: {$bidBalance}");
			return;
		}

		$currentTime = time();
		if ($currentTime - $this->lastTrade < $this->tradeWait) {
			$dT = $currentTime - $this->lastTrade;
			iLog("[TraderBot] WARNING: Can't automate this trade, last trade occurred {$dT}s ago - must wait {$this->tradeWait}s");
			return;
        }
		
		$finalProfit = ($profit / $volume) * $finalVolume;
		$finalPerc = (1 - ($finalVolume - ($finalProfit / $buyprice)) / $finalVolume) * 100;
		
		if ($finalProfit < $this->client->getProfitThresh() || $finalPerc < $this->client->getPercThresh()) {
			iLog("[TraderBot] WARNING: Profit or profit percentage lower than thresholds - Profit: {$finalProfit} (t: ".$this->client->getProfitThresh().") Perc: {$finalPerc} (t: ".$this->client->getPercThresh().")");
			return;
		}

		$pTrade = array("profit" => $finalProfit,
						"volume" => $finalVolume,	
						"kask" => $kask,
						"kbid" => $kbid,
						"askName" => $askName,
						"bidName" => $bidName,
						"wBuyPrice" => $wBuyPrice,
						"wSellPrice" => $wSellPrice,
						"buyPrice" => $buyprice,
						"sellPrice" => $sellprice );
		
		iLog("[TraderBot] Adding potential trade - Profit: {$finalProfit}USD for {$finalVolume}BTC - Buy {$askName} @{$buyprice} ({$wBuyPrice}) - Sell {$bidName} @{$sellprice} ({$wSellPrice}) - ~{$finalPerc}%");
		
		array_push($this->potentialTrades, $pTrade);
	}
	

	/*** VERY IMPORTANT FUNCTION ***/
	/** Set out leniency here - balanceMargin == slippage **/
	public function getMinTradeableVolume($buyPrice, $usdBal, $btcBal)
	{
		global $config;
		iLog("[TraderBot] getMinTradeableVolume - buy: {$buyPrice} usd: {$usdBal} btc: {$btcBal}");
		$bMargin = $this->client->getBalanceMargin();
		$min1 = $usdBal / ((1 + $bMargin) * $buyPrice);
		$min2 = $btcBal / (1 + $bMargin);
		return min($min1, $min2);
	}
	
	public function getPrivateMarket($mname)
	{
		return $this->client->getPrivateMarket($mname);
	}
	
	public function getPrivateMarketBalance($mname, $currency)
	{
		return $this->client->getPrivateMarket($mname)->getBalance($currency);
	}

	public function updatePrivateMarketBalances()
	{
		iLog("[TraderBot] Updating market balances...");
		$this->client->updateBalances();
	}

	public function watchBalances()
	{
		return true;
	}

	/**** VERY IMPORTANT: modify to have accurate buy/sell info ***/
	public function executeTrade($trade, $state='open')
	{
		if ($trade) {
			$buyMarket = $this->client->getPrivateMarket($trade['askName']);
			$sellMarket = $this->client->getPrivateMarket($trade['bidName']);
			
			if ($buyMarket && $sellMarket) {
				$this->lastTrade = time();
		
				iLog("[TraderBot] Executing Trade of {$trade['volume']}BTC: Buy {$trade['askName']} @{$trade['buyPrice']} - Sell {$trade['bidName']} @{$trade['sellPrice']}");
				
				$buyMarket->buy($trade['volume'], $trade['buyPrice']);
				$sellMarket->sell($trade['volume'], $trade['sellPrice']);
				
				/***** NEED TO UPDATE OUR MYSQL RECORDS HERE FOR TRADE *****/
				//addOpenTransactions();
			}
		}
	}
	
	public function getSetting($setting)
	{
		return isset($this->settings[$setting]) ? $this->settings[$setting] : NULL;
	}
}
?>