<?php
require_once("market.php");

class BitstampUSD extends Market
{
	public $updateRate;

	public function __construct($market)
	{
		parent::__construct($market);
		$this->updateRate = 20;
	}

	public function updateDepth()
	{
		$url = 'https://www.bitstamp.net/api/order_book/';
/**** REPLACE WITH PHP HEADER AND JSON READ	 ***/	
//		$req =  urllib.request.Request(url, None, headers={
//            "Content-Type": "application/x-www-form-urlencoded",
//            "Accept": "*/*",
//            "User-Agent": "curl/7.24.0 (x86_64-apple-darwin12.0)"})
//        res = urllib.request.urlopen(req)
//        depth = json.loads(res.read().decode('utf8'))

        	$this->depth = $this->formatDepth($depth);
	}

	public function sortAndFormat($l, $reverse)
	{
		$r = array();
		foreach($l as $i) {
			array_push($r, array('price' => $i[0], 'amount' => $i[1]);
		}
		usort($r, array("BitstampUSD", "comparePrice"));
		if ($reverse) {
			$r = array_reverse($r);
		}
		return $r;
	}

	public function formatDepth($depth)
	{
		$bids = $this->sortAndFormat($depth['bids'], true);
		$asks = $this->sortAndFormat($depth['asks'], false);
		return array('asks' => $asks, 'bids' => $bids);
	}
}

?>