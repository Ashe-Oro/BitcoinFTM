<?php
//header('Content-Type: application/json');
echo "hi";
/*
$noEchoLog = 1;
require_once("core/include.php");

$cid = (isset($_POST['cid']) ? $_POST['cid'] : 0);
$mkt = (isset($_POST['mkt']) ? $_POST['mkt'] : "");
$amt = (isset($_POST['mkt']) ? $_POST['mkt'] : 0);
$val = (isset($_POST['val']) ? $_POST['val'] : 0);
$crypt = (isset($_POST['crypt']) ? $_POST['crypt'] : "BTC"); // for future multi crypto work
$fiat = (isset($_POST['fiat']) ? $_POST['fiat'] : "USD");    // for future multi fiat work

$json = array("success" => false, "message" => "Your market buy order failed.");
$json[$crypt] = 0;
$json[$fiat] = 0;

if (is_numeric($cid) && is_string($mkt) && is_numeric($amt) && is_numeric($val) && is_string($crypt) && is_string($fiat)){
  if ($cid > 0 && !empty($mkt) && $amt > 0 && $val > 0) {

    $cl = new ClientList(array($cid));
    $args = array('nomarkets' => 1, 'nomob' => 1, 'noarbitrers' => 1);

    $a = new Arbitrage($cl, $args);
    if ($pm = $a->getClientPrivateMarket($mname)){
      if ($pm->buy($amt, $val)){
        $json['success'] = true;
        $json['message'] = "Your market buy order was successful.";
        $json[$crypt] = 0;
        $json[$fiat] = 0;
      }
    }
  } 
}

echo json_encode($json);
*/
?>