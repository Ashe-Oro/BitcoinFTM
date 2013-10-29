<?php
require_once('market.php');

class MtGoxUSD
{
	public function __construct()
	{
		parent::__construct("USD");
		$this->updateRate = 20;
		$this->depth = array('asks' => array('price' => 0, 'amount' => 0), 'bids' => array('price' => 0, 'amount' => 0));
	}

	public function updateDepth()
	{
		/*** CONVERT PYTHON TO READ LIVE MTGOX DATA IN PHP
		res = urllib.request.urlopen(
            'http://data.mtgox.com/api/2/BTCUSD/money/depth')
        jsonstr = res.read().decode('utf8')
        try:
            data = json.loads(jsonstr)
        except Exception:
            logging.error("%s - Can't parse json: %s" % (self.name, jsonstr))
        if data["result"] == "success":
            self.depth = self.format_depth(data["data"])
        else:
            logging.error("%s - fetched data error" % (self.name))
		****/
	}

	public function sortAndFormat($l, $reverse=false)
	{
		$r = array();
		foreach($l as $i) {
			array_push($r, array('price' => $i[0], 'amount' => $i[1]);
		}
		usort($r, array("MtGoxUSD", "comparePrice"));
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