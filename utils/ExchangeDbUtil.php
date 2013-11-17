<?php
include_once("../classes/mtgox.php");
include_once("../classes/bitstamp.php");
include_once("../classes/bitfinex_ltcbtc.php");
include_once("../classes/btce_ltcbtc.php");
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

		public function buildHistorySamples($xchg, $scale, $start, $end)
	{
		$db = new Database("btcftmpub.db.8986864.hostedresource.com", "btcftmpub", "Wolfpack1!", "btcftmpub");	

		$startDate = date_parse($start);
		$endDate = date_parse($end);

		$month = $startDate['month'];
		$day = $startDate['day'];
		$year = $startDate['year'];
		
		$endMonth = $endDate['month'];
		$endDay = $endDate['day'];
		$endYear = $endDate['year'];

		$count = 0;

		$startTime = strtotime($start);
		$endTime = strtotime($end);

		//first we'll get entries from the history to see if it's already been sampled
		$query = "SELECT * FROM {$xchg}_history_{$scale} WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
		
		$result = $db->query($query);
		$samples = array();

		while($row = $db->fetch_array_assoc($result)){
			$samples[$row['timestamp']] = $row;
		}


		//next we get all the data from the ticker history
		$query = "SELECT * FROM {$xchg}_ticker WHERE timestamp > {$startTime} AND timestamp < {$endTime} ORDER BY timestamp ASC";
		echo "<br/><br/>" . $query . "<br/><br/>";
		$result = $db->query($query);
		$candle = $this->getSampleCandleValues($startTime, $result);
		
		//if the timestamp already exists in the samples then we just need to update the values for that record, otherwise we need to create a record for it
		if (isset($samples[$candle['timestamp']])) {
			$query = "UPDATE {$xchg}_history_{$scale} VALUES ({$candle['timestamp']}, {$candle['high']}, {$candle['low']}, {$candle['avg']}, {$candle['open']}, {$candle['close']}, {$candle['total']}, {$candle['volume']}, {$candle['avgvolume']}, {$candle['count']})";
		} else {
			$query = "INSERT INTO {$xchg}_history_{$scale} VALUES ({$candle['timestamp']}, {$candle['high']}, {$candle['low']}, {$candle['avg']}, {$candle['open']}, {$candle['close']}, {$candle['total']}, {$candle['volume']}, {$candle['avgvolume']}, {$candle['count']})";
		}
		
		//only execute the query if we've added a value to the candle, otherwise it's invalid.
		if($candle['high'] != -1 && $candle['low'] != -1 && $candle['count'] > 0) {
			echo "<p>Trades for {$day}-{$month}-{$year} [{$startTime}]: {$candle['count']}<br />";
			echo "High: {$candle['high']} Low: {$candle['low']} Avg: {$candle['avg']} Open: {$candle['open']} Close: {$candle['close']} Avg: {$candle['avg']} Total: {$candle['total']} Volume: {$candle['volume']} AvgVolume: {$candle['avgvolume']}</p>";
		
			$db->query($query);	
			echo '<p>'.$query.'</p>';				
		}
		else {
			echo "Not enough data to execute query.";
		}
		
		$db->close();
	}

	private function getSampleCandleValues($timestamp, $result)
	{
		$candle = array('timestamp' => $timestamp, 'high' => -1, 'low' => -1, 'open' => -1, 'close' => -1, 'total' => 0, 'volume' => 0, 'avgvolume' => 0, 'count' => 0);
		
		$candle['count'] = mysql_num_rows($result);
		
		$i = 0;
		while($row = mysql_fetch_assoc($result)){
			if ($i == 0) {
				$candle['open'] = round($row['last'], 2);
			}
			if ($row['last'] > $candle['high'] || $candle['high'] == -1) {
				$candle['high'] = round($row['last'], 2);
			}
			if ($row['last'] < $candle['low'] || $candle['low'] == -1) {
				$candle['low'] = round($row['last'], 2);
			}
			$candle['total'] += round(round($row['last'], 2) * round($row['volume'], 4), 4);
			$candle['volume'] += round($row['volume'], 4);
			$i++;
			if ($i == $candle['count']){
				$candle['close'] = round($row['last'], 2);
			}
		}
		
		if ($candle['volume'] != 0) {
			$candle['avg'] = round($candle['total'] / $candle['volume'], 2);
			$candle['avgvolume'] = round($candle['volume'] / $candle['count'], 4);
		} else {
			$candle['avg'] = 0;
			$candle['avgvolume'] = 0;
		}
		
		return $candle;
	}

}
?>