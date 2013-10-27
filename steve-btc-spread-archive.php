<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings = array();

//time scale to test.  Current this is days for historic data, but should be a few times/min when analyzing live data
$settings['scale'] = "days";

//start and end dates for testing.  I'm not sure if we want to test on historical data...daily information isn't very helpful
$settings['start'] = "1-7-2013";
$settings['end'] = "20-10-2013";

//capital available
$settings['capital'] = 1;

//min spread we are willing to entertain.  This would be higher than the avg spread
$settings['min'] = 4;

//spread delta we will close with.  In this case it's $5 more narrow than the entry spread.
$settings['ds'] = 5;

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
}

if (isset($_GET['ds'])) {
	$settings['ds'] = $_GET['ds'];
}

// calls the doBtcSpreadTrades function below
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
   // SC: this DB will be set up on my server initially 
	$mysql = mysql_connect('localhost', 'root', 'root');
	if (!$mysql) {
		die('Not connected : ' . mysql_error());
	}
	//is database FTM available?
	$db_selected = mysql_select_db('ftm', $mysql);
	if (!$db_selected) {
		die ('Can\'t use ftm : ' . mysql_error());
	}

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
	
	//sorting through historical data by time stamp and storing in to array
    $xchg = 'mtgox';
	$query = "SELECT * FROM {$xchg}_history_{$settings['scale']} WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
	$result = mysql_query($query);
	$mtgox = array();
	while($row = mysql_fetch_assoc($result)){
		$mtgox[$row['timestamp']] = $row;
	}
	
	//sorting through historical data by time stamp and storing in to array
	$xchg = 'bitstamp';
	$query = "SELECT * FROM {$xchg}_history_{$settings['scale']} WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
	$result = mysql_query($query);
	$bitstamp = array();
	while($row = mysql_fetch_assoc($result)){
		$bitstamp[$row['timestamp']] = $row;
	}
	
    //defining a trade and its characteristics
	$trade = array(
		'capital' => $settings['capital'], 
		'status' => 'SEARCHING', 
		'open' => 0, 
		'close' => 0, 
		'openTotal' => 0,
		'closeTotal' => 0,
		'openMtGox' => NULL,
		'openBitstamp' => NULL,
		'closeMtGox' => NULL,
		'closeBitstamp' => NULL,
		'openMtGoxSell' = 0,
		'openBitstampClose' = 0,
		'closeMtGoxBuy' = 0,
		'closeBitstampSell' = 0,
		'profit' => 0
	);
	
    //comparing each row of timestamp at each exhange
	if (count($mtgox) == count($bitstamp)) {
        #what is $ts and $m?
		foreach($mtgox as $ts => $m) {
			if (isset($bitstamp[$ts])){
				$b = $bitstamp[$ts];
                #spread at certain historical time
                $d = getTradeSpread($m, $b);
				echo date('d M Y', $ts).': '.$d.'<br />';
				
                #if $d == -1 then  
				if ($d != -1) { // returns -1 if JSON or DB data is invalid
					if ($trade['status'] == 'SEARCHING' && $d >= $settings['min']) {
						$trade = openTrade($trade, $d, $m, $b); 
						
						echo "<p><b>TRADE OPENED!</b><br/>\n";
						echo "<b>Date:</b> ".date('d M Y', $trade['openTimestamp'])."<br />\n";
						echo "<b>Open Spread:</b>".$d."<br />\n";
						echo "<b>MtGox Value:</b>".$m['avg']."<br />\n";
						echo "<b>Bitstamp Value:</b>".$b['avg']."<br />\n";
						echo "</p>\n";
					} if ($trade['status'] == 'OPEN') {
						if ($settings['ds'] >= $trade['open']-$d) {
							$trade = closeTrade($trade, $d, $m, $b);
							
							echo "<p><b>TRADE CLOSED!</b><br/>\n";
							echo "<b>Date:</b> ".date('d M Y', $trade['closeTimestamp'])."<br />\n";
							echo "<b>Close Spread:</b>".$d."<br />\n";
							echo "<b>MtGox Value:</b>".$m['avg']."<br />\n";
							echo "<b>Bitstamp Value:</b>".$b['avg']."<br />\n";
							echo "</p>\n";
							
							// here is where we will add the profit calc to the running total
							$profit = getTradeProfit($trade);
							echo "<b>TRADE PROFIT!!!</b><br />\n";
							echo "<b>Profit: </b>".$profit;
							
					}else if ($trade['status'] == 'CLOSED'){
						if ($d >= $settings['min'] || $d > $settings['ds']) {
							$trade = openTrade($trade, $d, $m, $b);
							
							echo "<p><b>TRADE OPENED!</b><br/>\n";
							echo "<b>Date:</b> ".date('d M Y', $trade['openTimestamp'])."<br />\n";
							echo "<b>Open Spread:</b>".$d."<br />\n";
							echo "<b>MtGox Value:</b>".$m['avg']."<br />\n";
							echo "<b>Bitstamp Value:</b>".$b['avg']."<br />\n";
							echo "</p>\n";
							
						} 
					} else  {
					}
				}
			}
		}
	}
	mysql_close($mysql);
}

