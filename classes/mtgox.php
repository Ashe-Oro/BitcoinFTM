<?php
include_once("ticker.php");
class MtGox {
	
	const TICKER = "http://data.mtgox.com/api/1/BTCUSD/ticker";

	private $high;
	private $low;
	private $last;
	private $timestamp;
	private $volume;
	//these are bitstamp values... not sure what the mtgox equivilant is
	private $bid = "";
	private $ask = "";

	public function getTicker() {

		$json = file_get_contents(self::TICKER);
		$obj = json_decode($json);

		//get the result object
		$result = $obj->{'result'};
		if($result == "success"){

			//get the data
			$data = $obj->{'return'};

			//loop through key value pairs, key is not item or now, then process, otherwise grab the values
			foreach($data as $key => $value) {

				//TODO we're only getting the obvious values that match the ticker object, but should we get more?  These are what's common with Bitstamp
				if($key == "now") {
					$this->timestamp = $value;
				}
				elseif($key == "item") {
					//DO NOTHING (for now)...
				}
				else{
					foreach($value as $details_key=>$details_value) {
						if($key == "high" && $details_key == "value")
							$this->high = $details_value;
						if($key == "low" && $details_key == "value")
							$this->low = $details_value;
						if($key == "last" && $details_key == "value")
							$this->last = $details_value;
						if($key == "vol" && $details_key == "value")
							$this->volume = $details_value;
					}

				}
			}

			$ticker = new Ticker($this->high, $this->low, $this->last, $this->timestamp, $this->bid, $this->volume, $this->ask);

			return $ticker->getTicker();	
		}
		else {
			echo "Something Bad Happened and we can't get the data... Sorry :(";
		}
	}

}
?>