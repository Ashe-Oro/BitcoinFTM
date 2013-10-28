$(document).ready(function() {
	$.fn.ftmChart = function(chartOpts)
	{
		this.elem = this;
		var chart = this;
		
		this.settings = {
			scale: 'days',
			minValue: 70,
			maxValue: 220,
			startDate: "01-07-13",
			endDate: "20-10-13"
		}
		$.extend(this.settings, chartOpts);
		
		this.settings.dValue = this.settings.maxValue - this.settings.minValue;
		
		this.candles = new Object();
		this.candles.mtgox = new Array();
		this.candles.bitstamp = new Array();
		this.candles.deltas = new Array();
		
		this.container = $(this);
		this.container.addClass('ftmChart');
		this.cHeight = this.container.height()-18;
		this.cWidth = this.container.width()-18;
		
		this.init = function() {
			chart.loadMtGoxCandles();
			chart.loadBitstampCandles();
		}
		
		this.loadMtGoxCandles = function(cList) {
			if (!cList || !cList.length) {
				cList = ftm.mtgox.getHistory(chart.settings.startDate, chart.settings.endDate, chart.scale, chart.loadMtGoxCandlesCallback);
			}
		}
		
		this.loadMtGoxCandlesCallback = function(json) {
			for(var i in json) {
				chart.addMtGoxCandle(json[i]);
			}
		}
		
		this.loadBitstampCandles = function(cList) {
			if (!cList || !cList.length) {
				cList = ftm.bitstamp.getHistory(chart.settings.startDate, chart.settings.endDate, chart.scale, chart.loadBitstampCandlesCallback);
			}
		}
		
		this.loadBitstampCandlesCallback = function(json) {
			for(var i in json) {
				chart.addBitstampCandle(json[i]);
			}
		}
		
		this.loadDeltaCandles = function(cList) {
		}
		
		this.addMtGoxCandle = function(candle) {
			if(typeof(candle) == 'undefined' || !candle){
				candle = ftm.mtgox.getCurrentTicker();
			}
			var mkr = chart.addFtmCandle('mtgox', candle, chart.candles.mtgox.length);
			chart.candles.mtgox.push(mkr);
		}
		
		this.addBitstampCandle = function(candle) {
			if(typeof(candle) == 'undefined' || !candle){
				candle = ftm.bitstamp.getCurrentTicker();
			}
			var mkr = chart.addFtmCandle('bitstamp', candle, chart.candles.bitstamp.length);
			chart.candles.bitstamp.push(mkr);
		}
		
		this.addDeltaCandle = function(candle) {
			if(typeof(candle) == 'undefined' || !candle){
				candle = ftm.getCurrentDeltaTicker();
			}
			var mkr = chart.addFtmCandle('delta', candle, chart.candles.delta.length);
			chart.candles.bitstamp.push(mkr);
		}
		
		this.addFtmCandle = function(mClass, candle, idx)
		{
			var chartScale = chart.cHeight/chart.settings.dValue;
			var candleHeight = chartScale*(candle.high - candle.low);
			var candleWidth = 10;
			var cRange = chartScale*(candle.close-candle.open);
			var cTrend = (cRange > 0) ? 'pos' : (cRange < 0) ? 'neg' : 'neu';
			var rangeHeight = Math.abs(cRange);
			var cHighStick = chartScale*(candle.high-(Math.max(candle.open, candle.close)));
			var cLowStick = chartScale*((Math.min(candle.open, candle.close)-candle.low));
			
			var mkrId = mClass+'_candle_'+idx;
			
			var m = $('<div id="'+mkrId+'" class="candle '+mClass+' '+cTrend+'"><div class="candle_high"></div><div class="candle_low"></div><div class="candle_avg"></div><div class="candle_range"></div></div>');
			chart.container.append(m);
			var mkr = $('#'+mkrId);
			
			mkr.css({
				width: candleWidth+'px',
				height: candleHeight+'px',
				left: (chart.candles[mClass].length*(candleWidth+4))+'px',
				top: (chart.cHeight-(chartScale*candle.high))+'px'
			});
			
			mkr.find('.candle_high').css({
				position: 'absolute',
				width: '0px',
				height: cHighStick+'px',
				left: ((0.5*candleWidth)-1)+'px',	
				top: '0px'
			});
			
			mkr.find('.candle_low').css({
				position: 'absolute',
				width: '0px',
				height: cLowStick+'px',
				left: ((0.5*candleWidth)-1)+'px',
				bottom: '0px'
			});
			
			mkr.find('.candle_avg').css({
				position: 'absolute',
				width: candleWidth+'px',
				height: '0px',
				left: '0px',
				top: (chartScale*(candle.high-candle.avg))+'px'
			});
			
			if (mClass == 'delta') {
				if (candle.trend > 0) {
					mkr.candle.self.addClass('pos');
				} else if (ticker.trend < 0) {
					mkr.candle.self.addClass('neg');
				} else {
					mkr.candle.self.addClass('neu');
				}
			}
			
			var bColor = mkr.find('.candle_avg').css('border-top-color');
			var bColorRGBA = bColor.replace('rgb(', 'rgba(').replace(')', ', 0.3)');
			
			mkr.find('.candle_range').addClass(mClass).addClass(cTrend).css({
				position: 'absolute',
				width: candleWidth+'px',
				height: rangeHeight+'px',
				left: '0px',
				top: (chartScale*(candle.high-(Math.max(candle.open, candle.close))))+'px',
				border: '1px solid '+bColor,
				'background-color': bColorRGBA
			});
			
			return mkr;
		}
		
		return this;
	}
});
