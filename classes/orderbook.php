<?php
class Orderbook {
	private $timestamp;
	private $bids;
	private $asks;

	function __construct($timestamp, $bids, $asks) {
        $this -> timestamp = $timestamp;
        $this -> bid = $bids;
        $this -> ask = $asks;
    }

    public function getOrderbook() {

		$data = (object) array(
			'timestamp' => $this->timestamp,
			'bids'       => $this->bid,
			'asks'       => $this->ask
			);

		return $data;
    }
}
?>