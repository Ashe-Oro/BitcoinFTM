<?php
session_start();
$noEchoLog = 1;
require_once("config/config.php");

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
<title>BTC FTM</title>
<style type="text/css">
body {
	margin: 0;
	padding:0;
	text-align: center;
	background-color: #990000;
	font-family: Helvetica, Arial, sans-serif;
}

#container {
	margin: 100px auto;
	padding: 35px;
	background-color: #FFFFFF;
	border-top: 3px solid #000;
	border-bottom: 3px solid #000;
	-moz-box-shadow: 0px 0px 30px 10px rgba(0,0,0,0.5);
	-webkit-box-shadow: 0px 0px 30px 10px rgba(0,0,0,0.5);
	box-shadow: 0px 0px 30px 10px rgba(0,0,0,0.5);
}

#btc_signin {
	display: block;
	position: relative;
	margin: 20px auto;
	padding: 10px;
	background-color: #eee;
	border: 1px solid #ccc;
	width: 300px;
}

label {
	display: inline-block;
	float: left;
	width: 100px;
	font-weight: bold;
	line-height: 26px;
}

input {
	width: 180px;
	padding: 2px;
	font-size: 13px;
}

#submit {
	background: #990000;
	color: #eee;
	text-shadow: 1px 1px 1px #000;
	border: 2px solid #660000;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	cursor: pointer;
}

#submit:hover {
	background: #cc0000;
	color: #fff;
}

</style>
</head>

<body>

<div id="container">
<?php
if ($signedIn) {
?>

<h2>CAN I HAZ BITCOINZ?</h2>

<p>Loading your profile, please wait...</p>

<script language="javascript">
document.location.href = "controls.php";
</script>

<?php
} else {

if (strlen($errs)){ echo "<p>{$errs}</p>"; }
?>

<div id="logo"><img src="images/ftm-oval.jpg" /></div>

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