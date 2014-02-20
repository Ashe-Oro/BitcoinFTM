<?php
header('Content-Type: application/json');
$noEchoLog = 1;
require_once("core/include.php");

$valid_actions = array("buy", "sell");

$cid = (isset($_GET['cid']) ? $_GET['cid'] : 0);
$mkt = (isset($_GET['mkt']) ? $_GET['mkt'] : "");
$amt = (isset($_GET['amt']) ? $_GET['amt'] : 0);
$val = (isset($_GET['val']) ? $_GET['val'] : 0);
$crypt = (isset($_GET['crypt']) ? $_GET['crypt'] : "BTC"); // for future multi crypto work
$fiat = (isset($_GET['fiat']) ? $_GET['fiat'] : "USD");    // for future multi fiat work
$action = (isset($_GET['action']) ? $_GET['action'] : NULL);

$json = array("success" => false, "message" => "Your market buy order failed.");
$json[strtolower($crypt)] = 0;
$json[strtolower($fiat)] = 0;

if (!is_numeric($cid)){
  $json['message'] .= " - cid is NaN {$cid}";
} else if ($cid <= 0) {
  $json['message'] .= " - cid is zero or less {$cid}";
}
if (!is_string($mkt)){
  $json['message'] .= " - mkt is invalid {$mkt}";
}
if (!is_numeric($amt)){
  $json['message'] .= " - amt is NaN {$amt}";
} else if ($amt <= 0) {
  $json['message'] .= " - amt is zero or less {$amt}";
}
if (!is_numeric($val)){
  $json['message'] .= " - val is NaN {$val}";
} else if ($val <= 0) {
  $json['message'] .= " - val is zero or less {$val}";
}
if (!is_string($crypt)){
  $json['message'] .= " - crypt is invalid {$crypt}";
}
if (!is_string($fiat)){
  $json['message'] .= " - fiat is invalid {$fiat}";
}

if (!$action || !in_array($action, $valid_actions)) {
  $json['message'] .= " - no valid action specified {$action}";
}



if (is_numeric($cid) && is_string($mkt) && is_numeric($amt) && is_numeric($val) && is_string($crypt) && is_string($fiat)){
  if ($cid > 0 && !empty($mkt) && $amt > 0 && $val > 0) {

    $c = new Client((int)$cid);  
    if ($pm = $c->getPrivateMarket($mkt)){
      if ($pm->{$action}($amt, $val)){  // dynamically call buy/sell/(other action).... barf barf barf...
        $cBal = $pm->getBalance(strtoupper($crypt));
        $fBal = $pm->getBalance(strtoupper($fiat));

        $json['success'] = true;
        $json['message'] = "Your market buy order was successful";
        $json[strtolower($crypt)] = $cBal;
        $json[strtolower($fiat)] = $fBal;
      } else {
        $json['message'] .= " Buy failed on {$mkt} for client {$cid}";
      }
    } else {
      $json['message'] .= " Couldn't load private market {$mkt} for client {$cid}";
    }
  } 
} 

echo json_encode($json);
?>