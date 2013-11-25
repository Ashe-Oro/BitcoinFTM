<?php
include_once("orderbook.php");
class MtGoxOrderBook {
	
	const TICKER = "http://data.mtgox.com/api/2/BTCUSD/money/depth";

	public function getOrderbook($maxVolume) {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		$result = $obj->{'result'};
		if($result == "success"){

			$data = $obj->{'data'};
			$bids = $data->{'bids'};
			$asks = $data->{'asks'};

			$bidStr = "";
			$asksStr = "";

			$bidStr = $this->getBidAskInfo($maxVolume, $bids);
			$asksStr = $this->getBidAskInfo($maxVolume, $asks);

			$orderbook = new Orderbook($data->{'now'}, "'" . $bidStr . "'", "'". $asksStr . "'");

			return $orderbook->getOrderbook();

		}
		else {
			echo "COULD NOT GET MTGOX ORDERBOOKS!!!";
		}
	}

	public function getBidAskInfo($maxVolume, $bidsOrAsks) {

		$currentVolume = 0;
		$counter = 0;
		$retStr = "";

		while($currentVolume < $maxVolume){
			$retStr = $retStr . "(" . $bidsOrAsks[$counter]->{'price'} . "," . $bidsOrAsks[$counter]->{'amount'} . ")";
			
			$currentVolume += $bidsOrAsks[$counter]->{'amount'};
			if($currentVolume < $maxVolume) {
				$retStr = $retStr . ",";
			}
		}

		return $retStr;

	}
}
?>