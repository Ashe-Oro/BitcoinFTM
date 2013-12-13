<?php
include_once("orderbook.php");
class BTCeBTCUSDOrderBook {
	
	const TICKER = "https://btc-e.com/api/3/depth/btc_usd";

	public function getOrderbook($maxVolume) {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);
		$btc_usd = $obj->{'btc_usd'};

		$bids = $btc_usd->{'bids'};
		$asks = $btc_usd->{'asks'};
		$bidStr = "";
		$asksStr = "";

		$bidStr = $this->getBidAskInfo($maxVolume, $bids);
		$asksStr = $this->getBidAskInfo($maxVolume, $asks);

		$orderbook = new Orderbook(time(), "'" . $bidStr . "'", "'". $asksStr . "'");

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