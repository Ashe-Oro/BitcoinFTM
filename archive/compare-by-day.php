<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include("utils/database_util.php");

$compare = 'high';

if (isset($_GET['compare'])) {
	$compare = $_GET['compare'];
}

$scale = 'days';

if (isset($_GET['scale'])) {
	$scale = $_GET['scale'];
}
?>

<div style="display: block; float: left;">
<form method="get">
<label for="compare">Choose a field to compare:</label>
<select id="compare" name="compare">
<option value="high"<?php if ($compare == 'high') { ?> selected="selected"<?php } ?>>High</option>
<option value="low"<?php if ($compare == 'low') { ?> selected="selected"<?php } ?>>Low</option>
<option value="avg"<?php if ($compare == 'avg') { ?> selected="selected"<?php } ?>>Avg</option>
<option value="last"<?php if ($compare == 'last') { ?> selected="selected"<?php } ?>>Last</option>
<option value="total"<?php if ($compare == 'total') { ?> selected="selected"<?php } ?>>Total</option>
<option value="volume"<?php if ($compare == 'volume') { ?> selected="selected"<?php } ?>>Volume</option>
<option value="avgvolume"<?php if ($compare == 'avgvolume') { ?> selected="selected"<?php } ?>>Avg Volume</option>
<option value="count"<?php if ($compare == 'count') { ?> selected="selected"<?php } ?>>Count</option>
</select>

<label for="scale">Choose a time scale:</label>
<select id="scale" name="scale">
<option value="days"<?php if ($scale == 'days') { ?> selected="selected"<?php } ?>>Days</option>
<option value="weeks"<?php if ($scale == 'weeks') { ?> selected="selected"<?php } ?>>Weeks</option>
<option value="biweeks"<?php if ($scale == 'biweeks') { ?> selected="selected"<?php } ?>>BiWeeks</option>
<option value="months"<?php if ($scale == 'months') { ?> selected="selected"<?php } ?>>Months</option>
</select>
<input type="submit" value="Update" />
</form>
</div>


<?php

$db = new Database("127.0.0.1", "root", "root", "ftm");

$bQuery = "SELECT * FROM bitstamp_history_{$scale} ORDER BY timestamp ASC";
//echo $bQuery;

$bResult = $db->query($bQuery);

$bArray = array();
while($row = $db->fetch_array_assoc($bResult)){
	array_push($bArray, $row);
}

$mQuery = "SELECT * FROM mtgox_history_{$scale} ORDER BY timestamp ASC";
//echo $mQuery;
$mResult = $db->query($mQuery);

$mArray = array();
while($row = $db->fetch_array_assoc($mResult)){
	array_push($mArray, $row);
}

//echo count($bArray)." ".count($mArray);

if (count($bArray) == count($mArray)) {
	echo '<table width="800" cellpadding="2" cellspacing="2" border="1">';
	echo "<tr><th>{$scale}</th><th>Bitstamp {$compare}</th><th>MtGox {$compare}</th><th>MtGox-Bitstamp delta {$compare}</th></tr>";
	for ($i = 0; $i < count($bArray); $i++){
		$bH = $bArray[$i][$compare];
		$mH = $mArray[$i][$compare];
		$dH = $mH - $bH;
		
		if ($bH > 0 && $mH > 0) {
		
			echo "<tr>";
			echo "<td>".date('d-m-Y', $bArray[$i]['timestamp'])."</td>";
			echo "<td>{$bH}</td>";
			echo "<td>{$mH}</td>";
			echo "<td>{$dH}</td>";
			echo "</tr>";
		}
	}
	echo '</table>';
}


?>