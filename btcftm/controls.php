<?php
$VERSION = "0.48 pre-alpha";
$noEchoLog = 1;
session_start();
$signedIn = (isset($_SESSION['adminAccess']) && isset($_SESSION['clientID']) && isset($_SESSION['username'])) ? 1 : 0;

if (isset($_GET['signout'])){
	unset($_SESSION);	
	session_unset();
	$signedIn = 0;
}

if ($signedIn == 1) {
  require_once("core/include.php");
  require_once("panels.php");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>BTC FTM</title>
<?php if ($signedIn == 1) { ?>
  <script language="javascript" type="text/javascript" src="jquery/jquery-1.8.2.min.js"></script>
  <script language="javascript" type="text/javascript" src="jquery/jquery-cookie/jquery.cookie.js"></script>
  <script language="javascript" type="text/javascript" src="jquery/jquery-growl/jquery.growl.js" ></script>
  <link type="text/css" rel="stylesheet" href="jquery/jquery-growl/jquery.growl.css" />
  
  <script language="javascript" src="jquery/d3-master/d3.min.js"></script>
  <script language="javascript" src="jquery/nvd3/nv.d3.js"></script>
  <link href="jquery/nvd3/nv.d3.css" rel="stylesheet" type="text/css" />
 
  <script language="javascript" type="text/javascript" src="js/controls.js"></script>
  <?php if ($config['minify']) { ?>
  <!--<script language="javascript" type="text/javascript" src="js/combine-js.php"></script>-->
  <script language="javascript" type="text/javascript" src="js/combine.min.js"></script>
  <?php 
  } else { 
    foreach($panels as $p){
  	 echo "<script language='javascript' type='text/javascript' src='js/{$p}.js'></script>";
    }
  }
}
?>

<link type="text/css" rel="stylesheet" href="css/core.css" />
<link type="text/css" rel="stylesheet" href="css/controls.css" />

<?php if($signedIn == 1) { ?>
  <?php if ($config['minify']) { ?>
  <link type="text/css" rel="stylesheet" href="css/combine-css.php" />
  <?php 
  } else { 
    foreach($panels as $p){
      echo "<link type='text/css' rel='stylesheet' href='css/{$p}.css' />";
    }
  } 
}
?>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW"> <!-- remove once live -->
</head>

<body>
<?php
if ($signedIn == 1) {
	// MIGRATE THIS SHIT OUT TO SOMEWHERE ELSE!!!!

  /*
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
  */
	
  $client = new Client($_SESSION['username']);
  $cl = new ClientsList($_SESSION['username']);
  $args = array("history" => 1); // use historical mode triggers JSON feed
  $ARB = new Arbitrage($cl, $args);
  $markets = $ARB->markets;
  $currencies = $ARB->currencies;
  $matrix = $ARB->mob->getFullExchangeMatrix();

  require_once("css/market-colors.php"); // add in market color styles for consistency across site
?>
<script language="javascript" type="text/javascript">
$(document).ready(function(){
  <?php 
  echo "controls.json = ";
  $ARB->execCommand('json');
  echo ";";
  echo "controls.updateJSON();"; 
  ?>
});
</script>


<header id="header">
	<h2 class="title">GetThemCoins.com</h2>
  <div id="currency-options">
    <select id="currency-select">
      <option value="usd2btc" selected="selected">USD/BTC</option>
    </select>
  </div>
 	 <div id="loading-data"></div>
	    
	<div class="account">
		<h3 class="welcome"><b><?php echo $client->getName(); ?></b></h3>
		<span class="account-icon"></span>
		<ul class="account-dropdown">
			<li class="settings"><a href="#settings">Account Settings</a></li>
			<li class="signout"><a href="controls.php?signout=1">Sign Out</a></li>
		</ul>
	</div>

  <div id="bitcoin-market-ticker">
    <div class="ticker-wrapper">
    </div>
  </div>
</header>

<div id="container">
	<aside id="sidebar" role="complementary">
    <ul>
    <li class="dashboard"><a href="#dashboard">Dashboard</a></li>
    <li class="portfolio"><a href="#portfolio">Portfolio</a></li>
    <li class="orders"><a href="#orders">Buy/Sell</a></li>
    <li class="transfer"><a href="#transfer">Transfer</a></li>
    <li class="markets"><a href="#markets">Markets</a></li>
    <li class="matrix"><a href="#matrix">Spreads</a></li>
    <li class="orderbooks"><a href="#orderbooks">Order Books</a></li>
    <li class="charts"><a href="#charts">Charts</a></li>
    <!--<li class="bots"><a href="#bots">Bots</a></li>
    <li class="sims"><a href="#sims">Simulations</a></li>-->
    </ul>
  </aside>
    
  <section id="main-content">
    <div id="loading" class="content">
  	 <h1>Bitcoin Financial Trade Manager</h1>
		 <h3>Welcome, <?php echo $client->getName(); ?>!</h3>
     <p>Loading your profile, please wait...</p>
		 <img src="images/ajax-loader.gif" alt="Loading..." width="50" height="50" alt="Loading..." />
    </div>
	   <div id="dashboard" class="content init">
    	<?php include("partials/_dashboard.php"); ?>
    </div>
    <div id="orders" class="content init">
      <?php include("partials/_orders.php"); ?>
    </div>
    <div id="markets" class="content init">
    	<?php include("partials/_markets.php"); ?>
    </div>
     <div id="transfer" class="content init">
      <?php include("partials/_transfer.php"); ?>
    </div>
    <div id="orderbooks" class="content init">
      <?php include("partials/_orderbooks.php"); ?>
    </div>
    <div id="charts" class="content init">
      <?php include("partials/_charts.php"); ?>
    </div>
    <div id="matrix" class="content init">
    	<?php include("partials/_matrix.php"); ?>
    </div>
    <!--
    <div id="bots" class="content init">
      <?php //include("partials/_bots.php"); ?>
    </div>
    <div id="sims" class="content init">
   		<?php //include("partials/_sims.php"); ?>
    </div>
    -->
    <div id="settings" class="content init">
    	<?php include("partials/_settings.php"); ?>
    </div>
    <div id="portfolio" class="content init">
    	<?php include("partials/_portfolio.php"); ?>
    </div>
    <div id="arbitrage" class="content init">
      <?php include("partials/_arbitrage.php"); ?>
    </div>
  </section>
</div>

<div id="hidden-data">
  <?php
  $curlist = $currencies->getCurrencyList();
  foreach($curlist as $abbr => $c){
    echo "<div class='currency-data' id='currency-{$abbr}' data-precision='{$c->precision}' data-prefix='{$c->prefix}' data-symbol='{$c->symbol}'></div>";
  }
  echo "<div class='client-data' id='client-data' data-cid='".$client->getID()."' data-uname='".$client->getUsername()."' data-fname='".$client->getFirstName()."' data-lname='".$client->getLastName()."'></div>";
  echo "<div class='honeypot-data' id='honeypot-data' data-honey='".$config['honey']."' ></div>";
  ?>
</div>

<footer id="footer">
  <?php include("partials/_trading_status.php"); ?>
	<div class="version"><span class="copyright">Verion <?php echo $VERSION; ?> &copy; 2013-<?php echo date('Y'); ?>&nbsp;BTC Financial Trade Manager</span></div>
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