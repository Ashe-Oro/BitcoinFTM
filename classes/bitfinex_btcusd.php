<?php
include_once("ticker.php");
class BitfinexBTCUSD {
	
	const TICKER = "https://api.bitfinex.com/v1/ticker/btcusd";

	public function getTicker() {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		//0's represent information that is common to tickers that is not represented in Bitfinex data
		$ticker = new Ticker(0, 0, $obj->{'last_price'}, $obj->{'timestamp'}, $obj->{'bid'}, 0, $obj->{'ask'}, $obj->{'mid'});

		return $ticker->getTicker();

	}

}
?>