var markets = new Object();
markets.timeout = null;
markets.timeMS = 15000;
markets.sleepMS = 2000;

// deprecated old live function
markets.updateMarketsLive = function()
{
  if (markets.timeout) { clearTimeout(markets.timeout); }
  if ($('#markets').css("opacity") == 1) {
    $('#markets .waiting').fadeOut(function() { $('#markets .updating').fadeIn(); });
    $('#full-markets').load("full-markets.php", function() { 
      $("#markets .updating").fadeOut(function() { 
        $('#markets .waiting').fadeIn(); 
      }); 
      markets.timeout = setTimeout(function() {
        markets.updateMarkets();
      }, markets.timeMS);
    });
  } else {
    markets.timeout = setTimeout(function() {
      markets.updateMarkets();
    }, markets.timeMS);
  }
}

// new JSON-based function using callback listener
markets.updateMarkets = function()
{
  var deltas = controls.json.deltas.markets;
 $.each(controls.json.markets, function(mname, mkt){
  var dlt = deltas[mname];

  mname = mname.replace("USD","");
  markets.updateMarketValue(mname, 'last', mkt.last);
  markets.updateMarketValue(mname, 'high', mkt.high);
  markets.updateMarketValue(mname, 'low', mkt.low);
  markets.updateMarketValue(mname, 'ask', mkt.ask);
  markets.updateMarketValue(mname, 'bid', mkt.bid);
  markets.updateMarketValue(mname, 'sma10', mkt.sma10);
  markets.updateMarketValue(mname, 'sma25', mkt.sma25);
  $('#mkt-vol-'+mname+' .val').html((mkt.volume > 0) ? mkt.volume.toFixed(6) : "--");

  markets.updateMarketPerc(mname, 'last', dlt.last.perc);
  markets.updateMarketPerc(mname, 'high', dlt.high.perc);
  markets.updateMarketPerc(mname, 'low', dlt.low.perc);
  markets.updateMarketPerc(mname, 'ask', dlt.ask.perc);
  markets.updateMarketPerc(mname, 'bid', dlt.bid.perc);
  markets.updateMarketPerc(mname, 'sma10', dlt.sma10.perc);
  markets.updateMarketPerc(mname, 'sma25', dlt.sma25.perc);
  markets.updateMarketPerc(mname, 'vol', dlt.volume.perc);
 });
}

markets.updateMarketValue = function(mname, valname, value)
{
  if (value > 0){
   $('#mkt-'+valname+'-'+mname+' .val').html(controls.printCurrency(value, 'USD'));
 } else {
  $('#mkt-'+valname+'-'+mname+' .val').html('...');
 }
}

markets.updateMarketPerc = function(mname, valname, value)
{
  var percVal = value.toFixed(3)+'%';
  var klass = (value > 0) ? 'pos' : (value < 0) ? 'neg' : 'neu';
  $('#mkt-'+valname+'-'+mname+' .perc').html("<span class='"+klass+"'><span class='market-perc-icon'></span>"+percVal+"</span>");
}

$(document).ready(function() {
	controls.addJSONListener(markets.updateMarkets);
  //markets.updateMarkets();
});

