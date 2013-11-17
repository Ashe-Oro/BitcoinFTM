<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("utils/database_util.php");
?>
<style type="text/css">
.info_row {
	display: block;
	width: 100%;
	font-size: 13px;
	min-height: 20px;
}

.info {
	display: block;
	float: left;
	width: 300px;
}

.info b {
	display: block;
	float: left;
	width: 120px;
}
</style>

<?php

$settings = array();

//time scale to test.  should be a few times/min when analyzing live data
$settings['scale'] = 900000; // every 15 mins for now


//start and end dates for testing.  I'm not sure if we want to test on historical data...daily information isn't very helpful
$settings['start'] = "1-7-2013";
$settings['end'] = "3-10-2013";

//capital available
$settings['capital'] = 1;

//min spread we are willing to entertain.  This would be higher than the avg spread
$settings['min'] = 12;
$settings['orig_min'] = $settings['min'];

//spread delta we will close with.  In this case it's $5 more narrow than the entry spread.
$settings['ds'] = 2;

$settings['echo'] = true;

// allow URL-level overrides of default values
if (isset($_GET['scale'])) {
	$settings['scale'] = $_GET['scale'];
}

if (isset($_GET['start'])) {
	$settings['start'] = $_GET['start'];
}

if (isset($_GET['end'])) {
	$settings['end'] = $_GET['end'];
}

if (isset($_GET['capital'])) {
	$settings['capital'] = $_GET['capital'];
}

if (isset($_GET['min'])) {
	$settings['min'] = $_GET['min'];
	$settings['orig_min'] = $settings['min'];
}

if (isset($_GET['ds'])) {
	$settings['ds'] = $_GET['ds'];
}

if (isset($_GET['echo'])) {
	$settings['echo'] = $_GET['echo'];
}

$settings['echo'] = 1;
// calls the doBtcSpreadTrades function below
/*
for ($i = 10; $i < 11; $i++) {
	echo '<div style="float: left; display: block; margin-right: 10px;">';
	$settings['min'] = $i;
	for ($j = 0.5; $j < 1; $j += 0.5) {
		$settings['ds'] = $j;
		$myProfit = doBtcSpreadTrades($settings);
		echo "min: {$i} ds: {$j} profit: {$myProfit}<br />";
		flush();
	}
	echo '</div>';
}
*/
echo "<p>STARTING RUN FOR min: {$settings['min']} ds: {$settings['ds']} scale: {$settings['scale']}</p>";

doBtcSpreadTrades($settings);


/***
 
  	Function: doBTCSpreadTrades
  	Purpose: 
 
    @param (array) - details about trade conditions
        -scale: frequency of spread comparison
        -start: date/time to start spread comparison (only for back testing)
        -end: date/time to end spread comparison (only for back testing)
        -capital: # of Bitcoins to trade
        -min:  minimum spread (in $ terms) that we are willing to OPEN a position.  Hard coded at $10 for now.
        -ds:  current delta of the spread at exchange1 and exchange2
 
 
 
*/


function doBtcSpreadTrades($settings)
{
    //parses and sets local variables for testing date ranges which were passed in via array
    $startDate = date_parse($settings['start']);
	$endDate = date_parse($settings['end']);
	
    //parses out separate month, day, year from array returned from date_parse
	$month = $startDate['month'];
	$day = $startDate['day'];
	$year = $startDate['year'];
	
    //extracts the Day, Month, Year for back testing
	$endMonth = $endDate['month'];
	$endDay = $endDate['day'];
	$endYear = $endDate['year'];
	
   	//sets start and end date formats for comparison purposes
	$s = "{$day}-{$month}-{$year} 00:00:00";
	$e = "{$endDay}-{$endMonth}-{$endYear} 00:00:00";

    //converts times to standard UNIX time stamp for ease of comparison
	$startTime = strtotime($s);
	$endTime = strtotime($e);
	
    //defining a trade and its characteristics
	$trade = array(
		'capital' => $settings['capital'], 
		'status' => 'SEARCHING', 
		'open' => 0, 
		'close' => 0, 
		'openTotal' => 0,
		'closeTotal' => 0,
		'openMtGoxSell' => 0,
		'openBitstampClose' => 0,
		'closeMtGoxBuy' => 0,
		'closeBitstampSell' => 0,
		'mtGoxProfit' => 0,
		'bitstampProfit' => 0,
		'profit' => 0
	);
	$totalProfit = 0;
	
	$curTime = $startTime;
	while ($curTime <= $endTime) {
		$vals = getPricesAtTimestamp($curTime); 
		$m = $vals['mtgox'];
		$b = $vals['bitstamp'];
		
		$d = getTradeSpread($m, $b);
		//echo date('Y-M-d H:m:s', $curTime).': '.$d.'<br />';
		//flush();
		
		if ($d != -1) { // returns -1 if JSON or DB data is invalid
			if ($trade['status'] == 'SEARCHING') {
				if ($d >= $settings['min']) {
					$trade = echoTrade(openTrade($trade, $curTime, $d, $m, $b), $settings['echo']);
				}
			} else if ($trade['status'] == 'OPEN') {
				if ($d < $trade['open']) {
					if ($trade['open'] - $d >=  $settings['ds']) {
						$trade = echoTrade(closeTrade($trade, $curTime, $d, $m, $b), $settings['echo']);
						$totalProfit += $trade['profit'];	
						
						/**** HERE IS WHERE WE IMPLEMENT NEXT ENTRY STRATEGY *****/
						// I'm just taking wild guesses right now to see what effects it has on profit
						if ($trade['trend'] == 'TBULL') {
							//$settings['min'] += $settings['ds']; // just a wild guess here at a walk
							$settings['min'] = $settings['min']+0.5;
						} else
						if ($trade['trend'] == 'TBEAR') {
							//$settings['min'] -= $settings['ds']; // just a wild guess here at a walk
							$settings['min'] = $settings['min']-0.5;
						} else
						if ($trade['trend'] == 'PCONV') {
							//$settings['min'] = $settings['ds']; // just a wild guess here at a walk
							$settings['min'] = $d;
						} else
						if ($trade['trend'] == 'PDIV') {
							$settings['min'] = $settings['min']; // just a wild guess here at a walk
						}
					}
				}
			} else if ($trade['status'] == 'CLOSED'){
				if ($d >= $settings['min']) {
					$trade = echoTrade(openTrade($trade, $curTime, $d, $m, $b), $settings['echo']);
				}
			} else  {
				// ...queef?
			}
		}
		
		$curTime += $settings['scale'];
	}
	
	if ($settings['echo']) { echo "<p><b>TOTAL TRADE PROFIT:</b> {$totalProfit}</p>"; }
	
	return $totalProfit;
}

