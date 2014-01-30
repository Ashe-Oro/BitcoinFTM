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
  });
}

account.updateMarkets = function() {
  account.totalusd2btc = 0;
  account.totalbtc2usd = 0;
  account.totalvalueusd = 0;
  account.totalvaluebtc = 0;

	$.each(controls.json.markets, function(mname, mkt){
    mname = mname.replace("History","").replace("USD", "");
    $('#account-mkt-price-'+mname).html('$'+mkt.last);
    $('#account-mkt-ask-'+mname).html('$'+mkt.ask);
    $('#account-mkt-bid-'+mname).html('$'+mkt.bid);

    //var usdbal = parseFloat($('#account-mkt-usdbal-'+mname).attr('data-usdbal'));
    //var btcbal = parseFloat($('#account-mkt-btcbal-'+mname).attr('data-btcbal'));
    var usdbal = account.balances[mname].usd;
    var btcbal = account.balances[mname].btc;

    if (usdbal != -1 && btcbal != -1) {
      var usd2btc = account.balances[mname].usd2btc = (usdbal/mkt.ask);
      var btc2usd = account.balances[mname].btc2usd = (btcbal*mkt.bid);
      account.balances[mname].totalvalueusd = usdbal+btc2usd;
      account.balances[mname].totalvaluebtc = btcbal+usd2btc;

      $('#account-mkt-usd2btc-'+mname).html(usd2btc.toFixed(8)+" BTC");
      $('#account-mkt-btc2usd-'+mname).html("$"+btc2usd.toFixed(4));
      $('#account-mkt-usdtotal-'+mname).html("$"+(usdbal+btc2usd).toFixed(4));
      $('#account-mkt-btctotal-'+mname).html((btcbal+usd2btc).toFixed(8)+" BTC");

      account.totalusd2btc += usd2btc;
      account.totalbtc2usd += btc2usd;
      account.totalvalueusd += account.balances[mname].totalvalueusd;
      account.totalvaluebtc += account.balances[mname].totalvaluebtc;
    }
  });

  $('#account-mkt-usd2btc-total').html(account.totalusd2btc.toFixed(8)+" BTC");
  $('#account-mkt-btc2usd-total').html("$"+account.totalbtc2usd.toFixed(4));
  $('#account-mkt-usdtotal-total').html("$"+account.totalvalueusd.toFixed(4));
  $('#account-mkt-btctotal-total').html(account.totalvaluebtc.toFixed(8)+" BTC");
}

account.hasCapitalAtMarket = function(mname)
{
  return (account.balances[mname] && account.balances[mname].usd != -1 && account.balances[mname].btc != -1);
}

$(document).ready(function(){
	account.initAccount();
	controls.addJSONListener(account.updateMarkets);
})