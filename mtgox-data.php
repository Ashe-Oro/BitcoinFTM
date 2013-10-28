<h2>Getting Data From MtGox API...</h2>
<?php

$json = file_get_contents('http://data.mtgox.com/api/1/BTCUSD/ticker');
$obj = json_decode($json);

//get the result object
$result = $obj->{'result'};
if($result == "success"){
	//get the data
	$data = $obj->{'return'};
	
	//TODO!!! Use LOOPs to get this info!!!
	//parse the high data
	$high = $data->{'high'};
	$high_value = $high->{'value'};
	$high_value_int = $high->{'value_int'};
	$high_display = $high->{'display'};
	$high_currency = $high->{'currency'};
	printValues("High", $high_value, $high_value_int, $high_display, $high_currency);

	//parse the low data
	$low = $data->{'low'};
	$low_value = $low->{'value'};
	$low_value_int = $low->{'value_int'};
	$low_display = $low->{'display'};
	$low_currency = $low->{'currency'};
	printValues("Low", $low_value, $low_value_int, $low_display, $low_currency);

	//parse the avg data
	$avg = $data->{'avg'};
	$avg_value = $avg->{'value'};
	$avg_value_int = $avg->{'value_int'};
	$avg_display = $avg->{'display'};
	$avg_currency = $avg->{'currency'};
	printValues("Average", $avg_value, $avg_value_int, $avg_display, $avg_currency);

	//parse the vwap data
	$vwap = $data->{'vwap'};
	$vwap_value = $vwap->{'value'};
	$vwap_value_int = $vwap->{'value_int'};
	$vwap_display = $vwap->{'display'};
	$vwap_currency = $vwap->{'currency'};
	printValues("VWAP", $vwap_value, $vwap_value_int, $vwap_display, $vwap_currency);

	//parse the volume data
	$vol = $data->{'vol'};
	$vol_value = $vol->{'value'};
	$vol_value_int = $vol->{'value_int'};
	$vol_display = $vol->{'display'};
	$vol_currency = $vol->{'currency'};
	printValues("Volume", $vol_value, $vol_value_int, $vol_display, $vol_currency);

	//parse the last all data
	$last_all = $data->{'last_all'};
	$last_all_value = $last_all->{'value'};
	$last_all_value_int = $last_all->{'value_int'};
	$last_all_display = $last_all->{'display'};
	$last_all_currency = $last_all->{'currency'};
	printValues("Last All", $last_all_value, $last_all_value_int, $last_all_display, $last_all_currency);

	//parse the last local data
	$last_local = $data->{'last_local'};
	$last_local_value = $last_local->{'value'};
	$last_local_value_int = $last_local->{'value_int'};
	$last_local_display = $last_local->{'display'};
	$last_local_currency = $last_local->{'currency'};
	printValues("Last Local", $last_local_value, $last_local_value_int, $last_local_display, $last_local_currency);

	//parse the last orig data
	$last_orig = $data->{'last_orig'};
	$last_orig_value = $last_orig->{'value'};
	$last_orig_value_int = $last_orig->{'value_int'};
	$last_orig_display = $last_orig->{'display'};
	$last_orig_currency = $last_orig->{'currency'};
	printValues("Last Orig", $last_orig_value, $last_orig_value_int, $last_orig_display, $last_orig_currency);

	//parse the last data
	$last = $data->{'last'};
	$last_value = $last->{'value'};
	$last_value_int = $last->{'value_int'};
	$last_display = $last->{'display'};
	$last_currency = $last->{'currency'};
	printValues("LAST", $last_value, $last_value_int, $last_display, $last_currency);

	//parse the buy data
	$buy = $data->{'buy'};
	$buy_value = $buy->{'value'};
	$buy_value_int = $buy->{'value_int'};
	$buy_display = $buy->{'display'};
	$buy_currency = $buy->{'currency'};
	printValues("BUY", $buy_value, $buy_value_int, $buy_display, $buy_currency);

	//parse the sell data
	$sell = $data->{'sell'};
	$sell_value = $sell->{'value'};
	$sell_value_int = $sell->{'value_int'};
	$sell_display = $sell->{'display'};
	$sell_currency = $sell->{'currency'};
	printValues("SELL", $sell_value, $sell_value_int, $sell_display, $sell_currency);
}
else {
	echo "Something Bad Happened and we can't get the data... Sorry :(";
}

function printValues($typeVal, $val, $intVal, $display, $currency) {
	echo "<b>";
	echo $typeVal;
	echo " Values:</b> ";
	echo "<br/>Value: ";
	echo $val;
	echo "<br/>Int Val: ";
	echo $intVal;
	echo "<br/>Display Val: ";
	echo $display;
	echo "<br/>Currency: ";
	echo $currency;
	echo "<br/><br/>";
}

?>