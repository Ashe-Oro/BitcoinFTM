<?php
$noEchoLog = 1;
require_once("core/include.php");

$res = $DB->query("SELECT * FROM transactions ORDER BY timestamp DESC");
while ($row = $DB->fetch_array_assoc($res)) {
  var_dump($row);
}
?>