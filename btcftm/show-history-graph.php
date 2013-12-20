<style type="text/css">
#history-graph {
	position: relative;
	display: block; 
	width: 100%;
	height: 350px; 
	background: #000; 
	border: 1px solid #333; 
	color: #fff;
}

#history-graph-loader {
	display: block;
	position: absolute;
	width: 100%;
	height: 100%;
	background-image: url('http://lit2bit.com/btcftm/images/loading.gif');
	background-position: center center;
	background-repeat: no-repeat;
	z-index: 1000;
}

#history-graph-content {
	display: none;
	position: absolute;
	width: 100%;
	height: 100%;
	z-index: 1;
}

#history-graph-canvas {
	display: none;
	position: absolute;
	width: 100%;
	height: 100%;
	z-index: 100;
}

.mtGoxCandle {
	border-right: 1px solid #e00;
	border-left: 1px solid #d00;
	border-top: 1px solid #f00;
	border-bottom: 1px solid #f00;
	background: rgba(255,0,0,0.5);
	min-width: 1px;
	min-height: 1px;
	z-index: 10000;
}
</style>
<script language="javascript" type="application/javascript" src="http://lit2bit.com/btcftm/js/historyGraph/history-graph.js"></script>

<div id="history-graph" class="graph">
<div id="history-graph-loader" class="graph-loader"></div>
<?php
if ($period == 0) {
	$mHistory = $mMarket->getHistoryTickers($startTime);
	$bHistory = $bMarket->getHistoryTickers($startTime);
} else {
	$mHistory = $mMarket->getHistoryPeriodTickers($startTime, time(), $period);
	$bHistory = $bMarket->getHistoryPeriodTickers($startTime, time(), $period);
}

if (isset($mHistory) && isset($bHistory) && isset($period)){
	$mCalc = new TickerCalculator($mHistory);
	$bCalc = new TickerCalculator($bHistory);
	$tickerType = $mCalc->getTickerClass();
	
	$maxHigh = max($mCalc->getMaxHigh(), $bCalc->getMaxHigh());
	$minLow = min($mCalc->getMinLow(), $bCalc->getMinLow());
	$maxTime = max($mCalc->getMaxTimestamp(), $bCalc->getMaxTimestamp());
	$minTime = max($mCalc->getMinTimestamp(), $bCalc->getMinTimestamp());
	
	echo "<div class='graph-content' data-ttype='{$tickerType}' data-period='{$period}' data-maxhigh='{$maxHigh}' data-minlow='{$minLow}' data-mintime='{$minTime}' data-maxtime='{$maxTime}'>";
	echo "<div class='graph-mtgox'>";
	foreach($mHistory as $mTicker){
		echo $mTicker->getTickerCandle();
	}
	echo "</div>";
	
	echo "<div class='graph-bitstamp'>";
	foreach($bHistory as $bTicker) {
		echo $bTicker->getTickerCandle();
	}
	echo "</div>";
	echo "</div>";
} else {
	echo "<p><b>ERROR:</b> history variables not set</p>";
}
?>
<canvas id="history-graph-canvas" class="graph-canvas"></canvas>
</div>