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
  $('#mkt-last-'+mname).html(mkt.last);
  $('#mkt-high-'+mname).html(mkt.high);
  $('#mkt-low-'+mname).html(mkt.low);
  $('#mkt-ask-'+mname).html(mkt.ask);
  $('#mkt-bid-'+mname).html(mkt.bid);
  $('#mkt-vol-'+mname).html(mkt.volume);
 });
}

$(document).ready(function() {
	controls.addJSONListener(markets.updateMarkets);
  //markets.updateMarkets();
});

