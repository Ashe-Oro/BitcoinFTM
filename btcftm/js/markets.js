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
  $('#mkt-volume-'+mname+' .val').html((mkt.volume > 0) ? mkt.volume.toFixed(6) : "--");

  markets.updateMarketPerc(mname, 'last', dlt.last.perc);
  markets.updateMarketPerc(mname, 'high', dlt.high.perc);
  markets.updateMarketPerc(mname, 'low', dlt.low.perc);
  markets.updateMarketPerc(mname, 'ask', dlt.ask.perc);
  markets.updateMarketPerc(mname, 'bid', dlt.bid.perc);
  markets.updateMarketPerc(mname, 'sma10', dlt.sma10.perc);
  markets.updateMarketPerc(mname, 'sma25', dlt.sma25.perc);
  markets.updateMarketPerc(mname, 'volume', dlt.volume.perc);
 });
}

markets.bindHoverState = function()
{
  var mkt_cells = $("#markets").find("td, th");
  mkt_cells.on("mouseover", function() {
      var el = $(this),
      pos = el.index();
      el.parent().find("th, td").addClass("hover");
      mkt_cells.filter(":nth-child(" + (pos+1) + ")").addClass("hover");
      if (el.is('td')) { el.addClass("active"); }
  })
  .on("mouseout", function() {
    mkt_cells.removeClass("hover");
    mkt_cells.removeClass("active");
  })
  .on("click", function(e){
    var el = $(this), pos = el.index();
    var th = mkt_cells.filter("th.market:nth-child("+(pos+1)+")");
    if (th){
      var mname = th.attr('id').replace('market-th-','');
      orders.changeMarket(mname);
      controls.changeFtmState('orders');
    }
    return noEvent(e);
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
  markets.bindHoverState();
	controls.addJSONListener(markets.updateMarkets);
  //markets.updateMarkets();
});

