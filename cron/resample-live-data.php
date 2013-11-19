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

if (isset($_POST['xchg'])) {
	$xchg = $_POST['xchg'];
}

if (isset($_POST['scale'])) {
	$scale = $_POST['scale'];
}

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

if( (isset($_GET['xchg']) || $xchg != null) && (isset($_GET['scale']) || $scale != null ) ) {
	echo "<br /><br />Building HIstory with " . $xchg . " scale: " . $scale . " start: " . $start . " end: " . $end . "<br /><br/>";
	$exchangeDb->buildHistorySamples($xchg, $scale, $start, $end);
}
else {
	echo "<br /><br />FaiLED HIstory with " . $xchg . " scale: " . $scale . " start: " . $start . " end: " . $end . "<br /><br/>";
	echo "Cannot resample data... Missing required parameters!";
}

function subtractTime($time, $scale) {

	$toSubtract;
	switch($scale) {
		case "mins":
			$toSubtract = 60;
			break;
		case "halfhours":
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
		case "months":
			$toSubtract = 2592000;
			break;
	}

	return $time - $toSubtract;
}

?>