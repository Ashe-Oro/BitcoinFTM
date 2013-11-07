<?php
include_once("ticker.php");
class BtceLTCBTC {
	
	const TICKER = "https://btc-e.com/api/2/ltc_btc/ticker";

	public function getTicker() {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		//get the data
		$data = $obj->{'ticker'};

		$ticker = new Ticker($data->{'high'}, $data->{'low'}, $data->{'last'}, $data->{'server_time'}, $data->{'buy'}, $data->{'vol'}, $data->{'sell'}, $data->{'avg'});

		return $ticker->getTicker();

	}

}
?>