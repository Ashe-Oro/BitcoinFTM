<?php
include_once("./classes/mtgox.php");
include_once("./classes/bitstamp.php");
include("database_util.php");
class ExchangeDbUtil {
	
	const EXCHANGE_BITSTAMP = "bitstamp";
	const EXCHANGE_MTGOX = "mtgox";
	const HISTORY_SUFFIX = "_history";
	const HISTORY_DAYS_SUFFIX = "_history_days";
	const HISTORY_WEEKS_SUFFIX = "_history_weeks";
	const HISTORY_BIWEEKS_SUFFIX = "_history_biweeks";
	const HISTORY_MONTHS_SUFFIX = "_history_months";

	//TODO Need to write other functions to write to other tables
	//NOT sure if this needs to be done live OR run as aggregate (in which case the history table should hold more data)

	//Adds a "Now" entry to the passed in exchange's DB history table	
	public function addToHistory($xchg) {

		$db = new Database("127.0.0.1", "root", "root", "ftm");	

		$ticker = "";
		if($xchg == self::EXCHANGE_MTGOX) {
			$ticker = $this->getMtGoxTicker();
		}
		elseif($xchg == self::EXCHANGE_BITSTAMP) {
			$ticker = $this->getBitstampTicker();
		}

		$query = "INSERT INTO {$xchg}_history VALUES ({$ticker->{'timestamp'}}, {$ticker->{'last'}}, {$ticker->{'volume'}})";

		$db->query($query);

		$db->close();

		//TODO Removve the return statment as its not needed.  Can replace this with logging
		return "<br/>Query Executed: " . $query;	
	}

	private function getMtGoxTicker() {
		$mtgox = new MtGox();
		$ticker = $mtgox->getTicker();

		return $ticker;
	}

	private function getBitstampTicker() {
		$bitstamp = new BitStamp();
		$ticker = $bitstamp->getTicker();

		return $ticker;
	}

}
?>