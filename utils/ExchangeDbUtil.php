<?php
include_once("../classes/mtgox.php");
include_once("../classes/mtgoxOrderbook.php");
include_once("../classes/bitstamp.php");
include_once("../classes/bitstampOrderbook.php");
include_once("../classes/bitfinexOrderbook.php");
include_once("../classes/btce_btcusdOrderbook.php");
include_once("../classes/bitfinex_btcusd.php");
include_once("../classes/bitfinex_ltcbtc.php");
include_once("../classes/btce_ltcbtc.php");
include_once("../classes/btce_btcusd.php");
include("database_util.php");
class ExchangeDbUtil {
	
	const EXCHANGE_BITSTAMP = "bitstamp";
	const EXCHANGE_MTGOX = "mtgox";
	const EXCHANGE_BITFINEX_LTCBTC = "bitfinex_ltcbtc";
	const EXCHANGE_BITFINEX_BTCUSD = "bitfinex_btcusd";
	const EXCHANGE_BTCE_LTCBTC = "btce_ltcbtc";
	const EXCHANGE_BTCE_BTCUSD = "btce_btcusd";
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

	private function getBitfinexLTCBTCTicker() {
		$bitfinex = new BitfinexLTCBTC();
		$ticker = $bitfinex->getTicker();

		return $ticker;
	}

	private function getBitfinexBTCUSDTicker() {
		$bitfinex = new BitfinexBTCUSD();
		$ticker = $bitfinex->getTicker();

		return $ticker;
	}

	private function getBtceBTCUSDTicker() {
		$btce = new BtceBTCUSD();
		$ticker = $btce->getTicker();

		return $ticker;
	}

	private function getBtceLTCBTCTicker() {
		$btce = new BtceLTCBTC();
		$ticker = $btce->getTicker();

		return $ticker;
	}

	private function getMtGoxOrderbook($maxVolume) {
		$mtgox = new MtGoxOrderbook();
		$orderbook = $mtgox->getOrderbook($maxVolume);

		return $orderbook;
	}

	private function getBitstampOrderbook($maxVolume) {
		$bitstamp = new BitStampOrderbook();
		$orderbook = $bitstamp->getOrderbook($maxVolume);

		return $orderbook;
	}

	private function getBTCeBTCUSDOrderbook($maxVolume) {
		$btce = new BTCeBTCUSDOrderBook();
		$orderbook = $btce->getOrderbook($maxVolume);

		return $orderbook;
	}

	private function getBitfinexBTCUSDTOrderbook($maxVolume) {
		$bitfinex = new BitFinexOrderBook();
		$orderbook = $bitfinex->getOrderbook($maxVolume);

		return $orderbook;
	}

