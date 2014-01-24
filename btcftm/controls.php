<?php
$noEchoLog = 1;
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
<script language="javascript" type="application/javascript" src="jquery/jquery-1.8.2.min.js"></script>
<script language="javascript" type="application/javascript" src="js/controls.js"></script>

<link type="text/css" rel="stylesheet" href="css/core.css" />
<link type="text/css" rel="stylesheet" href="css/controls.css" />
</head>

<body>
<?php
if ($signedIn == 1) {
	require_once("core/include.php");

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

	if ($client->isActive()) {
		$cList = new ClientsList(array($client->getID()));
		$arb = new Arbitrage($cList);
	}
	
	$range = "1-day";
	if (isset($_GET['range'])){
		$range = $_GET['range'];
	}
	if (isset($_POST['change-range'])){
		$range = $_POST['change-range'];
	}
	
	$period = PERIOD_15M;
	if (isset($_POST['change-period'])){
		$period = (int) $_POST['change-period'];
	}
	
	$compare = "30-minute";
	if (isset($_POST['market-compare'])){
		$compare = $_POST['market-compare'];
	}
	
	//var_dump($_POST);

?>

<div id="container">

<header id="header">
	<h2 class="title">Bitcoin Financial Trade Manager</h2>
	    
	<h3 class="welcome">Welcome, <b><?php echo $client->getName(); ?></b>!</h3>
	<div class="account"><span class="account-icon"></span>
		<ul class="account-dropdown">
			<li class="settings"><a href="#settings">Account Settings</a></li>
			<li class="portfolio"><a href="#portfolio">Manage Portfolio</a></li>
			<li class="signout"><a href="controls.php?signout=1">Sign Out</a></li>
		</ul>
	</div>
</header>

<div id="container">
	<aside id="sidebar" role="complementary">
    <ul>
    <li class="dashboard"><a href="#dashboard">Dashboard</a></li>
    <li class="markets"><a href="#markets">Markets</a></li>
    <li class="matrix"><a href="#matrix">Matrix</a></li>
    <li class="charts"><a href="#charts">Charts</a></li>
    <li class="bots"><a href="#bots">Bots</a></li>
    <li class="sims"><a href="#sims">Simulations</a></li>
    </ul>
    </aside>
    
    <section id="main-content">
    	<div id="dashboard" class="content">
        <?php include("_dashboard.php"); ?>
        </div>
        <div id="markets" class="content">
        <?php include("_markets.php"); ?>
        </div>
        <div id="bots" class="content">
        <?php include("_bots.php"); ?>
        </div>
        <div id="matrix" class="content">
        <?php include("_matrix.php"); ?>
        </div>
        <div id="charts" class="content">
        <?php include("_charts.php"); ?>
        </div>
        <div id="sims" class="content">
        <?php include("_sims.php"); ?>
        </div>
        <div id="settings" class="content">
        <?php include("_settings.php"); ?>
        </div>
        <div id="portfolio" class="content">
        <?php include("_portfolio.php"); ?>
        </div>
    </section>
</div>

<footer id="footer">
	<span class="copyright">&copy; 2013-<?php echo date('Y'); ?>&nbsp;BTC Financial Trade Manager</span>
</footer>

<?php
} else {
?>

<h2>Signing out...</h2>

<script language="javascript">
document.location.href = "index.php";
</script>

<?php
}
?>
</body>
</html>