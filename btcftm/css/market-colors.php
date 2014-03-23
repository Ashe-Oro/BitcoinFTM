<?php 
require_once("core/utils/colorCalculator.php");
$calc = new ColorCalculator();
$curs = $currencies->getCurrencyList(); 

?>


<style type="text/css">
<?php
foreach($markets as $mkt){
  $color = $mkt->getColor();
  $calc->setColor($color);
  $dark1 = $calc->darkenColor(15);
  $dark2 = $calc->darkenColor(30);
  $dark3 = $calc->darkenColor(45);
  $dark4 = $calc->darkenColor(60);
  echo ".mkt-color-{$mkt->mname} { color: {$color}; }\n";
  echo ".mkt-bg-{$mkt->mname} { background-color: {$color}; }\n";
  echo ".mkt-bg-dark1-{$mkt->mname} { background-color: {$dark1}; }\n";
  echo ".mkt-bg-dark2-{$mkt->mname} { background-color: {$dark2}; }\n";
  echo ".mkt-bg-dark3-{$mkt->mname} { background-color: {$dark3}; }\n";
  echo ".mkt-bg-dark4-{$mkt->mname} { background-color: {$dark4}; }\n";
}
foreach($curs as $c){
  $cname = strtolower($c->abbr);
  $color = $c->getColor();
  $calc->setColor($color);
  $dark1 = $calc->darkenColor(15);
  $dark2 = $calc->darkenColor(30);
  $dark3 = $calc->darkenColor(45);
  $dark4 = $calc->darkenColor(60);
  echo ".cur-color-{$cname} { color: {$color}; }\n";
  echo ".cur-bg-{$cname} { background-color: {$color}; }\n";
  echo ".cur-bg-dark1-{$cname} { background-color: {$dark1}; }\n";
  echo ".cur-bg-dark2-{$cname} { background-color: {$dark2}; }\n";
  echo ".cur-bg-dark3-{$cname} { background-color: {$dark3}; }\n";
  echo ".cur-bg-dark4-{$cname} { background-color: {$dark4}; }\n";
}

$cname = "total";
$color = "#990000";
$calc->setColor($color);
$dark1 = $calc->darkenColor(15);
$dark2 = $calc->darkenColor(30);
$dark3 = $calc->darkenColor(45);
$dark4 = $calc->darkenColor(60);
echo ".cur-color-{$cname} { color: {$color}; }\n";
echo ".cur-bg-{$cname} { background-color: {$color}; }\n";
echo ".cur-bg-dark1-{$cname} { background-color: {$dark1}; }\n";
echo ".cur-bg-dark2-{$cname} { background-color: {$dark2}; }\n";
echo ".cur-bg-dark3-{$cname} { background-color: {$dark3}; }\n";
echo ".cur-bg-dark4-{$cname} { background-color: {$dark4}; }\n";
?>
</style>


<script language="javascript" type="text/javascript">
<?php
foreach($markets as $mkt){
  $color = $mkt->getColor();
  $calc->setColor($color);
  $dark1 = $calc->darkenColor(15);
  $dark2 = $calc->darkenColor(30);
  $dark3 = $calc->darkenColor(45);
  $dark4 = $calc->darkenColor(60);
  echo "controls.marketColors['{$mkt->mname}'] = new Object();\n";
  echo "controls.marketColors['{$mkt->mname}'].color = '{$color}';\n";
  echo "controls.marketColors['{$mkt->mname}'].dark1 = '{$dark1}';\n";
  echo "controls.marketColors['{$mkt->mname}'].dark2 = '{$dark2}';\n";
  echo "controls.marketColors['{$mkt->mname}'].dark3 = '{$dark3}';\n";
  echo "controls.marketColors['{$mkt->mname}'].dark4 = '{$dark4}';\n";
}
?>

<?php
foreach($curs as $c){
  echo "controls.currencyColors['{$c->abbr}'] = '".$c->getColor()."';\n";
}
?>

</script>