<?php
session_start();
$signedIn = (isset($_SESSION['adminAccess']) && isset($_SESSION['clientID']) && isset($_SESSION['username'])) ? 1 : 0;

if (isset($_GET['signout'])){
	unset($_SESSION);
	session_unset();
	$signedIn = 0;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>BTC FTM</title>
<link type="text/css" rel="stylesheet" href="http://lit2bit.com/btcftm/css/core.css" />
<link type="text/css" rel="stylesheet" href="http://lit2bit.com/btcftm/css/controls.css" />
</head>

<body>
<?php
if ($signedIn == 1) {
	$noEchoLog = 1;
	require_once("common.php");
	
	$settingsUpdateMessage = "";
	if (isset($_POST['submit-settings'])){
		//var_dump($_POST);
		
		try {
			$maxTxVolume = (float) $_POST['maxTxVolume'];
			$minTxVolume = (float) $_POST['minTxVolume'];
			$balanceMargin = (float) $_POST['balanceMargin'];
			$profitThresh = (float) $_POST['profitThresh'];
			$percThresh = (float) $_POST['percThresh'];
			$trading = (int) $_POST['trading'];
			
			//if ($maxTxVolume > 0 && $minxTxVolume > 0 && $balanceMargin > 0 && $profitThresh > 0 && $percThresh > 0){
				$DB->query("UPDATE clients SET maxTxVolume = {$maxTxVolume}, minTxVolume = {$minTxVolume}, balanceMargin = {$balanceMargin}, profitThresh = {$profitThresh}, percThresh = {$percThresh}, trading = {$trading} WHERE clientID = {$_SESSION['clientID']}");
				$settingsUpdateMessage = "Settings updated";
			//} else {
				//$settingsUpdateMessage = "Failed to update settings: all values must be greater than zero<br />{$maxTxVolume} {$minTxVolume} {$balanceMargin} {$profitThresh} {$percThresh}";
			//}
		} catch (Exception $e) {
			$settingsUpdateMessage = "Failed to update settings: ".$e->getMessage();
		}
		iLog($settingsUpdateMessage);
	}
	
	
	$client = new Client($_SESSION['username']);
	$arb = NULL;
	
	if ($client->isActive() && $client->isTrading()) {
		$arb = new Arbitrage($client);
	}
?>

<div id="container">

<div id="header" class="clearfix full">
<fieldset class="info" id="account_info" >
<legend>Your Accounts</legend>

<fieldset id="mtgox-wallet">
<legend>MtGox</legend>
<div class="info_row"><b>USD: </b><?php echo number_format($client->getMarketBalance("MtGox", "USD"), 4); ?></div>
<div class="info_row"><b>BTC: </b><?php echo number_format($client->getMarketBalance("MtGox", "BTC"), 8); ?> BTC</div>
</fieldset>

<fieldset id="bitstamp-wallet">
<legend>Bitstamp</legend>
<div class="info_row"><b>USD: </b><?php echo number_format($client->getMarketBalance("Bitstamp", "USD"), 4); ?> USD</div>
<div class="info_row"><b>BTC: </b><?php echo number_format($client->getMarketBalance("Bitstamp", "BTC"), 8);  ?> BTC</div>
</fieldset>

</fieldset>

<div id="logo"><img src="images/ftm-oval.jpg" /></div>
<h2 id="logo-title">Bitcoin Finance Trade Manager</h2>

<h3 id="logo-welcome">Welcome, <b><?php echo $client->getName(); ?></b>! <small>(<a href="controls.php?signout=1">Sign Out</a>)</small></h3>

<div id="trading-status">
<span id="testing-status">
<?php
$dMode = ($config['live']) ? "<span style='color: #090;'><b>LIVE</b></span>" : "<span style='color: #F00;'><b>TESTING</b></span>";
echo "Data Mode: {$dMode}";
?>
</span> | <span id="client-active">
<?php
$cMode = ($client->isActive()) ? "<span style='color: #090;'><b>ACTIVE</b></span>" : "<span style='color: #F00;'><b>INACTIVE</b></span>";
echo "Client Mode: {$cMode}";
?>
</span> | <span id="client-trading">
<?php
$tMode = ($client->isTrading()) ? "<span style='color: #090;'><b>ACTIVE</b></span>" : "<span style='color: #F00;'><b>STANDBY</b></span>";
echo "Trading Status: {$tMode}";
?>
</span>
</div>
</div>
<div style="height: 1px; clear: both; display: block;"></div>

<div id="info_wrapper" class="clearfix full">
<fieldset class="info" id="settings-wrapper">
<legend>Your Settings</legend>
<?php if (strlen($settingsUpdateMessage)) { echo "<h4>{$settingsUpdateMessage}</h4>"; } ?>
<form name="client-settings" id="client-settings" method="post">
<div class="info_row"><label for="minTxVolume">Min Trade Volume:</label> <input type="text" id="minTxVolume" name="minTxVolume" value="<?php echo $client->getMinTxVolume(); ?>" size="8" />BTC</div>
<div class="info_row"><label for="maxTxVolume">Max Trade Volume:</label> <input type="text" id="maxTxVolume" name="maxTxVolume" value="<?php echo $client->getMaxTxVolume(); ?>" size="8" />BTC</div>
<div class="info_row"><label for="balanceMargin">Balance Margin:</label> <input type="text" id="balanceMargin" name="balanceMargin" value="<?php echo $client->getBalanceMargin(); ?>" size="5" />%</div>
<div class="info_row"><label for="profitThresh">Profit Threshold:</label> <input type="text" id="profitThresh" name="profitThresh" value="<?php echo $client->getProfitThresh(); ?>" size="5" />USD</div>
<div class="info_row"><label for="percThresh">Percentage Threshold:</label> <input type="text" id="percThresh" name="percThresh" value="<?php echo $client->getPercThresh(); ?>" size="5" />%</div>
<div class="info_row"><label for="trading">Trading Status:</label> <input type="radio" id="trading_active" name="trading" value="1"<?php echo ($client->isTrading()) ? " checked='checked'" : ""; ?> /> ACTIVE <input type="radio" id="trading_standyby" name="trading" value="0"<?php echo ($client->isTrading()) ? "" : " checked='checked'"; ?> /> STANDBY </div>
<input type="submit" name="submit-settings" id="submit-settings" value="Update Settings" class="submit" />
</form>

</fieldset>

<fieldset class="info" id="markets-wrapper">
<legend>Markets</legend>

<fieldset id="mtgox-market">
<legend>MtGox</legend>
<div class="info_row"></div>
</fieldset>

<fieldset id="bitstamp-market">
<legend>Bitstamp</legend>
<div class="info_row"></div>
</fieldset>

</fieldset>
</div>
<div style="height: 1px; clear: both; display: block;"></div>

<div class="clearfix full">
<fieldset class="info" id="results-wrapper">
<legend>Your Results</legend>

</fieldset>
</div>

<div style="height: 1px; clear: both; display: block;"></div>

<?php
} else {
?>

<h2>Fuck off, wanker!</h2>

<script language="javascript">
document.location.href = "index.php";
</script>

<?php
}
?>
</body>
</html>