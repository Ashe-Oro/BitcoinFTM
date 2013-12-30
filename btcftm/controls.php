<?php
$noEchoLog = 1;
session_start();
$signedIn = (isset($_SESSION['adminAccess']) && isset($_SESSION['clientID']) && isset($_SESSION['username'])) ? 1 : 0;

if (isset($_GET['signout'])){
	unset($_SESSION);	
	session_unset();
	$signedIn = 0;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>BTC FTM</title>
<script language="javascript" type="application/javascript" src="jquery/jquery-1.8.2.min.js"></script>

<link type="text/css" rel="stylesheet" href="css/core.css" />
<link type="text/css" rel="stylesheet" href="css/controls.css" />
</head>

<body>
<?php
if ($signedIn == 1) {
	require_once("core/include.php");
		
	$settingsUpdateMessage = "";
	if (isset($_POST['submit-settings'])){
		//var_dump($_POST);
		
		try {
			$maxTxVolume = (float) $_POST['maxTxVolume'];
			$minTxVolume = (float) $_POST['minTxVolume'];
			$balanceMargin = (float) $_POST['balanceMargin'];
			$profitThresh = (float) $_POST['profitThresh'];
			$percThresh = (float) $_POST['percThresh'];
			$trading = (int) $_POST['trading'];
			
			//if ($maxTxVolume > 0 && $minxTxVolume > 0 && $balanceMargin > 0 && $profitThresh > 0 && $percThresh > 0){
				$DB->query("UPDATE clients SET maxTxVolume = {$maxTxVolume}, minTxVolume = {$minTxVolume}, balanceMargin = {$balanceMargin}, profitThresh = {$profitThresh}, percThresh = {$percThresh}, trading = {$trading} WHERE clientID = {$_SESSION['clientID']}");
				$settingsUpdateMessage = "Settings updated";
			//} else {
				//$settingsUpdateMessage = "Failed to update settings: all values must be greater than zero<br />{$maxTxVolume} {$minTxVolume} {$balanceMargin} {$profitThresh} {$percThresh}";
			//}
		} catch (Exception $e) {
			$settingsUpdateMessage = "Failed to update settings: ".$e->getMessage();
		}
		iLog($settingsUpdateMessage);
	}
	
	
	$client = new Client($_SESSION['username']);
	$arb = NULL;
	
	if ($client->isActive()) {
		$cList = new ClientsList(array($client->getID()));
		$arb = new Arbitrage($cList);
	}
	
	$range = "1-day";
	if (isset($_GET['range'])){
		$range = $_GET['range'];
	}
	if (isset($_POST['change-range'])){
		$range = $_POST['change-range'];
	}
	
	$period = PERIOD_15M;
	if (isset($_POST['change-period'])){
		$period = (int) $_POST['change-period'];
	}
	
	$compare = "30-minute";
	if (isset($_POST['market-compare'])){
		$compare = $_POST['market-compare'];
	}
	
	//var_dump($_POST);

?>

<div id="container">

<div id="header" class="clearfix full">
    <fieldset class="info" id="account_info" >
        <legend>Your <?php echo ($config['live']) ? "LIVE" : "TESTING"; ?> Accounts</legend>
        
        <fieldset id="mtgox-wallet">
            <legend>MtGox</legend>
            <div class="info_row"><b>USD: </b><?php echo number_format($client->getMarketBalance("MtGox", "USD"), 4); ?></div>
            <div class="info_row"><b>BTC: </b><?php echo number_format($client->getMarketBalance("MtGox", "BTC"), 8); ?> BTC</div>
        </fieldset>
        
        <fieldset id="bitstamp-wallet">
            <legend>Bitstamp</legend>
            <div class="info_row"><b>USD: </b><?php echo number_format($client->getMarketBalance("Bitstamp", "USD"), 4); ?> USD</div>
            <div class="info_row"><b>BTC: </b><?php echo number_format($client->getMarketBalance("Bitstamp", "BTC"), 8);  ?> BTC</div>
        </fieldset>
    
    </fieldset>

    <div id="logo"><img src="images/ftm.png" /></div>
    <h2 id="logo-title">Bitcoin Finance Trade Manager</h2>
    
    <h3 id="logo-welcome">Welcome, <b><?php echo $client->getName(); ?></b>! <small>(<a href="controls.php?signout=1">Sign Out</a>)</small></h3>

    <div id="trading-status">
        <span id="testing-status">
        <?php
        $dMode = ($config['live']) ? "<span style='color: #090;'><b>LIVE</b></span>" : "<span style='color: #F00;'><b>TESTING</b></span>";
        echo "Data Mode: {$dMode}";
        ?>
        </span> | <span id="client-active">
		<?php
        $cMode = ($client->isActive()) ? "<span style='color: #090;'><b>ACTIVE</b></span>" : "<span style='color: #F00;'><b>INACTIVE</b></span>";
        echo "Client Mode: {$cMode}";
        ?>
        </span> | <span id="client-trading">
        <?php
        $tMode = ($client->isTrading()) ? "<span style='color: #090;'><b>ACTIVE</b></span>" : "<span style='color: #F00;'><b>STANDBY</b></span>";
        echo "Trading Status: {$tMode}";
        ?>
        </span>
    </div>
</div>

<div style="height: 1px; clear: both; display: block;"></div>

<div id="info_wrapper" class="clearfix full">
    <fieldset class="info" id="settings-wrapper">
        <legend>Your Settings</legend>
        <?php if (strlen($settingsUpdateMessage)) { echo "<h4>{$settingsUpdateMessage}</h4>"; } ?>
        <form name="client-settings" id="client-settings" method="post">
        <div class="info_row"><label for="minTxVolume">Min Trade Volume:</label> <input type="text" id="minTxVolume" name="minTxVolume" value="<?php echo $client->getMinTxVolume(); ?>" size="8" />BTC</div>
        <div class="info_row"><label for="maxTxVolume">Max Trade Volume:</label> <input type="text" id="maxTxVolume" name="maxTxVolume" value="<?php echo $client->getMaxTxVolume(); ?>" size="8" />BTC</div>
        <div class="info_row"><label for="balanceMargin">Balance Margin:</label> <input type="text" id="balanceMargin" name="balanceMargin" value="<?php echo $client->getBalanceMargin(); ?>" size="5" />%</div>
        <div class="info_row"><label for="profitThresh">Profit Threshold:</label> <input type="text" id="profitThresh" name="profitThresh" value="<?php echo $client->getProfitThresh(); ?>" size="5" />USD</div>
        <div class="info_row"><label for="percThresh">Percentage Threshold:</label> <input type="text" id="percThresh" name="percThresh" value="<?php echo $client->getPercThresh(); ?>" size="5" />%</div>
        <div class="info_row"><label for="trading">Trading Status:</label> <input type="radio" id="trading_active" name="trading" value="1"<?php echo ($client->isTrading()) ? " checked='checked'" : ""; ?> /> ACTIVE <input type="radio" id="trading_standyby" name="trading" value="0"<?php echo ($client->isTrading()) ? "" : " checked='checked'"; ?> /> STANDBY </div>
        <input type="submit" name="submit-settings" id="submit-settings" value="Update Settings" class="submit" />
        </form>
    
    </fieldset>
    
    <fieldset class="info" id="markets-wrapper">
        <legend>Markets - Updated <?php echo date("d M Y  H:i:s"); ?></legend>
        <form name="compare-to" id="compare-to" method="post">
        Compare To:
        <select name="market-compare" id="market-compare">
        	<option value="1-minute"<?php if ($compare == "1-minute") { echo " selected='selected'"; } ?>>1 Minute Ago</option>
            <option value="3-minute"<?php if ($compare == "3-minute") { echo " selected='selected'"; } ?>>3 Minutes Ago</option>
            <option value="5-minute"<?php if ($compare == "10-minute") { echo " selected='selected'"; } ?>>5 Minutes Ago</option>
        	<option value="10-minute"<?php if ($compare == "10-minute") { echo " selected='selected'"; } ?>>10 Minutes Ago</option>
            <option value="15-minute"<?php if ($compare == "15-minute") { echo " selected='selected'"; } ?>>15 Minutes Ago</option>
            <option value="30-minute"<?php if ($compare == "30-minute") { echo " selected='selected'"; } ?>>30 Minutes Ago</option>
            <option value="1-hour"<?php if ($compare == "1-hour") { echo " selected='selected'"; } ?>>1 Hour Ago</option>
            <option value="2-hour"<?php if ($compare == "2-hour") { echo " selected='selected'"; } ?>>2 Hours Ago</option>
            <option value="4-hour"<?php if ($compare == "4-hour") { echo " selected='selected'"; } ?>>4 Hours Ago</option>
            <option value="6-hour"<?php if ($compare == "6-hour") { echo " selected='selected'"; } ?>>6 Hours Ago</option>
            <option value="12-hour"<?php if ($compare == "12-hour") { echo " selected='selected'"; } ?>>12 Hours Ago</option>
            <option value="1-day"<?php if ($compare == "1-day") { echo " selected='selected'"; } ?>>1 Day Ago</option>
            <option value="3-day"<?php if ($compare == "3-day") { echo " selected='selected'"; } ?>>3 Days Ago</option>
            <option value="7-day"<?php if ($compare == "7-day") { echo " selected='selected'"; } ?>>7 Days Ago</option>
            <option value="10-day"<?php if ($compare == "10-day") { echo " selected='selected'"; } ?>>10 Days Ago</option>
            <option value="20-day"<?php if ($compare == "20-day") { echo " selected='selected'"; } ?>>20 Days Ago</option>
            <option value="30-day"<?php if ($compare == "30-day") { echo " selected='selected'"; } ?>>30 Days Ago</option>
            <option value="60-day"<?php if ($compare == "60-day") { echo " selected='selected'"; } ?>>60 Days Ago</option>
            <option value="120-day"<?php if ($compare == "120-day") { echo " selected='selected'"; } ?>>120 Days Ago</option>
            <option value="180-day"<?php if ($compare == "180-day") { echo " selected='selected'"; } ?>>180 Days Ago</option>
            <option value="1-year"<?php if ($compare == "1-year") { echo " selected='selected'"; } ?>>1 Year Ago</option>
        </select>
        <input type="submit" name="submit-compare" id="submit-compare" value="Go" />
        </form>
        
        <?php
        if ($arb){
			$rangeStr = str_replace("-", " ", $range);
            $startTime = strtotime("-{$rangeStr}");
			
			$compareStr = str_replace("-", " ", $compare);
			$compareTime = strtotime("-{$compareStr}");
            
            $mMarket = $arb->getArbitrer()->getMarket("MtGoxUSD");
            $bMarket = $arb->getArbitrer()->getMarket("BitstampUSD");
        
            $mTicker = $mMarket->getCurrentTicker();
            $bTicker = $bMarket->getCurrentTicker();
            $sTicker = $mTicker->getTickerSpread($bTicker); // get the spread
			
			$mCompTicker = $mMarket->getHistoryTicker($compareTime);
			$bCompTicker = $bMarket->getHistoryTicker($compareTime);
			$sCompTicker = $mCompTicker->getTickerSpread($bCompTicker);
       
	   		if ($period == 0) {
				$mHistory = $mMarket->getHistoryTickers($startTime);
				$bHistory = $bMarket->getHistoryTickers($startTime);
			} else {
				$mHistory = $mMarket->getHistoryPeriodTickers($startTime, time(), $period);
				$bHistory = $bMarket->getHistoryPeriodTickers($startTime, time(), $period);
			}
			
			$mCalc = new TickerCalculator($mHistory);
			$bCalc = new TickerCalculator($bHistory);
			
			if ($period == 0){
				$mSMA = $mMarket->getHistorySMA($startTime);
				$mXMA = $mMarket->getHistoryXMA($startTime);
				$bSMA = $bMarket->getHistorySMA($startTime);
				$bXMA = $bMarket->getHistoryXMA($startTime);
			} else {
				$mSMA = $mMarket->getHistoryPeriodSMA($startTime, time(), $period);
				$mXMA = $mMarket->getHistoryPeriodXMA($startTime, time(), $period);
				$bSMA = $bMarket->getHistoryPeriodSMA($startTime, time(), $period);
				$bXMA = $bMarket->getHistoryPeriodXMA($startTime, time(), $period);
			}
			
			
        }
		
		function showInfoRow($marketName, $ticker, $tickerOld)
		{
			$lName = strtolower($marketName);
			$dHigh = $ticker->getHigh() - $tickerOld->getHigh();
			$dLow = $ticker->getLow() - $tickerOld->getLow();
			$dLast = $ticker->getLast() - $tickerOld->getLast();
			$dBid = $ticker->getBid() - $tickerOld->getBid();
			$dAsk = $ticker->getAsk() - $tickerOld->getAsk();
			$dVolume = $ticker->getVolume() - $tickerOld->getVolume();
			
			$pHigh = number_format($dHigh / $tickerOld->getHigh(), 4);
			$pLow = number_format($dLow / $tickerOld->getLow(), 4);
			$pLast = number_format($dLast / $tickerOld->getLast(), 4);
			$pBid = number_format($dBid / $tickerOld->getBid(), 4);
			$pAsk = number_format($dAsk / $tickerOld->getAsk(), 4);
			$pVolume = number_format($dVolume / $tickerOld->getVolume(), 4);
			
			
			$cHigh = ($dHigh > 0) ? "pos" : (($dHigh < 0) ? "neg" : "neu");
			$cLow = ($dLow > 0) ? "pos" : (($dLow < 0) ? "neg" : "neu");
			$cLast = ($dLast > 0) ? "pos" : (($dLast < 0) ? "neg" : "neu");
			$cBid = ($dBid > 0) ? "pos" : (($dBid < 0) ? "neg" : "neu");
			$cAsk = ($dAsk > 0) ? "pos" : (($dAsk < 0) ? "neg" : "neu");
			$cVol = ($dVolume > 0) ? "pos" : (($dVolume < 0) ? "neg" : "neu");
			
			//echo "{$marketName}: {$dHigh} {$cHigh} {$dLow} {$dLast} {$dBid} {$dAsk} {$dVolume}<br />";
			
			echo "<fieldset id='{$lName}-market'>";
			echo "<legend>{$marketName}</legend>";
            echo "<div class='info_row'>";
            echo "<ul class='market-ticker' id='{$lName}-ticker-info'>";
            echo "<li id='{$lName}-ticker-high' class='{$cHigh}'><label>High:</label>".number_format($ticker->getHigh(), 4)." ({$pHigh}%)</li>";
            echo "<li id='{$lName}-ticker-low' class='{$cLow}'><label>Low:</label>".number_format($ticker->getLow(), 4)." ({$pLow}%)</li>";
            echo "<li id='{$lName}-ticker-last' class='{$cLast}'><label>Last:</label>".number_format($ticker->getLast(), 4)." ({$pLast}%)</li>";
            echo "<li id='{$lName}-ticker-bid' class='{$cBid}'><label>Bid:</label>".number_format($ticker->getBid(), 4)." ({$pBid}%)</li>";
            echo "<li id='{$lName}-ticker-ask' class='{$cAsk}'><label>Ask:</label>".number_format($ticker->getAsk(), 4)." ({$pAsk}%)</li>";
            echo "<li id='{$lName}-ticker-volume' class='{$cVol}'><label>Volume:</label>".number_format($ticker->getVolume(), 8)." ({$pVolume}%)</li>";
            echo "</ul>";
            echo "</div>";
    		echo "</fieldset>";
		}
			
		showInfoRow("MtGox", $mTicker, $mCompTicker);
		showInfoRow("Bitstamp", $bTicker, $bCompTicker);
		showInfoRow("Spread", $sTicker, $sCompTicker);
        ?>
    
    </fieldset>
</div>

<div class="clearfix full">
    <fieldset class="info" id="trends-wrapper">
    <?php $startDateStr = date("d M Y  H:i:s", $startTime); ?>
    <legend>History Trends - from <?php echo $startDateStr; ?> to now - Range: <?php echo $range; ?> Period: <?php echo $period; ?></legend>
    	<form name="change-range-form" id="change-range-form" method="post">
        <b>History Range: </b><select id="change-range" name="change-range">
        	<option value="10-minute"<?php if ($range == "10-minute") { echo " selected='selected'"; } ?>>10 Minute</option>
            <option value="30-minute"<?php if ($range == "30-minute") { echo " selected='selected'"; } ?>>30 Minute</option>
            <option value="1-hour"<?php if ($range == "1-hour") { echo " selected='selected'"; } ?>>1 Hour</option>
            <option value="2-hour"<?php if ($range == "2-hour") { echo " selected='selected'"; } ?>>2 Hour</option>
            <option value="4-hour"<?php if ($range == "4-hour") { echo " selected='selected'"; } ?>>4 Hour</option>
            <option value="6-hour"<?php if ($range == "6-hour") { echo " selected='selected'"; } ?>>6 Hour</option>
            <option value="12-hour"<?php if ($range == "12-hour") { echo " selected='selected'"; } ?>>12 Hour</option>
            <option value="1-day"<?php if ($range == "1-day") { echo " selected='selected'"; } ?>>1 Day</option>
            <option value="3-day"<?php if ($range == "3-day") { echo " selected='selected'"; } ?>>3 Day</option>
            <option value="7-day"<?php if ($range == "7-day") { echo " selected='selected'"; } ?>>7 Day</option>
            <option value="10-day"<?php if ($range == "10-day") { echo " selected='selected'"; } ?>>10 Day</option>
            <option value="20-day"<?php if ($range == "20-day") { echo " selected='selected'"; } ?>>20 Day</option>
            <option value="30-day"<?php if ($range == "30-day") { echo " selected='selected'"; } ?>>30 Day</option>
            <option value="60-day"<?php if ($range == "60-day") { echo " selected='selected'"; } ?>>60 Day</option>
            <option value="120-day"<?php if ($range == "120-day") { echo " selected='selected'"; } ?>>120 Day</option>
            <option value="180-day"<?php if ($range == "180-day") { echo " selected='selected'"; } ?>>180 Day</option>
            <option value="1-year"<?php if ($range == "1-year") { echo " selected='selected'"; } ?>>1 Year</option>
        </select>
         <b>Period: </b><select id="change-period" name="change-period">
        	<option value="0"<?php if ($period == 0) { echo " selected='selected'"; } ?>>None</option>
            <option value="<?php echo PERIOD_1M; ?>"<?php if ($period == PERIOD_1M) { echo " selected='selected'"; } ?>>1 Minute</option>
            <option value="<?php echo PERIOD_3M; ?>"<?php if ($period == PERIOD_3M) { echo " selected='selected'"; } ?>>3 Minute</option>
            <option value="<?php echo PERIOD_5M; ?>"<?php if ($period == PERIOD_5M) { echo " selected='selected'"; } ?>>5 Minute</option>
            <option value="<?php echo PERIOD_15M; ?>"<?php if ($period == PERIOD_15M) { echo " selected='selected'"; } ?>>15 Minute</option>
            <option value="<?php echo PERIOD_30M; ?>"<?php if ($period == PERIOD_30M) { echo " selected='selected'"; } ?>>30 Minute</option>
            <option value="<?php echo PERIOD_1H; ?>"<?php if ($period == PERIOD_1H) { echo " selected='selected'"; } ?>>1 Hour</option>
            <option value="<?php echo PERIOD_2H; ?>"<?php if ($period == PERIOD_2H) { echo " selected='selected'"; } ?>>2 Hour</option>
            <option value="<?php echo PERIOD_4H; ?>"<?php if ($period == PERIOD_4H) { echo " selected='selected'"; } ?>>4 Hour</option>
            <option value="<?php echo PERIOD_6H; ?>"<?php if ($period == PERIOD_6H) { echo " selected='selected'"; } ?>>6 Hour</option>
            <option value="<?php echo PERIOD_12H; ?>"<?php if ($period == PERIOD_12H) { echo " selected='selected'"; } ?>>12 Hour</option>
            <option value="<?php echo PERIOD_1D; ?>"<?php if ($period == PERIOD_1D) { echo " selected='selected'"; } ?>>1 Day</option>
            <option value="<?php echo PERIOD_3D; ?>"<?php if ($period == PERIOD_3D) { echo " selected='selected'"; } ?>>3 Day</option>
            <option value="<?php echo PERIOD_1W; ?>"<?php if ($period == PERIOD_1W) { echo " selected='selected'"; } ?>>1 Week</option>
        </select>
        <input type="submit" class="submit" value="Update" id="submit-range" name="submit-range" />
        </form>
        
        <fieldset id="history-graph-wrapper">
        <legend>History Graph</legend>
        <div><b>NOT WORKING!</b> - Need to finish after TraderBot</div>
       <?php require_once("show-history-graph.php"); ?>
        </fieldset>
    	
    	<fieldset class="history-box" id="mtgox-history">
            <legend>MtGox History Trends</legend>
            <fieldset id="mtgox-history-sma">
            	<legend>SMA</legend>
                <ul class="market-history" id="mtgox-sma-info">
                <li id="mtgox-sma-high"><label>High:</label> <br /><?php echo number_format($mSMA->getHigh(), 4); ?></li>
                <li id="mtgox-sma-low"><label>Low:</label> <br /><?php echo number_format($mSMA->getLow(), 4); ?></li>
                <?php if ($period == 0) { ?>
                <li id="mtgox-sma-last"><label>Last:</label> <br /><?php echo number_format($mSMA->getLast(), 4); ?></li>
                <li id="mtgox-sma-bid"><label>Bid:</label> <br /><?php echo number_format($mSMA->getBid(), 4); ?></li>
                <li id="mtgox-sma-ask"><label>Ask:</label> <br /><?php echo number_format($mSMA->getAsk(), 4); ?></li>
                <?php } else { ?>
                  <li id="mtgox-sma-open"><label>Open:</label> <br /><?php echo number_format($mSMA->getOpen(), 4); ?></li>
            <li id="mtgox-sma-close"><label>Close:</label> <br /><?php echo number_format($mSMA->getClose(), 4); ?></li>
            <li id="mtgox-sma-avg"><label>Avg:</label> <br /><?php echo number_format($mSMA->getAvg(), 4); ?></li>
              <li id="mtgox-sma-avg"><label>AvgVol:</label> <br /><?php echo number_format($mSMA->getAvgVolume(), 6); ?></li>
                <?php } ?>
                <li id="mtgox-sma-volume"><label>Volume:</label> <br /><?php echo number_format($mSMA->getVolume(), 6); ?></li>
                </ul>
            </fieldset>
            <fieldset id="mtgox-history-xma">
            	<legend>XMA</legend>
            <ul class="market-history" id="mtgox-xma-info">
            <li id="mtgox-xma-high"><label>High:</label> <br /><?php echo number_format($mXMA->getHigh(), 4); ?></li>
            <li id="mtgox-xma-low"><label>Low:</label> <br /><?php echo number_format($mXMA->getLow(), 4); ?></li>
             <?php if ($period == 0) { ?>
            <li id="mtgox-xma-last"><label>Last:</label> <br /><?php echo number_format($mXMA->getLast(), 4); ?></li>
            <li id="mtgox-xma-bid"><label>Bid:</label> <br /><?php echo number_format($mXMA->getBid(), 4); ?></li>
            <li id="mtgox-xma-ask"><label>Ask:</label> <br /><?php echo number_format($mXMA->getAsk(), 4); ?></li>
             <?php } else { ?>
             <li id="mtgox-xma-open"><label>Open:</label> <br /><?php echo number_format($mXMA->getOpen(), 4); ?></li>
            <li id="mtgox-xma-close"><label>Close:</label> <br /><?php echo number_format($mXMA->getClose(), 4); ?></li>
            <li id="mtgox-xma-avg"><label>Avg:</label> <br /><?php echo number_format($mXMA->getAvg(), 4); ?></li>
              <li id="mtgox-xma-avg"><label>AvgVol:</label> <br /><?php echo number_format($mXMA->getAvgVolume(), 6); ?></li>
                <?php } ?>
            <li id="mtgox-xma-volume"><label>Volume:</label> <br /><?php echo number_format($mXMA->getVolume(), 6); ?></li>
            </ul>
            </fieldset>
        </fieldset>
    
    	<fieldset class="history-box" id="bitstamp-history">
            <legend>Bitstamp History Trends</legend>
            <fieldset id="bitstamp-history-sma">
            <legend>SMA</legend>
            <ul class="market-history" id="bitstamp-sma-info">
            <li id="bitstamp-sma-high"><label>High:</label> <br /><?php echo number_format($bSMA->getHigh(), 4); ?></li>
            <li id="bitstamp-sma-low"><label>Low:</label> <br /><?php echo number_format($bSMA->getLow(), 4); ?></li>
              <?php if ($period == 0) { ?>
            <li id="bitstamp-sma-last"><label>Last:</label> <br /><?php echo number_format($bSMA->getLast(), 4); ?></li>
            <li id="bitstamp-sma-bid"><label>Bid:</label> <br /><?php echo number_format($bSMA->getBid(), 4); ?></li>
            <li id="bitstamp-sma-ask"><label>Ask:</label> <br /><?php echo number_format($bSMA->getAsk(), 4); ?></li>
             <?php } else { ?>
               <li id="bitstamp-sma-open"><label>Open:</label> <br /><?php echo number_format($bSMA->getOpen(), 4); ?></li>
            <li id="bitstamp-sma-close"><label>Close:</label> <br /><?php echo number_format($bSMA->getClose(), 4); ?></li>
            <li id="bitstamp-sma-avg"><label>Avg:</label> <br /><?php echo number_format($bSMA->getAvg(), 4); ?></li>
              <li id="bitstamp-sma-avg"><label>AvgVol:</label> <br /><?php echo number_format($bSMA->getAvgVolume(), 6); ?></li>
                <?php } ?>
            <li id="bitstamp-sma-volume"><label>Volume:</label> <br /><?php echo number_format($bSMA->getVolume(), 6); ?></li>
            </ul>
            </fieldset>
             <fieldset id="bistamp-history-xma">
             <legend>XMA</legend>
            <ul class="market-history" id="bitstamp-xma-info">
            <li id="bitstamp-xma-high"><label>High:</label> <br /><?php echo number_format($bXMA->getHigh(), 4); ?></li>
            <li id="bitstamp-xma-low"><label>Low:</label> <br /><?php echo number_format($bXMA->getLow(), 4); ?></li>
              <?php if ($period == 0) { ?>
            <li id="bitstamp-xma-last"><label>Last:</label> <br /><?php echo number_format($bXMA->getLast(), 4); ?></li>
            <li id="bitstamp-xma-bid"><label>Bid:</label> <br /><?php echo number_format($bXMA->getBid(), 4); ?></li>
            <li id="bitstamp-xma-ask"><label>Ask:</label> <br /><?php echo number_format($bXMA->getAsk(), 4); ?></li>
             <?php } else { ?>
              <li id="bitstamp-xma-open"><label>Open:</label> <br /><?php echo number_format($bXMA->getOpen(), 4); ?></li>
            <li id="bitstamp-xma-close"><label>Close:</label> <br /><?php echo number_format($bXMA->getClose(), 4); ?></li>
            <li id="bitstamp-xma-avg"><label>Avg:</label> <br /><?php echo number_format($bXMA->getAvg(), 4); ?></li>
              <li id="bitstamp-xma-avg"><label>AvgVol:</label> <br /><?php echo number_format($bXMA->getAvgVolume(), 6); ?></li>
                <?php } ?>
            <li id="bitstamp-xma-volume"><label>Volume:</label> <br /><?php echo number_format($bXMA->getVolume(), 6); ?></li>
            </ul>
            </fieldset>
        </fieldset>
    
    </fieldset>
</div>

<div class="clearfix full">
    <fieldset class="info" id="testing-wrapper">
    <legend>Testing Crap Goes Here</legend>
    
    <?php
	/*
	$bPerTickers = $bMarket->getHistoryPeriodTickers($startTime);
	var_dump($bPerTickers);
	*/
	?>
    
    </fieldset>
</div>

<div style="height: 1px; clear: both; display: block;"></div>

<?php
} else {
?>

<h2>Fuck The Man!</h2>

<script language="javascript">
document.location.href = "index.php";
</script>

<?php
}
?>
</body>
</html>