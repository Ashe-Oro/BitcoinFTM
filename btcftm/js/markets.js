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
 $.each(controls.json.markets, function(mname, mkt){
  mname = mname.replace("USD","");
  $('#mkt-last-'+mname).html(controls.printCurrency(mkt.last, 'USD'));
  $('#mkt-high-'+mname).html(controls.printCurrency(mkt.high, 'USD'));
  $('#mkt-low-'+mname).html(controls.printCurrency(mkt.low, 'USD'));
  $('#mkt-ask-'+mname).html(controls.printCurrency(mkt.ask, 'USD'));
  $('#mkt-bid-'+mname).html(controls.printCurrency(mkt.bid, 'USD'));
  $('#mkt-sma10-'+mname).html(controls.printCurrency(mkt.sma10, 'USD'));
  $('#mkt-sma25-'+mname).html(controls.printCurrency(mkt.sma25, 'USD'));
  $('#mkt-vol-'+mname).html((mkt.volume > 0) ? mkt.volume.toFixed(6) : "--");
 });
}

$(document).ready(function() {
	controls.addJSONListener(markets.updateMarkets);
  //markets.updateMarkets();
});

