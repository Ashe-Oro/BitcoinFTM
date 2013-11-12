<?php
require_once("utils/database_util.php");

$localhosts = array('127.0.0.1', "::1");

if(!in_array($_SERVER['REMOTE_ADDR'], $localhosts)){
    $host = 'bbtcftm.db.7218359.hostedresource.com';
	$user = 'btcftm';
	$pass = 'FTMw1nn1ng!';
	$name = 'btcftm';
}
else {
	$host = '127.0.0.1';
	$user = 'root';
	$pass = 'root';
	$name = 'ftm';
}

$DB = new Database($host, $user, $pass, $name); 
?>