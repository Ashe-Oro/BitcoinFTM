var account = new Object();
account.balances = new Array();

account.initAccount = function() {
  account.totalusd = 0;
  account.totalbtc = 0;
  account.totalbtc2usd = -1;
  account.totalusd2btc = -1;
  account.totalvalueusd = -1;
  account.totalvaluebtc = -1;

  $('tr.account-mkt').each(function(){
    var mname = $(this).attr('id').replace('account-mkt-','');
    account.balances[mname] = new Object();
    account.balances[mname].usd = parseFloat($('#account-mkt-usdbal-'+mname).attr('data-usdbal'));
    account.balances[mname].btc = parseFloat($('#account-mkt-btcbal-'+mname).attr('data-btcbal'));
    account.balances[mname].usd2btc = -1;
    account.balances[mname].btc2usd = -1;
    account.balances[mname].totalvalueusd = -1;
    account.balances[mname].totalvaluebtc = -1;
    account.totalusd += (account.balances[mname].usd != -1) ? account.balances[mname].usd : 0;
    account.totalbtc += (account.balances[mname].btc != -1) ? account.balances[mname].btc : 0;

    if (account.balances[mname].usd == -1 && account.balances[mname].btc == -1) {
      $(this).hide(); // account isn't set up, so hide from dash
    }
  });
}

account.updateMarkets = function() {
  account.totalusd = 0;
  account.totalbtc = 0;
  account.totalusd2btc = 0;
  account.totalbtc2usd = 0;
  account.totalvalueusd = 0;
  account.totalvaluebtc = 0;

  var mktCount = 0;
  for (key in controls.json.markets) {
    if (controls.json.markets.hasOwnProperty(key)) mktCount++;
  }

  var i = 0;
	$.each(controls.json.markets, function(mname, mkt){
    mname = mname.replace("History","").replace("USD", "");
    $('#account-mkt-price-'+mname).html(controls.printCurrency(mkt.last, "USD"));
    $('#account-mkt-ask-'+mname).html(controls.printCurrency(mkt.ask, "USD"));
    $('#account-mkt-bid-'+mname).html(controls.printCurrency(mkt.bid, "USD"));

    //var usdbal = parseFloat($('#account-mkt-usdbal-'+mname).attr('data-usdbal'));
    //var btcbal = parseFloat($('#account-mkt-btcbal-'+mname).attr('data-btcbal'));
    var usdbal = account.balances[mname].usd;
    var btcbal = account.balances[mname].btc;

    if (usdbal != -1 && btcbal != -1) {
      account.totalusd += usdbal;
      account.totalbtc += btcbal;

      $('#account-mkt-usdbal-'+mname).attr('data-usdbal', usdbal);
      $('#account-mkt-usdbal-'+mname).html(controls.printCurrency(usdbal, "USD"));
      $('#account-mkt-btcbal-'+mname).attr('data-btcbal', btcbal);
      $('#account-mkt-btcbal-'+mname).html(controls.printCurrency(btcbal, "BTC"));

      var usd2btc = account.balances[mname].usd2btc = (usdbal/mkt.ask);
      var btc2usd = account.balances[mname].btc2usd = (btcbal*mkt.bid);
      account.balances[mname].totalvalueusd = usdbal+btc2usd;
      account.balances[mname].totalvaluebtc = btcbal+usd2btc;

      $('#account-mkt-usd2btc-'+mname).html(controls.printCurrency(usd2btc, "BTC"));
      $('#account-mkt-btc2usd-'+mname).html(controls.printCurrency(btc2usd, "USD"));
      $('#account-mkt-usdtotal-'+mname).html(controls.printCurrency((usdbal+btc2usd), "USD"));
      $('#account-mkt-btctotal-'+mname).html(controls.printCurrency((btcbal+usd2btc), "BTC"));

      account.totalusd2btc += usd2btc;
      account.totalbtc2usd += btc2usd;
      account.totalvalueusd += account.balances[mname].totalvalueusd;
      account.totalvaluebtc += account.balances[mname].totalvaluebtc;
    }

    if (++i == mktCount) {
      $('#account-mkt-usdbal-total').html(controls.printCurrency(account.totalusd, "USD"));
      $('#account-mkt-btcbal-total').html(controls.printCurrency(account.totalbtc, "BTC"));

      $('#account-mkt-usd2btc-total').html(controls.printCurrency(account.totalusd2btc, "BTC"));
      $('#account-mkt-btc2usd-total').html(controls.printCurrency(account.totalbtc2usd, "USD"));
      $('#account-mkt-usdtotal-total').html(controls.printCurrency(account.totalvalueusd, "USD"));
      $('#account-mkt-btctotal-total').html(controls.printCurrency(account.totalvaluebtc, "BTC"));

      account.updatePieCharts();
    }
  });
}

account.updateMarketPieChart = function()
{
  var percData = new Array();
  var i = 0;

  var mktCount = 0;
  for (key in controls.json.markets) {
    if (controls.json.markets.hasOwnProperty(key)) mktCount++;
  }

  $.each(controls.json.markets, function(mname, mkt){
    mname = mname.replace("History","").replace("USD", "");
    if (account.balances[mname].totalvalueusd > 0) {
      percData.push({ "label": mname, "value": account.balances[mname].totalvalueusd });
    }
    if (++i == mktCount){
     nv.addGraph(function() {
        var chart = nv.models.pieChart()
            .x(function(d) { return d.label })
            .y(function(d) { return d.value })
            .showLabels(true)     //Display pie labels
            .labelThreshold(.05)  //Configure the minimum slice size for labels to show up
            .labelType("percent") //Configure what type of data to show in the label. Can be "key", "value" or "percent"
            .donut(true)          //Turn on Donut mode. Makes pie chart look tasty!
            .donutRatio(0.25)     //Configure how big you want the donut hole size to be.
       
          d3.select("#account-market-chart svg")
              .datum(percData)
              .transition().duration(350)
              .call(chart);
       
        return chart;
      });
    }
  });
}

account.updateCurrencyPieChart = function()
{
  var percData = new Array();
  var i = 0;
  var currencyCount = 2;

  percData.push({"label": 'USD', "value": account.totalusd});
  percData.push({"label": 'BTC', "value": account.totalbtc2usd});

  nv.addGraph(function() {
      var chart = nv.models.pieChart()
          .x(function(d) { return d.label })
          .y(function(d) { return d.value })
          .showLabels(true)     //Display pie labels
          .labelThreshold(.05)  //Configure the minimum slice size for labels to show up
          .labelType("percent") //Configure what type of data to show in the label. Can be "key", "value" or "percent"
          .donut(true)          //Turn on Donut mode. Makes pie chart look tasty!
          .donutRatio(0.25)     //Configure how big you want the donut hole size to be.
     
        d3.select("#account-currency-chart svg")
            .datum(percData)
            .transition().duration(350)
            .call(chart);
     
      return chart;
  });
}

account.updatePieCharts = function()
{
  account.updateMarketPieChart();
  account.updateCurrencyPieChart();
}

account.hasCapitalAtMarket = function(mname)
{
  return (account.balances[mname] && account.balances[mname].usd != -1 && account.balances[mname].btc != -1);
}

$(document).ready(function(){
	account.initAccount();
  controls.addBalanceListener(account.updateMarkets);
	controls.addJSONListener(account.updateMarkets);
})