<?php
require_once("./core/public_markets/market.php");

abstract class HistoryMarket extends Market
{
	protected $timestamp = 0;
	protected $period = 0;
	
	public function __construct($currency)
	{
		parent::__construct($currency);
		$this->live = false;
		$this->updateRate = 0; // always update from DB
	}
	
	public function updateTimestamp($timestamp, $period)
	{
		$this->timestamp = $timestamp;
		$this->period = $period;
	}
	
	protected function getPeriodTable()
	{
		switch($this->period) {
			// use half-hourly table
			case PERIOD_30M:
			{
				return "history_half_hours";
			}
			
			// use hourly table
			case PERIOD_1H:
			case PERIOD_2H:
			case PERIOD_4H:
			case PERIOD_6H:
			case PERIOD_12H:
			{
				return "history_hours";
			}
			
			// use daily table
			case PERIOD_1D:
			case PERIOD_3D:
			{
				return "history_days";
			}
			
			// use weekly table
			case PERIOD_1W:
			{
				return "history_weeks";
			}
			
			// use granular table
			default:
			{
				return "ticker";
			}
		}
	}
}
?>