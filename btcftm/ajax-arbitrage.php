<?php
header('Content-Type: application/json');
$noEchoLog = 1;
require_once("core/include.php");

$cid = (int) (isset($_GET['cid']) ? $_GET['cid'] : 0);
$amkt = (string) (isset($_GET['amkt']) ? $_GET['amkt'] : "");
$bmkt = (string) (isset($_GET['bmkt']) ? $_GET['bmkt'] : "");
$amt = (float) (isset($_GET['amt']) ? $_GET['amt'] : 0);
$ask = (float) (isset($_GET['ask']) ? $_GET['ask'] : 0);
$bid = (float) (isset($_GET['bid']) ? $_GET['bid'] : 0);
$crypt = (string) (isset($_GET['crypt']) ? $_GET['crypt'] : "BTC"); // for future multi crypto work
$fiat = (string) (isset($_GET['fiat']) ? $_GET['fiat'] : "USD");    // for future multi fiat work

$json = array("success" => false, "message" => "Your market arbitrage failed.");
$json['amkt'] = array(
  $fiat => 0,
  $crypt => 0
);
$json['bmkt'] = array(
  $fiat => 0,
  $crypt => 0
);

if (!is_numeric($cid)){
  $json['message'] .= " - cid is NaN {$cid}";
} else if ($cid <= 0) {
  $json['message'] .= " - cid is zero or less {$cid}";
}
if (!is_string($amkt)){
  $json['message'] .= " - amkt is invalid {$amkt}";
}
if (!is_string($bmkt)){
  $json['message'] .= " - bmkt is invalid {$bmkt}";
}
if (!is_numeric($amt)){
  $json['message'] .= " - amt is NaN {$amt}";
} else if ($amt <= 0) {
  $json['message'] .= " - amt is zero or less {$amt}";
}
if (!is_numeric($ask)){
  $json['message'] .= " - ask is NaN {$ask}";
} else if ($ask <= 0) {
  $json['message'] .= " - ask is zero or less {$ask}";
}
if (!is_numeric($bid)){
  $json['message'] .= " - bid is NaN {$bid}";
} else if ($bid <= 0) {
  $json['message'] .= " - bid is zero or less {$bid}";
}
if (!is_string($crypt)){
  $json['message'] .= " - crypt is invalid {$crypt}";
}
if (!is_string($fiat)){
  $json['message'] .= " - fiat is invalid {$fiat}";
}


if (is_numeric($cid) && is_string($amkt) && is_string($bmkt) && is_numeric($amt) && is_numeric($ask) && is_numeric($bid) && is_string($crypt) && is_string($fiat)){
  if ($cid > 0 && $amt > 0 && $ask > 0 && $bid > 0) {

    $c = new Client((int)$cid);  
    if ($c) {
      if ($p = $c->getPortfolio()){
        if ($p->arbitrage($amkt, $ask, $bmkt, $bid, $amt, $crypt, $fiat)){  
          $askmkt = $p->getPrivateMarket($amkt)->getBalances();
          $bidmkt = $p->getPrivateMarket($bmkt)->getBalances();

          $json['amkt'] = array(
            strtolower($fiat) => $askmkt[strtolower($fiat)],
            strtolower($crypt) => $askmkt[strtolower($crypt)]
          );
          $json['bmkt'] = array(
            strtolower($fiat) => $bidmkt[strtolower($fiat)],
            strtolower($crypt) => $bidmkt[strtolower($crypt)]
          );

          $json['success'] = true;
          $json['message'] = "Your arbitrage was successful!";
        } else {
          $json['message'] .= " Arbitrage failed on {$amkt} and {$bmkt} for client {$cid}";
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