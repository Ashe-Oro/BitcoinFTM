<?php
include_once("orderbook.php");
class BitFinexOrderBook {
	
	const TICKER = "https://api.bitfinex.com/v1/book/btcusd/";

	public function getOrderbook($maxVolume) {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		$bids = $obj->{'bids'};
		$asks = $obj->{'asks'};
		$bidStr = "";
		$asksStr = "";

		$bidStr = $this->getBidAskInfo($maxVolume, $bids);
		$asksStr = $this->getBidAskInfo($maxVolume, $asks);

		$orderbook = new Orderbook($bids[0]->{'timestamp'}, "'" . $bidStr . "'", "'". $asksStr . "'");

		return $orderbook->getOrderbook();

	}

	public function getBidAskInfo($maxVolume, $bidsOrAsks) {

		$currentVolume = 0;
		$counter = 0;
		$retStr = "";

		while($currentVolume < $maxVolume){
			$retStr = $retStr . "(" . $bidsOrAsks[$counter]->{'price'} . "," . $bidsOrAsks[$counter]->{'amount'}. ")";
			
			$currentVolume += $bidsOrAsks[$counter]->{'amount'};
			if($currentVolume < $maxVolume) {
				$retStr = $retStr . ",";
			}
		}

		return $retStr;

	}
}
?>