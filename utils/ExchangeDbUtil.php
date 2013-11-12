<?php
include_once("./classes/mtgox.php");
include_once("./classes/bitstamp.php");
include_once("./classes/bitfinex_ltcbtc.php");
include_once("./classes/btce_ltcbtc.php");
include("database_util.php");
class ExchangeDbUtil {
	
	const EXCHANGE_BITSTAMP = "bitstamp";
	const EXCHANGE_MTGOX = "mtgox";
	const EXCHANGE_BITFINEX_LTCBTC = "bitfinex_ltcbtc";
	const EXCHANGE_BTCE_LTCBTC = "btce_ltcbtc";
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

	private function getBitfinexTicker() {
		$bitfinex = new BitfinexLTCBTC();
		$ticker = $bitfinex->getTicker();

		return $ticker;
	}

	private function getBtceLTCBTCTicker() {
		$btce = new BtceLTCBTC();
		$ticker = $btce->getTicker();

		return $ticker;
	}

	public function addToTicker($xchg) {


		$db = new Database("btcftmpub.db.8986864.hostedresource.com", "btcftmpub", "Wolfpack1!", "btcftmpub");	

		$ticker = "";
		$query = "";

		if($xchg == self::EXCHANGE_MTGOX) {
			$ticker = $this->getMtGoxTicker();
			$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'high'}}, {$ticker->{'last'}}, {$ticker->{'low'}}, {$ticker->{'volume'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
		}
		elseif($xchg == self::EXCHANGE_BITSTAMP) {
			$ticker = $this->getBitstampTicker();
			$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'high'}}, {$ticker->{'last'}}, {$ticker->{'low'}}, {$ticker->{'volume'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
		}
		elseif($xchg == self::EXCHANGE_BITFINEX_LTCBTC) {
			$ticker = $this->getBitfinexTicker();
			$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'mid'}}, {$ticker->{'last'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
		}
		elseif($xchg == self::EXCHANGE_BTCE_LTCBTC) {
			$ticker = $this->getBtceLTCBTCTicker();
			$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'high'}}, {$ticker->{'low'}}, {$ticker->{'mid'}}, {$ticker->{'volume'}}, {$ticker->{'last'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
		}

		$db->query($query);

		$db->close();

		//TODO Removve the return statment as its not needed.  Can replace this with logging
		return "<br/>Query Executed: " . $query;	
	}

}
?>