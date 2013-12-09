<?php
include_once("ticker.php");
class BtceBTCUSD {
	
	const TICKER = "https://btc-e.com/api/3/ticker/btc_usd";

	public function getTicker() {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		//get the data
		$data = $obj->{'btc_usd'};

		$ticker = new Ticker($data->{'high'}, $data->{'low'}, $data->{'last'}, $data->{'updated'}, $data->{'buy'}, $data->{'vol'}, $data->{'sell'}, $data->{'avg'});

		return $ticker->getTicker();

	}

}
?>