function echoTrade($trade, $echo)
{
	if (!$trade) { return NULL; }
	$isOpenTrade = ($trade['status'] == 'OPEN');
	
	if ($echo) {
		
		echo "<div class='info_row'>";
		echo "<div class='info'><b>{$trade['status']} Date:</b> ".date('d M Y G:h:s', $trade['openTimestamp'])."</div>\n";
		
		if ($isOpenTrade) {
			echo "<div class='info'><b>Spread:</b> $".$trade['open']."</div>\n";
			echo "<div class='info'><b>MtGox SELL:</b> $".$trade['openMtGoxSell']."</div>\n";
			echo "<div class='info'><b>Bitstamp BUY:</b> $".$trade['openBitstampBuy']."</div>\n";
			echo "</div>";
		} else {
			echo "<div class='info'><b>Spread:</b> $".$trade['close']."</div>\n";
			echo "<div class='info'><b>MtGox BUY:</b> $".$trade['closeMtGoxBuy']."</div>\n";
			echo "<div class='info'><b>Bitstamp SELL:</b> $".$trade['closeBitstampSell']."</div>\n";
			echo "</div>";
			
			echo "<div class='info_row'>";
			$mtGoxProfit = getMtGoxProfit($trade);
			$bitstampProfit = getBitstampProfit($trade);
			echo "<div class='info'><b>MtGox PROFIT: </b>$".$mtGoxProfit."</div>\n";
			echo "<div class='info'><b>Bitstamp PROFIT: </b>$".$bitstampProfit."</div>\n";
			echo "<div class='info'><b>TRADE PROFIT: </b>$".$trade['profit']."</div>\n";
			echo "</div>";
			
			echo "<div class='info_row'>";
			echo "<div class='info'><b>MtGox TREND: </b>$".$trade['mtGoxTrend']."</div>\n";
			echo "<div class='info'><b>Bitstamp TREND: </b>$".$trade['bitstampTrend']."</div>\n";
			echo "<div class='info'><b>Trade TREND: </b>".$trade['trend']."</div>\n";
			echo "</div>";
		}
		if (!$isOpenTrade) { echo "<hr />"; }
		flush();
	}
	return $trade;
}


function openTrade($trade, $curTime, $d, $mtgox, $bitstamp)
{
	$trade['status'] = 'OPEN';
	
	$trade['open'] = $d;
	$trade['openTotal'] = $trade['capital'] * $d;
	$trade['openTimestamp'] = $curTime;
	
	$trade['openMtGoxSell'] = $mtgox;
	$trade['openBitstampBuy'] = $bitstamp;
	$trade['closeMtGoxBuy'] = 0;
	$trade['closeBitstampSell'] = 0;
	
	$trade['close'] = 0;
	$trade['closeTimestamp'] = -1;
	
	$trade['profit'] = 0;
	$trade['mtGoxProfit'] = 0;
	$trade['bitstampProfit'] = 0;
	
	$trade['trend'] = 'NONE';
	$trade['mtGoxTrend'] = 0;
	$trade['bitstampTrend'] = 0;
	
	return $trade;
}

