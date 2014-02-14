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
  account.totalusd2btc = 0;
  account.totalbtc2usd = 0;
  account.totalvalueusd = 0;
  account.totalvaluebtc = 0;

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
  });

  $('#account-mkt-usd2btc-total').html(controls.printCurrency(account.totalusd2btc, "BTC"));
  $('#account-mkt-btc2usd-total').html(controls.printCurrency(account.totalbtc2usd, "USD"));
  $('#account-mkt-usdtotal-total').html(controls.printCurrency(account.totalvalueusd, "USD"));
  $('#account-mkt-btctotal-total').html(controls.printCurrency(account.totalvaluebtc, "BTC"));
}

account.hasCapitalAtMarket = function(mname)
{
  return (account.balances[mname] && account.balances[mname].usd != -1 && account.balances[mname].btc != -1);
}

$(document).ready(function(){
	account.initAccount();
	controls.addJSONListener(account.updateMarkets);
})