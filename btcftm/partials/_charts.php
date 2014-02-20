<h1>Market Charts</h1>

<div id="chart-controls">
	<div class="chart-item">
		<label>Time Range: </label>
		<select id="chart-int-range">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
		</select>
	</div>
	<div class="chart-item">
		<select id="chart-hdw-range">
			<option value="hour">hour(s)</option>
			<option value="day">day(s)</option>
			<option value="week">week(s)</option>
		</select>
	</div>
	<div class="chart-item">
		<label for="chart-display">Value: </label>
		<select id="chart-display">
			<option value="avg">Average</option>
			<option value="ask">Ask</option>
			<option value="bid">Bid</option>
			<option value="high">High</option>
			<option value="low">Low</option>
		</select>
	</div>
</div>

<div id="charts">
	<div id="charts-overlay" class="overlay">
		<div id="charts-overlay-content">
			<h3>Loading...</h3>
			<img src="images/ajax-loader.gif" />
		</div>
	</div>
	
	<!--<iframe src="http://bitcoinwisdom.com/markets/bitfinex/btcusd" width="100%" height="400"></iframe>-->
	<div id="ftm-chart">
	  <svg></svg>
	</div>

</div>