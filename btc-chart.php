<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Chart Test</title>

<?php $root = "/FTM/"; ?>

<link type="text/css" href="<?php echo $root; ?>js/jquery-ui/css/ui-lightness/jquery-ui-1.10.3.custom.min.css" />
<style type="text/css">
.ftmChart {
	position: relative;
	width: 800px;
	height: 240px;
	display: block;
	border: 1px solid #777;
	background: #fff;
	margin: 10px;
	overflow: auto;
}

.candle {
	display: block;
	position: absolute;
}

.candle_high {
	display: block;
	position: absolute;
	width: 3px;
	border: 1px solid #000;
	background: rgba(0,0,0,0.3);
}

.bitstamp .candle_high {
	border-color: #F90;
	background-color: rgba(255, 153, 0, 0.3);
	z-index: 2;
}

.mtgox .candle_high {
	border-color: #09F;
	background-color: rgba(0, 153, 255, 0.3);
	z-index: 1;
}

.delta .candle_high {
	border-color: #090;
	background-color: rgba(0, 153, 0, 0.3);
	z-index: 3;
}

.delta.neu .candle_high {
	border-color: #999;
	background-color: rgba(153, 153, 153, 0.3);
}

.delta.neg .candle_high {
	border-color: #900;
	background-color: rgba(153, 0, 0, 0.3);
}

.delta.pos .candle_high {
	border-color: #090;
	background-color: rgba(0, 153, 0, 0.3);
}

.candle_low {
	display: block;
	position: absolute;
	width: 3px;
	border: 1px solid #000;
	background: rgba(0,0,0,0.3);
}

.bitstamp .candle_low {
	border-color: #F90;
	background-color: rgba(255, 153, 0, 0.3);
}

.mtgox .candle_low {
	border-color: #09F;
	background-color: rgba(0, 153, 255, 0.3);
}

.delta .candle_low {
	border-color: #090;
	background-color: rgba(0, 153, 0, 0.3);
}

.delta.neu .candle_high {
	border-color: #999;
	background-color: rgba(153, 153, 153, 0.3);
}

.delta.neg .candle_high {
	border-color: #900;
	background-color: rgba(153, 0, 0, 0.3);
}

.delta.pos .candle_high {
	border-color: #090;
	background-color: rgba(0, 153, 0, 0.3);
}

.candle_avg {
	display: block;
	position: absolute;
	width: 10px;
	border: 1px solid #000;
}

.bitstamp .candle_avg {
	border-color: #F90;
	background-color: rgba(255, 153, 0, 0.3);
}

.mtgox .candle_avg {
	border-color: #09F;
	background-color: rgba(0, 153, 255, 0.3);
}

.delta .candle_avg {
	border-color: #090;
	background-color: rgba(0, 153, 0, 0.3);
}

.delta.pos .candle_avg {
	border-color: #090;
	background-color: rgba(0, 153, 0, 0.3);
}

.delta.neg .candle_avg {
	border-color: #900;
	background-color: rgba(153, 0, 0, 0.3);
}

.delta.neu .candle_avg {
	border-color: #999;
	background-color: rgba(153, 153, 153, 0.3);
}
</style>

<script language="javascript" type="text/javascript" src="<?php echo $root; ?>js/jquery/jquery-1.8.2.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $root; ?>js/jquery/ui.js/jquery-ui-1.9.1.custom.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $root; ?>plugins/btcftm/btcftm.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $root; ?>plugins/client/client.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $root; ?>plugins/mtgox/mtgox.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $root; ?>plugins/bitstamp/bitstamp.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $root; ?>plugins/ftmcharts/ftmchart.js"></script>

<script language="javascript">
var ftm = null;
var ftmChart = null;

$(document).ready(function() {
	ftm = new btcftm(); 
						   
	ftmChart = $('#btc_chart').ftmChart();
	ftmChart.init();
});
</script>

<style type="text/css">
body {
	margin: 0;
	padding: 0;
	text-align: center;
	width: 100%;
	height: 100%;
}

#btc_chart {
	position: absolute;
	top: 0px;
	left: 0px;
	width: 100%;
	height: 100%;
}
</style>
</head>

<body>
<div id="btc_chart""></div>
</body>
</html>