function closeTrade($trade, $curTime, $d, $mtgox, $bitstamp)
{
	$trade['status'] = 'CLOSED';
	
	$trade['close'] = $d;
	$trade['closeTotal'] = $trade['capital'] * $d;
	$trade['closeTimestamp'] = $curTime;
	
	$trade['closeMtGoxBuy'] = $mtgox;
	$trade['closeBitstampSell'] = $bitstamp;
	
	$trade['mtGoxProfit'] = getMtGoxProfit($trade);
	$trade['bitstampProfit'] = getBitstampProfit($trade);
	$trade['profit'] = getTradeProfit($trade);
	
	$trade['mtGoxTrend'] = getXchgTradeTrend($trade, 'mtgox');
	$trade['bitstampTrend'] = getXchgTradeTrend($trade, 'bitstamp');
	$trade['trend'] = getTradeTrend($trade);
	
	return $trade;
}

function getTradeProfit($trade)
{
	// dS - k*Fgox(Sgox+bgox) + Fstamp(Sstamp+bstamp)
	$profit = 0;
	if ($trade['status'] == 'CLOSED') {
		$mtGoxProfit = getMtGoxProfit($trade);
		$bitstampProfit = getBitstampProfit($trade);
		$profit = $mtGoxProfit + $bitstampProfit;
		$profit *= $trade['capital']; // STEP 3!!!!!
	}
	return $profit;
}

function getMtGoxProfit($trade) {
	return  getMtGoxCommission(1)*($trade['openMtGoxSell']-$trade['closeMtGoxBuy']); // in a rising BTC market, usually a neg number
}

function getBitstampProfit($trade) {
	return  getBitstampCommission(1)*($trade['closeBitstampSell']-$trade['openBitstampBuy']); // in a rising BTC market, usually a pos number
}

function getBitstampCommission($volume)
{
	$com = 0.006;
	// calcs in here
	return 1 - $com;
}

function getMtGoxCommission($volume)
{
	$com = 0.005;
	// calcs in here
	return 1 - $com;
}

function getTradeSpread($mtgox, $bitstamp)
{
	if ($mtgox > 0 && $bitstamp > 0) {
		return $mtgox - $bitstamp; 
	}
	return -1;
}

function openOrCloseTrade($trade)
{
	return ($trade['status'] == 'OPEN') ? 'CLOSED': 'OPEN';
}

function getTradeTrend($trade)
{
	$trend = 'NONE';
	if (isTandemBullish($trade)){
		$trend = 'TBULL';
	} else
	if (isTandemBearish($trade)){
		$trend = 'TBEAR';
	} else
	if (isPriceConvergance($trade)){
		$trend = 'PCONV';
	} else
	if (isPriceDivergance($trade)){
		$trend = 'PDIV';
	}
	return $trend;
}


function getXchgTradeTrend($trade, $xchg)
{
	if ($xchg == 'mtgox') {
		return $trade["closeMtGoxBuy"] - $trade["openMtGoxSell"];
	} else if ($xchg == 'bitstamp') {
		return $trade["closeBitstampSell"] - $trade["openBitstampBuy"];
	}
	return 0;
}


function isTandemBullish($trade)
{
	$mtgoxT = getXchgTradeTrend($trade, 'mtgox');
	$bitstampT = getXchgTradeTrend($trade, 'bitstamp');
	return ($mtgoxT > 0 && $bitstampT > 0 && $bitstampT > $mtgoxT);
}

function isTandemBearish($trade)
{
	$mtgoxT = getXchgTradeTrend($trade, 'mtgox');
	$bitstampT = getXchgTradeTrend($trade, 'bitstamp');
	return ($mtgoxT < 0 && $bitstampT < 0 && $bitstampT < $mtgoxT);
}

function isPriceConvergance($trade)
{
	$mtgoxT = getXchgTradeTrend($trade, 'mtgox');
	$bitstampT = getXchgTradeTrend($trade, 'bitstamp');
	return ($mtgoxT < 0 && $bitstampT > 0);
}

function isPriceDivergance($trade)
{
	$mtgoxT = getXchgTradeTrend($trade, 'mtgox');
	$bitstampT = getXchgTradeTrend($trade, 'bitstamp');
	return ($mtgoxT > 0 && $bitstampT < 0);
}

function getPricesAtTimestamp($timestamp)
{
	$db = new Database("127.0.0.1", "root", "root", "ftm");

	$ret = array('mtgox' => -1, 'bitstamp' => -1);
	
	$xchg = 'mtgox';
	$query = "SELECT * FROM {$xchg}_history WHERE timestamp < {$timestamp} ORDER BY timestamp DESC LIMIT 1";
	$result = $db->query($query);
	if($row = $db->fetch_array_assoc($result)){
		$ret['mtgox'] = $row['price'];
	}
	
	$xchg = 'bitstamp';
	$query = "SELECT * FROM {$xchg}_history WHERE timestamp < {$timestamp} ORDER BY timestamp DESC LIMIT 1";
	$result = $db->query($query);
	$bitstamp = array();
	if($row = $db->fetch_array_assoc($result)){
		$ret['bitstamp'] = $row['price'];
	}
	
	$db->close();

	return $ret;
}

?>

