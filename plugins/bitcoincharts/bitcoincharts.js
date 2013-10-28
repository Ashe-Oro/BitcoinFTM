function bitcoincharts()
{
	var crt = this;
	this.elem = this;
	this.tickers = new Array();
	
	this.updateTicker = function() {
		$.ajax({
			url: "plugins/bitcoincharts/bitcoincharts.php",
			dataType: 'json',
			data: {
				method: 'updateTicker'
			},
			success: function(json) {
				crt.updateTickerSuccess(json);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				//alert('this does not work');
			}
		});
	}
	
	this.updateTickerSuccess = function(json) {
		if (json) {
			var t = {
				
			};
			this.tickers.push(t);
		}
	}
}