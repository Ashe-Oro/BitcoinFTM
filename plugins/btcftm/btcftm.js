function btcftm() {
	this.mtgox = new mtgox();
	this.bitstamp = new bitstamp();
	this.deltas = new Array();
	
	this.updateTickers = function() {
		this.mtgox.updateTicker();
		this.bitstamp.updateTicker();
	}
	
	this.updateDeltaTickers = function() {
		var cur = this.getCurrentDeltaTicker();
		var trend = 'neu';
		
		var dT = {
			high: Number((this.mtgox.getCurrentHigh() - this.bitstamp.getCurrentHigh())).toFixed(2),
			low: Number((this.mtgox.getCurrentLow() - this.bitstamp.getCurrentLow())).toFixed(2),
			last: Number((this.mtgox.getCurrentLast() - this.bitstamp.getCurrentLast())).toFixed(2),
			bid: Number((this.mtgox.getCurrentBid() - this.bitstamp.getCurrentBid())).toFixed(2),
			ask: Number((this.mtgox.getCurrentBid() - this.bitstamp.getCurrentBid())).toFixed(2),
			volume: Number((this.mtgox.getCurrentVolume() - this.bitstamp.getCurrentVolume())).toFixed(4),
			timestamp: new Date().getTime(), 
			trend: 0
		}
		var cur = this.getCurrentDeltaTicker();
		if (cur) {
			var curS = Number(cur.high)-Number(cur.low) ;
			var dS = Number(dT.high)-Number(dT.low);
			if (curS > dS){
				dT.trend = -1;
			} else if (curS < dS) {
				dT.trend = 1;
			} else {
				dT.trend = 0;
			}
		}
		
		if (!cur || cur.timestamp != dT.timestamp) {
			this.deltas.push(dT);
		}
	}
	
	this.getCurrentDeltaTicker = function() {
		if (this.deltas.length) {
			return this.deltas[this.deltas.length-1];
		} else {
			return {high: 0, low: 0, last: 0, bid: 0, ask: 0, volume: 0, timestamp: 0}
		}
	}
	
	this.getCurrentDeltaHigh = function() {
		return this.getCurrentDeltaTicker().high;
	}
	
	this.getCurrentDeltaLow = function() {
		return this.getCurrentDeltaTicker().low;
	}
	
	this.getCurrentDeltaLast = function() {
		return this.getCurrentDeltaTicker().last;
	}
	
	this.getCurrentDeltaBid = function() {
		return this.getCurrentDeltaTicker().bid;
	}
	
	this.getCurrentDeltaAsk = function() {
		return this.getCurrentDeltaTicker().ask;
	}
	
	this.getCurrentDeltaVolume = function() {
		return this.getCurrentDeltaTicker().volume;
	}
	
	this.getCurrentDeltaTimestamp = function() {
		return this.getCurrentDeltaTicker().timestamp;
	}
	
	this.getDeltaTickersCount = function() {
		return this.deltas.length;
	}
	
	this.getHistory = function(startDate, endDate, scale, callback) {
		$.ajax({
			url: "plugins/bitstamp/bitstamp-history.php",
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