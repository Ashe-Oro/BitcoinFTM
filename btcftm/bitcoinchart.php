<?php
class BitcoinChart {
	const BITCOINCHART_URL = "http://bitcoincharts.com/charts/chart.png?";

	public $defaults = array(
		'width' => 640,
		'm' => 'mtgoxUSD',
		'submitButton' => 'Draw',
		'r' => 60,
		'i' => '',
		'c' => 0,
		's' => '',
		'e' => '',
		'Prev' => '',
		'Next' => '',
		't' => 'S',
		'b' => '',
		'a1' => '',
		'm1' => 10,
		'a2' => '',
		'm2' => 25,
		'x' => 0,
		'i1' => '',
		'i2' => '',
		'i3' => '',
		'i4' => '',
		'v' => 1,
		'cv' => 0,
		'ps' => 0,
		'l' => 0,
		'p' => 0
	);
	public $settings = array();

	public function __construct($options=NULL)
	{
		if ($options){
			$this->settings = array_merge($this->defaults, $options);
		} else {
			$this->settings = $this->defaults;
		}
	}

	public function draw()
	{
		echo $this->getImage();
	}

	public function getImage()
	{
		$chart = $this->getImageURL();
		return "<img src='{$chart}' />";
	}

	public function getImageURL()
	{
		$url = BitcoinChart::BITCOINCHART_URL;
		$query = http_build_query($this->settings);
		return $url.$query.'&';
	}

	public function set($var, $val)
	{
		$this->settings[$var] = $val;
	}

	public function setSettings($options)
	{
		if ($options){
			$this->settings = array_merge($this->settings, $options);
		}
	}

	public function getDefaults()
	{
		return $this->defaults;
	}
}
?>