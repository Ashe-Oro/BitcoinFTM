<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$xchg = "mtgox";
if (isset($_GET['xchg'])) {
	$xchg = $_GET['xchg'];
}

$scale = "days";
if (isset($_GET['scale'])) {
	$scale = $_GET['scale'];
}

$start = "1-7-2013";
$end = "20-10-2013";


buildHistorySamples($xchg, $scale, $start, $end);

function buildHistorySamples($xchg, $scale, $start, $end)
{
	$mysql = mysql_connect('localhost', 'root', 'root');
	if (!$mysql) {
		die('Not connected : ' . mysql_error());
	}
	
	$db_selected = mysql_select_db('ftm', $mysql);
	if (!$db_selected) {
		die ('Can\'t use ftm : ' . mysql_error());
	}
	

	$startDate = date_parse($start);
	$endDate = date_parse($end);
	
	$month = $startDate['month'];
	$day = $startDate['day'];
	$year = $startDate['year'];
	
	$endMonth = $endDate['month'];
	$endDay = $endDate['day'];
	$endYear = $endDate['year'];
	
	$count = 0;
	
	$s = "{$day}-{$month}-{$year} 00:00:00";
	$e = "{$endDay}-{$endMonth}-{$endYear} 00:00:00";
	$startTime = strtotime($s);
	$endTime = strtotime($e);
	
	
	$query = "SELECT * FROM {$xchg}_history_{$scale} WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
	echo $query;
	$result = mysql_query($query);
	$samples = array();
	while($row = mysql_fetch_assoc($result)){
		$samples[$row['timestamp']] = $row;
	}
	
	while($month <= $endMonth && $year <= $endYear) {
		$daysInMonth = 31;
		if ($month == 2) {
			$daysInMonth = ($year % 4 == 0) ? 29: 28;
		} else if ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
			$daysInMonth = 30;
		}
		$dayInc = 1;
		if ($scale == 'weeks') { $dayInc = 7; }
		if ($scale == 'biweeks') { $dayInc = 14; }
		if ($scale == 'months') { $dayInc = $daysInMonth; }
		
		
		while ($day <= $daysInMonth && !($day > $endDay && $month == $endMonth && $year == $endYear)) {
			$dateStr = "{$day}-{$month}-{$year} 00:00:00";
			echo $dateStr;
		
			$nextDay = $day+max($dayInc-1, 1);
			$nextMonth = $month;
			$nextYear = $year;
			if ($nextDay > $daysInMonth) {
				$nextDay -= $daysInMonth;
				$nextMonth++;
				if ($nextMonth > 12) {
					$nextMonth = 1;
					$nextYear++;
				}
			} 
			
			$dateStr2 = "{$nextDay}-{$nextMonth}-{$nextYear} 23:59:59";
			echo $dateStr2;
				
			$startTime = strtotime($dateStr);
			$endTime = strtotime($dateStr2);
			
			$query = "SELECT * FROM {$xchg}_history WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
			//echo $query;
			$result = mysql_query($query);
			$candle = getSampleCandleValues($startTime, $result);
			//var_dump($candle);
			echo "<p>Trades for {$day}-{$month}-{$year} [{$startTime}]: {$candle['count']}<br />";
			echo "High: {$candle['high']} Low: {$candle['low']} Avg: {$candle['avg']} Open: {$candle['open']} Close: {$candle['close']} Avg: {$candle['avg']} Total: {$candle['total']} Volume: {$candle['volume']} AvgVolume: {$candle['avgvolume']}</p>";
			
			if (isset($samples[$candle['timestamp']])) {
				$query = "UPDATE {$xchg}_history_{$scale} VALUES ({$candle['timestamp']}, {$candle['high']}, {$candle['low']}, {$candle['avg']}, {$candle['open']}, {$candle['close']}, {$candle['total']}, {$candle['volume']}, {$candle['avgvolume']}, {$candle['count']})";
			} else {
				$query = "INSERT INTO {$xchg}_history_{$scale} VALUES ({$candle['timestamp']}, {$candle['high']}, {$candle['low']}, {$candle['avg']}, {$candle['open']}, {$candle['close']}, {$candle['total']}, {$candle['volume']}, {$candle['avgvolume']}, {$candle['count']})";
			}
			echo '<p>'.$query.'</p>';
			
			mysql_query($query);
			
			$day += $dayInc;
		}
		
		$day = max($daysInMonth-$day, 1);
		$month++;
		if ($month > 12) {
			$month = 1;
			$year++;
		}
	}
	
	mysql_close($mysql);
	
}

function getSampleCandleValues($timestamp, $result)
{
	$candle = array('timestamp' => $timestamp, 'high' => -1, 'low' => -1, 'open' => -1, 'close' => -1, 'total' => 0, 'volume' => 0, 'avgvolume' => 0, 'count' => 0);
	
	$candle['count'] = mysql_num_rows($result);
	
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
		if ($i == 0) {
			$candle['open'] = round($row['price'], 2);
		}
		if ($row['price'] > $candle['high'] || $candle['high'] == -1) {
			$candle['high'] = round($row['price'], 2);
		}
		if ($row['price'] < $candle['low'] || $candle['low'] == -1) {
			$candle['low'] = round($row['price'], 2);
		}
		$candle['total'] += round(round($row['price'], 2) * round($row['volume'], 4), 4);
		$candle['volume'] += round($row['volume'], 4);
		$i++;
		if ($i == $candle['count']){
			$candle['close'] = round($row['price'], 2);
		}
	}
	
	if ($candle['volume'] != 0) {
		$candle['avg'] = round($candle['total'] / $candle['volume'], 2);
		$candle['avgvolume'] = round($candle['volume'] / $candle['count'], 4);
	} else {
		$candle['avg'] = 0;
		$candle['avgvolume'] = 0;
	}
	
	return $candle;
}

?>