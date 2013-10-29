<?php
class BTCeUSD
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 60;
	}

	public function updateDepth()
	{
		$url = 'https://btc-e.com/api/2/btc_usd/depth';
		/**** CONVERT PYTHON THIS TO READ STREAM IN PHP ***/
		//req = urllib.request.Request(url, None, headers={
          //  "Content-Type": "application/x-www-form-urlencoded",
           // "Accept": "*/*",
          //  "User-Agent": "curl/7.24.0 (x86_64-apple-darwin12.0)"})
        //res = urllib.request.urlopen(req)
        //depth = json.loads(res.read().decode('utf8'))
        //self.depth = self.format_depth(depth)
		/***/
	}

	public function sortAndFormat($l, $reverse=false)
	{
		$r = array();
		foreach($l as $i) {
			array_push($r, array('price' => $i[0], 'amount' => $i[1]);
		}
		usort($r, array("BTCeUSD", "comparePrice"));
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