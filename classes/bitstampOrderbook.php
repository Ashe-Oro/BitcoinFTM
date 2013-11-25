<?php
include_once("orderbook.php");
class BitStampOrderBook {
	
	const TICKER = "https://www.bitstamp.net/api/order_book/";

	public function getOrderbook($maxVolume) {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		$bids = $obj->{'bids'};
		$asks = $obj->{'asks'};
		$bidStr = "";
		$asksStr = "";

		$bidStr = $this->getBidAskInfo($maxVolume, $bids);
		$asksStr = $this->getBidAskInfo($maxVolume, $asks);

		$orderbook = new Orderbook($obj->{'timestamp'}, "'" . $bidStr . "'", "'". $asksStr . "'");

		return $orderbook->getOrderbook();

	}

	public function getBidAskInfo($maxVolume, $bidsOrAsks) {

		$currentVolume = 0;
		$counter = 0;
		$retStr = "";

		while($currentVolume < $maxVolume){
			$retStr = $retStr . "(" . $bidsOrAsks[$counter][0] . "," . $bidsOrAsks[$counter][1] . ")";
			
			$currentVolume += $bidsOrAsks[$counter][1];
			if($currentVolume < $maxVolume) {
				$retStr = $retStr . ",";
			}
		}

		return $retStr;

	}
}
?>