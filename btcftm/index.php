<?php
session_start();
$noEchoLog = 1;
require_once("./core/config/config.php");

$errs = "";
if (isset($_POST['submit']) && isset($_POST['uname']) && isset($_POST['pwd'])){
	$uname = $_POST['uname'];
	$pwd = $_POST['pwd'];
	$md5pwd = md5($pwd);
	
	$qid = $DB->query("SELECT * FROM clients WHERE username = '{$uname}' AND password = '{$md5pwd}' LIMIT 1");
	if ($result = $DB->fetch_array_assoc($qid)){
		$_SESSION['adminAccess'] = 1;
		$_SESSION['clientID'] = $result['clientid'];
		$_SESSION['username'] = $result['username'];
	} else {
		$errs = "Username and password not recognized.";
	}
}

$signedIn = (isset($_SESSION['adminAccess']) && isset($_SESSION['clientID']) && isset($_SESSION['username'])) ? 1 : 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>GetDemCoins.com</title>
<script language="javascript" type="text/javascript" src="jquery/jquery-1.8.2.min.js"></script>
<script language="javascript" src="js/index.js"></script>
<link rel="stylesheet" type="text/css" href="css/index.css" />
</head>

<body class="<?php echo $signedIn ? "signed_in" : "signed_out"; ?>">

<div id="container">
<?php
if ($signedIn) {
?>

<h2>Gotta Get Dem Coins!</h2>

<p>Loading your profile, please wait...</p>

<img src="/images/ajax-loader.gif" alt="Loading..." width="50" height="50" />

<script language="javascript">
document.location.href = "controls.php";
</script>

<?php
} else {

if (strlen($errs)){ echo "<p>{$errs}</p>"; }
?>

<h1>GetDemCoins.com</h1>

<h3>Please Sign In</h3>

<form name="btc_signin" id="btc_signin" method="post">
<div><label for="uname">Username:</label> <input type="text" id="uname" name="uname" size="32" value="" /></div>
<div><label for="pwd">Password:</label> <input type="password" id="pwd" name="pwd" size="32" value="" /></div>
<div><input type="submit" id="submit" name="submit" value="Sign In" /></div> 
</form>

<?php 
}
?>
</div>
</body>
</html>