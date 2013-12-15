<?php
include_once("orderbook.php");
class KrakenBTCUSDOrderBook {
	
	const SERVER_TIME_URL = "https://api.kraken.com/0/public/Time";

	public function getOrderbook($maxVolume) {

        $url = "https://api.kraken.com";
        $version = 0;
        $method = "Depth";
        //I don't understand where this pair comes from, but do know it is for BTC USD
        $request = array('pair' => 'XXBTZUSD');
        $curl = curl_init();

        // build the POST data string
        $postdata = http_build_query($request, '', '&');
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Kraken PHP API Agent',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true)
        );
        // make request
        curl_setopt($curl, CURLOPT_URL, $url . '/' . $version . '/public/' . $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array());
        //curl_exec($curl);
        $result = curl_exec($curl);

        if($result===false){
            echo "CURL error: " . curl_error($curl);
            return false;
        }
        // decode results
        $result = json_decode($result, true);
        if(!is_array($result)){
            echo "JSON decode error";
            return false;
        }

		$result = $result['result']['XXBTZUSD'];

		$bids = $result['bids'];
		$asks = $result['asks'];
		$bidStr = "";
		$asksStr = "";

		$bidStr = $this->getBidAskInfo($maxVolume, $bids);
		$asksStr = $this->getBidAskInfo($maxVolume, $asks);

		$orderbook = new Orderbook($this->getServerTime(), "'" . $bidStr . "'", "'". $asksStr . "'");

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

	function getServerTime() {
        $json = file_get_contents(self::SERVER_TIME_URL);
        $obj = json_decode($json);
        $timestamp = $obj->{'result'}->{'unixtime'};
        return $timestamp;
    }
}
?>