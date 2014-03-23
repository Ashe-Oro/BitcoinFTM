<?php
$clientid = $_GET['cid'];
if ($clientid) {
  $DB->query("UPDATE  privatemarkets SET  `usd` =  1000.00, `btc` = 1.0 WHERE clientid = {$clientid}");
  echo "Client {$cid} reset";
}
?>