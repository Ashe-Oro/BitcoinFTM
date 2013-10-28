// JavaScript Document
/**
 * Creates a new mtgox.com object
 */
function mtgox()
{
	var mtg = this;
	this.elem = this;
	this.tickers = new Array();
	
	this.updateTicker = function() {
		$.ajax({
			url: "plugins/mtgox/mtgox.php",
			dataType: 'json',
			data: {
				method: 'updateTicker'
			},
			success: function(json) {
				mtg.updateTickerSuccess(json);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert('this does not work');
			}
		});
	}
	
	this.updateTickerSuccess = function(json) {
		if (json) {
			var t = {
				high: Number(json.data.high.value).toFixed(2), 
				low: Number(json.data.low.value).toFixed(2), 
				last: Number(json.data.last.value).toFixed(2), 
				bid: Number(json.data.buy.value).toFixed(2), 
				ask: Number(json.data.sell.value).toFixed(2), 
				volume: Number(json.data.vol.value).toFixed(4), 
				timestamp: Number(json.data.now),
			};
			var cur = this.getCurrentTicker();
			if (!cur || (cur.timestamp != t.timestamp)) {
				this.tickers.push(t);
			}
		}
	}
	
	this.getCurrentTicker = function() {
		if (this.tickers.length) {
			return this.tickers[this.tickers.length-1];
		} else {
			return {high: 0, low: 0, last: 0, bid: 0, ask: 0, volume: 0, timestamp: 0}
		}
	}
	
	this.getCurrentHigh = function() {
		return this.getCurrentTicker().high;
	}
	
	this.getCurrentLow = function() {
		return this.getCurrentTicker().low;
	}
	
	this.getCurrentLast = function() {
		return this.getCurrentTicker().last;
	}
	
	this.getCurrentBid = function() {
		return this.getCurrentTicker().bid;
	}
	
	this.getCurrentAsk = function() {
		return this.getCurrentTicker().ask;
	}
	
	this.getCurrentVolume = function() {
		return this.getCurrentTicker().volume;
	}
	
	this.getCurrentTimestamp = function() {
		return this.getCurrentTicker().timestamp;
	}
	
	this.getTickersCount = function() {
		return this.tickers.length;
	}
	
	this.getHistory = function(startDate, endDate, scale, callback) {
		$.ajax({
			url: "plugins/mtgox/mtgox-history.php",
			dataType: 'json',
			data: {
				startDate: startDate,
				endDate: endDate,
				scale: scale
			},
			success: function(json) {
				if (typeof(callback) == 'function') {
					callback(json);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert('this does not work');
			}
		});
	}
}