<?php
$noEchoLog = 1;
require_once("core/include.php");

$hp = new Honeypot();
$totalHoney = $hp->getTotalHoney();
$pot = $hp->getPot();

echo "<h1>Total Honey: \${$totalHoney}</h1>";
echo "<p><b>Total Transactions:</b> ".count($pot)."</p>";
?>