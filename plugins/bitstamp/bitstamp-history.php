<?php
header('Content-type: application/json');
$xchg = "bitstamp";

$scale = "days";
if (isset($_GET['scale'])) {
	$scale = $_GET['scale'];
}


$start = "01-07-2013";
if (isset($_GET['startDate'])) {
	$start = $_GET['startDate'];
}

$end = "01-20-2013";
if (isset($_GET['endDate'])) {
	$end = $_GET['endDate'];
}

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
$result = mysql_query($query);
$samples = array();
while($row = mysql_fetch_assoc($result)){
	$samples[$row['timestamp']] = $row;
}

echo json_encode($samples);
?>