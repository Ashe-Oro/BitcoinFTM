<h2>Getting Data From MtGox API...</h2>
<?php

$item = "";
$now = "";

$json = file_get_contents('http://data.mtgox.com/api/1/BTCUSD/ticker');
$obj = json_decode($json);

//get the result object
$result = $obj->{'result'};
if($result == "success"){
	//get the data
	$data = $obj->{'return'};

	//loop through key value pairs, key is not item or now, then process, otherwise grab the values
	foreach($data as $key => $value) {
		$info_value = "";
		$info_value_int = "";
		$info_display = "";
		$info_currency = "";

		if($key == "item") {
			$item = $value;
		}
		elseif($key == "now") {
			$now = $value;
		}
		else{
			foreach($value as $details_key=>$details_value) {
				if($details_key == "value") 
					$info_value = $details_value;
				if($details_key == "value_int") 
					$info_value_int = $details_value;
				if($details_key == "display")
					$info_display = $details_value;
				if($details_key == "currency")
					$info_currency == $details_value;
			}
			printValues($key, $info_value, $info_value_int, $info_display, $info_currency);
		}
	}
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