	public function addToTicker($xchg) {

		$db = new Database("btcftmpub.db.8986864.hostedresource.com", "btcftmpub", "Wolfpack1!", "btcftmpub");
		//$db = new Database("127.0.0.1", "root", "root", "ftm");	

		$ticker = "";
		$query = "";
		$ret = "";

		if($xchg == self::EXCHANGE_MTGOX) {
			$ticker = $this->getMtGoxTicker();

			if($ticker != null && $ticker->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'high'}}, {$ticker->{'last'}}, {$ticker->{'low'}}, {$ticker->{'volume'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BITSTAMP) {
			$ticker = $this->getBitstampTicker();
			
			if($ticker != null && $ticker->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'high'}}, {$ticker->{'last'}}, {$ticker->{'low'}}, {$ticker->{'volume'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BTCE_BTCUSD) {
			$ticker = $this->getBtceBTCUSDTicker();

			if($ticker != null && $ticker->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'high'}}, {$ticker->{'low'}}, {$ticker->{'mid'}}, {$ticker->{'volume'}}, {$ticker->{'last'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BITFINEX_BTCUSD) {
			$ticker = $this->getBitfinexBTCUSDTicker();
			
			if($ticker != null && $ticker->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'mid'}}, {$ticker->{'last'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BITFINEX_LTCBTC) {
			$ticker = $this->getBitfinexLTCBTCTicker();
			
			if($ticker != null && $ticker->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'mid'}}, {$ticker->{'last'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BTCE_LTCBTC) {
			$ticker = $this->getBtceLTCBTCTicker();
			
			if($ticker != null && $ticker->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_ticker VALUES ({$ticker->{'timestamp'}}, {$ticker->{'high'}}, {$ticker->{'low'}}, {$ticker->{'mid'}}, {$ticker->{'volume'}}, {$ticker->{'last'}}, {$ticker->{'bid'}}, {$ticker->{'ask'}})";
			}
		}

		if($query != ""){
			$db->query($query);
			$ret = "<br/>Query Executed: " . $query;
		}
		else {
			$ret = "<br/>Query NOT Executed: Bad data for Exchange " . $xchg;
		}

		$db->close();

		//TODO Removve the return statment as its not needed.  Can replace this with logging
		return 	$ret;
	}

	public function addToOrderbooks($xchg, $maxVolume) {

		$db = new Database("btcftmpub.db.8986864.hostedresource.com", "btcftmpub", "Wolfpack1!", "btcftmpub");
		//$db = new Database("127.0.0.1", "root", "root", "ftm");	

		$orderbook = "";
		$query = "";
		$ret = "";

		if($xchg == self::EXCHANGE_MTGOX) {
			$orderbook = $this->getMtGoxOrderbook($maxVolume);
			
			if($orderbook != null && $orderbook->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_orderbook VALUES ({$orderbook->{'timestamp'}}, {$orderbook->{'bids'}}, {$orderbook->{'asks'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BITSTAMP) {
			$orderbook = $this->getBitstampOrderbook($maxVolume);
			
			if($orderbook != null && $orderbook->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_orderbook VALUES ({$orderbook->{'timestamp'}}, {$orderbook->{'bids'}}, {$orderbook->{'asks'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BTCE_BTCUSD) {
			$orderbook = $this->getBTCeBTCUSDOrderbook($maxVolume);

			if($orderbook != null && $orderbook->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_orderbook VALUES ({$orderbook->{'timestamp'}}, {$orderbook->{'bids'}}, {$orderbook->{'asks'}})";
			}
		}
		elseif($xchg == self::EXCHANGE_BITFINEX_BTCUSD) {
			$orderbook = $this->getBitfinexBTCUSDTOrderbook($maxVolume);

			if($orderbook != null && $orderbook->{'timestamp'} > 0) {
				$query = "INSERT INTO {$xchg}_orderbook VALUES ({$orderbook->{'timestamp'}}, {$orderbook->{'bids'}}, {$orderbook->{'asks'}})";
			}
		}

		if($query != ""){
			$db->query($query);
			$ret = "<br/>Query Executed: " . $query;
		}
		else {
			$ret = "<br/>Query NOT Executed: Bad data for Exchange " . $xchg;
		}

		$db->close();

		//TODO Removve the return statment as its not needed.  Can replace this with logging
		return 	$ret;
	}

	public function buildHistorySamples($xchg, $scale, $start, $end)
	{
		$db = new Database("btcftmpub.db.8986864.hostedresource.com", "btcftmpub", "Wolfpack1!", "btcftmpub");	
		//$db = new Database("127.0.0.1", "root", "root", "ftm");	
		
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
		$decrement = 0;
		$openPos = 0;
		$lastSuccessfulPos = 0;

		$i = 0;
		
		while($row = mysql_fetch_assoc($result)){
			if($row['last'] > 0) {
				if ($i == $openPos) {
					echo "OPen Position is: " . $openPos;
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
				
				//since we're not certain where the last good row is, we'll alway set close to the last one processed
				$candle['close'] = round($row['last'], 2);
					
			}
			else{
				//if we're in this case, we can't count the row towards the total count
				$decrement++;
				//if we haven't found a successful open position, lets try the next one
				if($i == $openPos) {
					$openPos++;
				}
			}
		}

		//for an accurate count we have to subtract total rows minus the bad rows
		$candle['count'] = $candle['count'] - $decrement;

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