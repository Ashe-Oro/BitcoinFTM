<?php
$noEchoLog = 1;
require_once("core/include.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Welcome to THE MATRIX</title>
<script language="javascript" type="application/javascript" src="http://lit2bit.com/btcftm/jquery/jquery-1.8.2.min.js"></script>
<script language="javascript" type="text/javascript">
var timeout = null;
var timeMS = 15000;
$(document).ready(function() {
	updateBestOps();
});

function updateBestOps()
{
	if (timeout) { clearTimeout(timeout); }
	$('#waiting').fadeOut(function() { $('#updating').fadeIn(); });
	$('#best-opportunities').load("best-ops.php", function() { 
		$("#updating").fadeOut(function() { 
			$('#waiting').fadeIn(); 
		}); 
		timeout = setTimeout(function() {
			updateBestOps();
		}, timeMS);
	});
}
</script>

<style type="text/css">
td {
	padding: 5px;
	margin: 2px;
}

.pos { 
color: #0c0;
}

.neg {
color: #c00;
}

.ask {
	color: #009;
}

.bid {
	color: #990;
}
</style>
</head>

<body>
<div id="updating">
Updating... this may take a few seconds...
</div>
<div id="waiting">
Waiting 15 seconds...
</div>
<div id="best-opportunities">
</div>

</body>
</html>