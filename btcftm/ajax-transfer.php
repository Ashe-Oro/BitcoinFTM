<?php
header('Content-Type: application/json');
$noEchoLog = 1;
require_once("core/include.php");

$cid = (int) (isset($_GET['cid']) ? $_GET['cid'] : 0);
$fmkt = (string) (isset($_GET['fmkt']) ? $_GET['fmkt'] : "");
$tmkt = (string) (isset($_GET['tmkt']) ? $_GET['tmkt'] : "");
$amt = (float) (isset($_GET['amt']) ? $_GET['amt'] : 0);
$crypt = (string) (isset($_GET['crypt']) ? $_GET['crypt'] : "BTC"); // for future multi crypto work

$json = array("success" => false, "message" => "Your market transfer order failed.");
$json['crypt'] = $crypt;
$json['fmkt'] = array(
  "name" => 0,
  "bal" => 0
);
$json['tmkt'] = array(
  "name" => 0,
  "bal" => 0
);

if (!is_numeric($cid)){
  $json['message'] .= " - cid is NaN {$cid}";
} else if ($cid <= 0) {
  $json['message'] .= " - cid is zero or less {$cid}";
}
if (!is_string($fmkt)){
  $json['message'] .= " - fmkt is invalid {$fmkt}";
}
if (!is_string($tmkt)){
  $json['message'] .= " - tmkt is invalid {$tmkt}";
}
if (!is_numeric($amt)){
  $json['message'] .= " - amt is NaN {$amt}";
} else if ($amt <= 0) {
  $json['message'] .= " - amt is zero or less {$amt}";
}
if (!is_string($crypt)){
  $json['message'] .= " - crypt is invalid {$crypt}";
}

if (is_numeric($cid) && is_string($fmkt) && is_string($tmkt) && is_numeric($amt) && is_string($crypt)){
  if ($cid > 0 && $amt > 0) {

    $c = new Client((int)$cid);  
    if ($c) {
      if ($p = $c->getPortfolio()){
        if ($p->transfer($fmkt, $tmkt, $amt, $crypt)){  
          $frommkt = $p->getPrivateMarket($fmkt)->getBalances();
          $tomkt = $p->getPrivateMarket($tmkt)->getBalances();

          $json['fmkt'] = array(
            "name" => $fmkt,
            "bal" => $frommkt[$crypt]
          );
          $json['tmkt'] = array(
            "name" => $tmkt,
            "bal" => $tomkt[$crypt]
          );

          $json['success'] = true;
          $json['message'] = "Your transfer was successful!";
        } else {
          $json['message'] .= " Transfer failed on {$fmkt} and {$tmkt} for client {$cid}";
        }
      } else {
        $json['message'] .= " Couldn't load portfolio client {$cid}";
      }
    } else {
      $json['message'] .= " Couldn't load client {$cid}";
    }
  } 
} else {
  $json['message'] .= " One of your parameters is invalid";
}


echo json_encode($json);
?>