var charts = new Object();
charts.bitwisdom = "http://bitcoinwisdom.com/markets/";
charts.market = "MtGoxUSD";

charts.showChart = function(args) {
  /*$('#bitcoin-chart').load("get-bitcoinchart.php", args, function() { 
    
  });*/
  var cUrl = charts.bitwisdom;
  var cParam = "";
  if (args && typeof(args.m) != 'undefined') {
    charts.market = args.m;
  }
  switch(charts.market){
    case 'BitstampUSD':
      cParam = '/bitstamp/btcusd/';
      break;

    case 'BTCeUSD':
      cParam = '/btce/btcusd/';
      break;

    case 'CampBXUSD':
      cParam = '/campbx/btcusd/';
      break;

    case 'BitfinexUSD':
      cParam = '/bitfinex/btcusd/';
      break;

    case 'CryptotradeUSD':
      cParam = '/cryptotrade/btcusd/';
      break;

    case 'KrakenUSD':
      cParam = "";
      break;

    case 'MtGoxUSD':
    default:
      cParam = '/mtgox/btcusd/';
      break;
  }
  
  $('.bitcoin-market-chart').removeClass('active');
  $('#btcmarket_'+charts.market).addClass('active');

  $('#bitcoin-chart iframe').attr('src', cUrl+cParam)
}

charts.bindChartLinks = function() {
  $('.bitcoin-market-chart').each(function(e){
    $(this).click(function(e){

      var market = $(this).attr('id').replace('btcmarket_','');
      var args = {
        m: market 
      };
      charts.showChart(args);

      e.stopPropagation();
      e.preventDefault();
      return false;
    })

    $(this).find('a').click(function(e){
      return false;
    });
  })
}

charts.sizeChart = function() {
  var cw = $('#charts').width();
  var ch = $('#charts').height();

  var dh = $('#bitcoin-markets').outerHeight() + $('#charts h1').outerHeight();
  $('#bitcoin-chart iframe').css({width: cw+'px', height: (ch-dh-55)+'px'});
}

$(document).ready(function() {
  charts.sizeChart();
  charts.bindChartLinks();
	charts.showChart();
});

