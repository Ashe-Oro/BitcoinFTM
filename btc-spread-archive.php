<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings = array();

$settings['scale'] = "days";
$settings['start'] = "1-7-2013";
$settings['end'] = "20-10-2013";
$settings['capital'] = 1;
$settings['min'] = 4;
$settings['ds'] = 5;


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

doBtcSpreadTrades($settings);


function doBtcSpreadTrades($settings)
{
	$mysql = mysql_connect('localhost', 'root', 'root');
	if (!$mysql) {
		die('Not connected : ' . mysql_error());
	}
	
	$db_selected = mysql_select_db('ftm', $mysql);
	if (!$db_selected) {
		die ('Can\'t use ftm : ' . mysql_error());
	}

	$startDate = date_parse($settings['start']);
	$endDate = date_parse($settings['end']);
	
	$month = $startDate['month'];
	$day = $startDate['day'];
	$year = $startDate['year'];
	
	$endMonth = $endDate['month'];
	$endDay = $endDate['day'];
	$endYear = $endDate['year'];
	
	$s = "{$day}-{$month}-{$year} 00:00:00";
	$e = "{$endDay}-{$endMonth}-{$endYear} 00:00:00";
	$startTime = strtotime($s);
	$endTime = strtotime($e);
	
	#sorting through historical data by time stamp and storing in to array
	$xchg = 'mtgox';
	$query = "SELECT * FROM {$xchg}_history_{$settings['scale']} WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
	$result = mysql_query($query);
	$mtgox = array();
	while($row = mysql_fetch_assoc($result)){
		$mtgox[$row['timestamp']] = $row;
	}
	
	#sorting through historical data by time stamp and storing in to array
	$xchg = 'bitstamp';
	$query = "SELECT * FROM {$xchg}_history_{$settings['scale']} WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
	$result = mysql_query($query);
	$bitstamp = array();
	while($row = mysql_fetch_assoc($result)){
		$bitstamp[$row['timestamp']] = $row;
	}
	
    #defining a trade and its characteristics
	$trade = array(
		'capital' => $settings['capital'], 
		'status' => 'CLOSED', 
		'open' => 0, 
		'close' => 0, 
		'openTotal' => 0,
		'closeTotal' => 0,
		'openDelta' => 0,
		'closeDelta' => 0,
		'openMtGox' => NULL,
		'openBitstamp' => NULL,
		'closeMtGox' => NULL,
		'closeBitstamp' => NULL,
		'profit' => 0
	);
	
    #comparing each row of timestamp at each exhange
	if (count($mtgox) == count($bitstamp)) {
        #what is $ts and $m?
		foreach($mtgox as $ts => $m) {
			if (isset($bitstamp[$ts])){
				$b = $bitstamp[$ts];
                #spread at certain historical time
                $d = getTradeSpread($m, $b);
				echo date('d M Y', $ts).': '.$d.'<br />';
				
                #if $d == -1 then  
				if ($d != -1) {
					if ($trade['status'] == 'CLOSED' && $trade['open'] == 0 && $d >= $settings['min']) {
						$trade = openTrade($trade, $d, $m, $b); 
						
						echo "<p><b>TRADE OPENED!</b><br/>\n";
						echo "<b>Date:</b> ".date('d M Y', $trade['openTimestamp'])."<br />\n";
						echo "<b>Open Spread:</b>".$d."<br />\n";
						echo "<b>MtGox Value:</b>".$m['avg']."<br />\n";
						echo "<b>Bitstamp Value:</b>".$b['avg']."<br />\n";
						echo "</p>\n";
					} else if ($trade['status'] == 'CLOSED'){
						if ($d >= $settings['min'] || $d > $settings['ds']) {
							$trade = openTrade($trade, $d, $m, $b);
							
							echo "<p><b>TRADE OPENED!</b><br/>\n";
							echo "<b>Date:</b> ".date('d M Y', $trade['openTimestamp'])."<br />\n";
							echo "<b>Open Spread:</b>".$d."<br />\n";
							echo "<b>MtGox Value:</b>".$m['avg']."<br />\n";
							echo "<b>Bitstamp Value:</b>".$b['avg']."<br />\n";
							echo "</p>\n";
							
						} 
					} else if ($trade['status'] == 'OPEN') {
						if ($settings['ds'] >= $trade['open']-$d) {
							$trade = closeTrade($trade, $d, $m, $b);
							
							echo "<p><b>TRADE CLOSED!</b><br/>\n";
							echo "<b>Date:</b> ".date('d M Y', $trade['closeTimestamp'])."<br />\n";
							echo "<b>Close Spread:</b>".$d."<br />\n";
							echo "<b>MtGox Value:</b>".$m['avg']."<br />\n";
							echo "<b>Bitstamp Value:</b>".$b['avg']."<br />\n";
							echo "</p>\n";
						}
					}
				}
			}
		}
	}
	mysql_close($mysql);
}

function openTrade($trade, $d, $mtgox, $bitstamp)
{
	$trade['status'] = 'OPEN';
	
	$trade['open'] = $d;
	$trade['openTotal'] = $trade['capital'] * $d;
	$trade['openDelta'] = getTradeSpread($mtgox, $bitstamp);
	$trade['openmtgox'] = $mtgox;
	$trade['openbitstamp'] = $bitstamp;
	$trade['openTimestamp'] = $mtgox['timestamp'];
	
	$trade['close'] = 0;
	$trade['closeDelta'] = 0;
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
	$trade['closeDelta'] = getTradeSpread($mtgox, $bitstamp);
	$trade['closemtgox'] = $mtgox;
	$trade['closebitstamp'] = $bitstamp;
	$trade['closeTimestamp'] = $mtgox['timestamp'];
	
	return $trade;
}

/*

function openOrCloseTrade($trade)
{
	return ($trade['status'] == 'OPEN') ? 'CLOSED': 'OPEN';
}

function getTradeProfit($trade)
{
	return ($trade['openMtGox']-$trade['closeMtGox']) - ($trade['openBitstamp'] - $trade['closeBitstamp']);
}

function getTotalTradeProfit($trade)
{	
	return $trade['capital'] * getTradeProfit($trade);
}

function getTradeTrend($trade, $xchg)
{
	return $trade["open{$xchg}"]['avg'] - $trade["close{$xchg}]['avg']";
}
*/
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

function getMtGoxPerc()
{
}

function getBitstampPerc()
{
}
?>
