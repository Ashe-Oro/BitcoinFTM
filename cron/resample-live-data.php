<h2>Resampling Live data in DB...</h2>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("../utils/ExchangeDbUtil.php");

$exchangeDb = new ExchangeDbUtil();

$xchg;
$scale;
$start;
$end;

//get the now time incase query params aren't passed in
$now = getdate();
$nowFormated = date('Y-m-d H:i:s', strtotime($now['year'] . '-' . $now['mon'] . '-' . $now['mday'] . " " . $now['hours'] . ":" . $now['minutes'] . ":" . $now['seconds']));

//when checking query params, we'll only be doing a POST from the cron job and that will only pass in xchg and scale.  Start and end will be based off of scale and the now time in that case.
if (isset($_POST['xchg'])) {
	$xchg = $_POST['xchg'];
}
elseif (isset($_GET['xchg'])) {
	$xchg = $_GET['xchg'];
}

if (isset($_POST['scale'])) {
	$scale = $_POST['scale'];
}
elseif (isset($_GET['scale'])) {
	$scale = $_GET['scale'];
}

//Start and end will only come through a Get if passed in by query param (and not via cron)  Otherwise we base them off the now time
if (isset($_GET['start'])) {
	$start = $_GET['start'];
}
else {
	$start = date('Y-m-d H:i:s', subtractTime(strtotime($nowFormated), $scale));
}

if (isset($_GET['end'])) {
	$end = $_GET['end'];
}
else {
	$end = date('Y-m-d H:i:s', strtotime($nowFormated));
}

//if start == end date then we're querying a day in the past so we set teh end date to be 1 day ahead of the start date which starts at the 0:00:00 time
if($start == $end) {
	$start = date('Y-m-d H:i:s', strtotime($start));
	$end = strtotime($end) + 86400;
	$end = date('Y-m-d H:i:s', $end);
}

if( (isset($_GET['xchg']) || $xchg != null) && (isset($_GET['scale']) || $scale != null ) ) {
	echo "Building HIstory with " . $xchg . " scale: " . $scale . " start: " . $start . " end: " . $end . "<br/>";
	$exchangeDb->buildHistorySamples($xchg, $scale, $start, $end);
}
else {
	echo "FaiLED HIstory with " . $xchg . " scale: " . $scale . " start: " . $start . " end: " . $end . "<br/>";
	echo "Cannot resample data... Missing required parameters!";
}

function subtractTime($time, $scale) {

	$toSubtract;
	switch($scale) {
		case "mins":
			$toSubtract = 60;
			break;
		case "half_hours":
			$toSubtract = 1800;
			break;
		case "hours":
			$toSubtract = 3600;
			break;
		case "days":
			$toSubtract = 86400;
			break;
		case "weeks":
			$toSubtract = 604800;
			break;
		case "biweeks":
			$toSubtract = 1209600;
			break;
		case "months":
			$toSubtract = 2592000;
			break;
	}

	return $time - $toSubtract;
}

?>