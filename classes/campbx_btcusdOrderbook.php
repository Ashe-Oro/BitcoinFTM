<?php
include_once("orderbook.php");
class CampBXBTCUSDOrderBook {
	
	const TICKER = "http://campbx.com/api/xdepth.php";

	public function getOrderbook($maxVolume) {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		$bids = $obj->{'Bids'};
		$asks = $obj->{'Asks'};
		$bidStr = "";
		$asksStr = "";

		$bidStr = $this->getBidAskInfo($maxVolume, $bids);
		$asksStr = $this->getBidAskInfo($maxVolume, $asks);

		$now = time();
		$orderbook = new Orderbook($now, "'" . $bidStr . "'", "'". $asksStr . "'");

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