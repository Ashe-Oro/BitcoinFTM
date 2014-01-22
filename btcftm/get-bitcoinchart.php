<?php
require_once("bitcoinchart.php");

$chart = new BitcoinChart();

$opts = array();
if (isset($_POST['m'])){
	$m = '';
	switch($_POST['m']){
		case 'MtGoxUSD':
			$m = 'mtgoxUSD';
			break;

		case 'BitstampUSD':
			$m = 'bitstampUSD';
			break;

		case 'KrakenUSD':
			$m = 'krakenUSD';
			break;

		case 'BTCeUSD':
			$m = 'btceUSD';
			break;

		case 'BitfinexUSD':
			$m = 'bitfinexUSD';
			break;

		case 'CryptotradeUSD':
			$m = 'crytrUSD';
			break;

		case 'CampBXUSD':
			$m = 'cbxUSD';
			break;

		default:
			break;
	}
	$opts['m'] = $m;
}

$chart->setSettings($opts);
$chart->draw();
?>