/**
 * Opens a trade
 */
function openTrade($trade, $d, $mtgox, $bitstamp)
{
	$trade['status'] = 'OPEN';
	
	$trade['open'] = $d;
	$trade['openTotal'] = $trade['capital'] * $d;
	$trade['openmtgox'] = $mtgox;
	$trade['openbitstamp'] = $bitstamp;
	$trade['openTimestamp'] = $mtgox['timestamp'];
	
	$trade['openMtGoxSell'] = $mtgox['avg'];
	$trade['openBitstampBuy'] = $bitstamp['avg'];
	$trade['closeMtGoxBuy'] = 0;
	$trade['closeBitstampSell'] = 0;
	
	$trade['close'] = 0;
	$trade['closemtgox'] = NULL;
	$trade['closebitstamp'] = NULL;
	$trade['closeTimestamp'] = $mtgox['timestamp'];
	return $trade;
}

function closeTrade($trade, $d, $mtgox, $bitstamp)
{
	$trade['status'] = 'CLOSED';
	
	$trade['close'] = $d;
	$trade['closeTotal'] = $trade['capital'] * $d;
	$trade['closemtgox'] = $mtgox;
	$trade['closebitstamp'] = $bitstamp;
	$trade['closeTimestamp'] = $mtgox['timestamp'];
	
	$trade['closeMtGoxBuy'] = $mtgox['avg'];
	$trade['closeBitstampSell'] = $bitstamp['avg'];
	
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
	return  getMtGoxCommission()*($trade['openMtGoxSell']-$trade['closeMtGoxBuy']); // in a rising BTC market, usually a neg number
}

function getBitstampProfit($trade) {
	return  getBitstampCommission()*($trade['openBitstampBuy']-$trade['closeBitstampSell']); // in a rising BTC market, usually a pos number
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


/*

function openOrCloseTrade($trade)
{
	return ($trade['status'] == 'OPEN') ? 'CLOSED': 'OPEN';
}





function getTradeTrend($trade, $xchg)
{
	return $trade["open{$xchg}"]['avg'] - $trade["close{$xchg}]['avg']";
}
*/

#######
#
#is this our "Opportunity Finder"?
#
#######
function getTradeSpread($mtgox, $bitstamp)
{
    #if the average price is > 0?  
    #return the spread
    #if -1 then unable to get the current price?
	if ($mtgox['avg'] > 0 && $bitstamp['avg'] > 0) {
		return $mtgox['avg'] - $bitstamp['avg']; 
	}
	return -1;
}
/*
function isTandemBullish($trade)
{
	$mtgoxT = getTradeTrend($trade, 'mtgox');
	$bitstampT = getTradeTrend($trade, 'bitstamp')
	return ($mtgoxT > 0 && $bitstampT > 0 && $bitstampT > $mtgoxT);
}

function isTandemBearish($trade)
{
	$mtgoxT = getTradeTrend($trade, 'mtgox');
	$bitstampT = getTradeTrend($trade, 'bitstamp')
	return ($mtgoxT < 0 && $bitstampT < 0 && $bitstampT < $mtgoxT);
}

function isPriceConvergance($trade)
{
	$mtgoxT = getTradeTrend($trade, 'mtgox');
	$bitstampT = getTradeTrend($trade, 'bitstamp')
	return ($mtgoxT < 0 && $bitstampT > 0);
}

function isPriceDivergance($trade)
{
	$mtgoxT = getTradeTrend($trade, 'mtgox');
	$bitstampT = getTradeTrend($trade, 'bitstamp')
	return ($mtgoxT > 0 && $bitstampT < 0);
}

*/
 
?>
