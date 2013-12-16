<?php
include_once("ticker.php");
class CryptoTradeBTCUSD {
	
	const TICKER = "https://crypto-trade.com/api/1/ticker/btc_usd";

	public function getTicker() {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);
		$data = $obj->{'data'};
		$now = time();

		$ticker = new Ticker($data->{'high'}, $data->{'low'}, $data->{'last'}, $now, $data->{'max_bid'}, $data->{'vol_usd'}, $data->{'min_ask'});

		return $ticker->getTicker();

	}

}
?>