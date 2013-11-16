<?php
require_once("utils/database_util.php");

$localhosts = array('127.0.0.1', "::1");

//if running from localhost use local db, otherwise use public site db
if(!in_array($_SERVER['REMOTE_ADDR'], $localhosts)){
    $host = 'btcftmpub.db.8986864.hostedresource.com';
        $user = 'btcftmpub';
        $pass = 'Wolfpack1!';
        $name = 'btcftmpub';
}
else {
        $host = '127.0.0.1';
        $user = 'root';
        $pass = 'root';
        $name = 'ftm';
}

$DB = new Database($host, $user, $pass, $name); 
?>