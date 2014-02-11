<?php
require_once("historymarket.php");

class HistoryCampBXUSD extends HistoryMarket
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->orderBook = new MarketOrderBook();
		$this->table = "campbx_btcusd";
		$this->depthUrl = "http://campbx.com/api/xdepth.php";
		$this->tickerUrl = "http://campbx.com/api/xticker.php";
	}

	public function parseDepthJson($res)
	{
		$data = json_decode($res);
		$data->asks = $data->Asks;
		$data->bids = $data->Bids;
		return $data;
	}
	
	public function parseTickerRow($row){
		//	var_dump($row);
		$row['volume'] = 0;
		$row['high'] = $row['last'];
		$row['low'] = $row['last'];
		return $row;
	}
}

?>