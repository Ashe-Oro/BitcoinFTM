<?php
include_once("ticker.php");
class BitStamp {
	
	const TICKER = "https://www.bitstamp.net/api/ticker/";

	public function getTicker() {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		$ticker = new Ticker($obj->{'high'}, $obj->{'low'}, $obj->{'last'}, $obj->{'timestamp'}, $obj->{'bid'}, $obj->{'volume'}, $obj->{'ask'});

		return $ticker->getTicker();

	}

}
?>