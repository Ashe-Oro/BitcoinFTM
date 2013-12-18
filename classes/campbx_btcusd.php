<?php
include_once("ticker.php");
class CampBXBTCUSD {
	
	const TICKER = "http://campbx.com/api/xticker.php";

	public function getTicker() {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);
		$now = time();
		
		$ticker = new Ticker(null, null, $obj->{'Last Trade'}, $now, $obj->{'Best Bid'}, null, $obj->{'Best Ask'});

		return $ticker->getTicker();

	}

}
?>