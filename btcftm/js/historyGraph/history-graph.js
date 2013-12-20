$(document).ready(function(){
	$('#history-graph').historyGraph();
});

$.fn.historyGraph = function()
{
	this.init = function()
	{
		var HG = $(this);
		this.HG = HG;
		this.HG_loader = HG.find('.graph-loader');
		this.HG_content = HG.find('.graph-content');
		this.HG_canvas = HG.find('.graph-canvas');
		this.HG_mtGox = HG.find('.graph-mtgox');
		this.HG_bitstamp = HG.find('.graph-bitstamp');
		
		this.mtGoxTickers = this.HG_mtGox.find('.tickercandle');
		this.bitstampTicker = this.HG_bitstamp.find('.tickercandle');
		
		this.HG_high = Number(this.HG_content.attr('data-maxhigh'));
		this.HG_low = Number(this.HG_content.attr('data-minlow'));
		this.HG_range = this.HG_high - this.HG_low;
		
		this.HG_mintime = Number(this.HG_content.attr('data-mintime'));
		this.HG_maxtime = Number(this.HG_content.attr('data-maxtime'));
		this.HG_timerange = this.HG_maxtime - this.HG_mintime;
		
		this.HG_period = Number(this.HG_content.attr('data-period'));
		this.HG_ttype = this.HG_content.attr('data-ttype');
			
		this.setPxRatios();
		this.drawGraph();
	}
	
	this.setPxRatios = function()
	{
		this.HG_width = this.HG.width();
		this.HG_height = this.HG.height();
		
		this.HG_maxH = this.HG_high * 1.2;
		this.HG_minH = this.HG_low * 0.8;
	
		/* units of currency (USD) per vertical pixel */
		this.HG_hPxRatio =  (this.HG_maxH - this.HG_minH) / this.HG_height;
		
		/* units of time per horizontal pixel */
		this.HG_wPxRatio =  this.HG_timerange / this.HG_width;
		
		//alert(this.HG_period);
		
		/* number of periods in time range */
		this.HG_periodCount = Math.floor(this.HG_timerange / this.HG_period);
		
		/* width in pixels in period */
		this.HG_periodWidth = Math.round(this.HG_width / (this.HG_periodCount+1));
		
	}
	
	/*
	this.drawGraph = function()
	{
		var ctx = this.HG_canvas[0].getContext("2d");
		ctx.fillStyle = "#FF0000";
		//alert(this.HG_periodWidth);
		for(i = 0; i < this.mtGoxTickers.length; i++){
			var t = $(this.mtGoxTickers[i]);
			var dL = t.attr('data-timestamp') - 
		}
		
		ctx.fillRect(0,0,this.HG_periodWidth,10);
	}
	*/
	
	this.drawGraph = function()
	{
		this.HG_content.show();
		for(i = 0; i < this.mtGoxTickers.length; i++){
			var t = $(this.mtGoxTickers[i]);
			var dTime = t.attr('data-timestamp') - this.HG_mintime;
			var dLeft = Math.round(dTime / this.HG_wPxRatio);
			
			if (this.HG_ttype == "PeriodTicker"){
				var dSpread = t.attr('data-close') - t.attr('data-open'); 
				var dH = Math.abs(dSpread);
				var dT = this.HG_maxH - Math.max(t.attr('data-close'), t.attr('data-open'));
				var dTop = Math.round(dH / this.HG_hPxRatio);
			} else {
				var dSpread = t.attr('data-high') - t.attr('data-low'); 
				var dH = Math.abs(dSpread);
				var dT = this.HG_maxH - t.attr('data-high');
				var dTop = Math.round(dH / this.HG_hPxRatio);
			}
			
			t.css({'display': 'block', 'position': 'absolute', 'width': (this.HG_periodWidth-2)+'px', 'height': (dH-2)+'px', top: dTop+'px', left: dLeft+'px' });
			t.addClass('mtGoxCandle');
		}
		this.HG_loader.fadeOut();
	}
	
	$(window).bind('resize', function (){
		$(this).setPxRatios();
	});
	
	this.init();
}