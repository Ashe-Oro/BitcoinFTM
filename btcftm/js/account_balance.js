var account = new Object();
account.balances = new Array();

account.initAccount = function() {
  $('tr.account-mkt').each(function(){
    var mname = $(this).attr('id').replace('account-mkt-','');
    account.balances[mname] = new Object();
    account.balances[mname].usd = $(this).attr('data-usdbal');
    account.balances[mname].btc = $(this).attr('data-btcbal');
  });
}

account.updateMarkets = function() {
  var totalusd2btc = 0;
  var totalbtc2usd = 0;
  var totalusd = 0;
  var totalbtc = 0;
	$.each(controls.json.markets, function(mname, mkt){
    mname = mname.replace("History","").replace("USD", "");
    $('#account-mkt-price-'+mname).html('$'+mkt.last);
    $('#account-mkt-ask-'+mname).html('$'+mkt.ask);
    $('#account-mkt-bid-'+mname).html('$'+mkt.bid);

    var usdbal = parseFloat($('#account-mkt-usdbal-'+mname).attr('data-usdbal'));
    var btcbal = parseFloat($('#account-mkt-btcbal-'+mname).attr('data-btcbal'));
    if (usdbal != -1 && btcbal != -1) {
      var usd2btc = (usdbal/mkt.ask);
      var btc2usd = (btcbal*mkt.bid);
      $('#account-mkt-usd2btc-'+mname).html(usd2btc.toFixed(8)+" BTC");
      $('#account-mkt-btc2usd-'+mname).html("$"+btc2usd.toFixed(4));
      $('#account-mkt-usdtotal-'+mname).html("$"+(usdbal+btc2usd).toFixed(4));
      $('#account-mkt-btctotal-'+mname).html((btcbal+usd2btc).toFixed(8)+" BTC");

      totalusd2btc += usd2btc;
      totalbtc2usd += btc2usd;
      totalusd += usdbal+btc2usd;
      totalbtc += btcbal+usd2btc;
    }
  });

  $('#account-mkt-usd2btc-total').html(totalusd2btc.toFixed(8)+" BTC");
  $('#account-mkt-btc2usd-total').html("$"+totalbtc2usd.toFixed(4));
  $('#account-mkt-usdtotal-total').html("$"+totalusd.toFixed(4));
  $('#account-mkt-btctotal-total').html(totalbtc.toFixed(8)+" BTC");
}

$(document).ready(function(){
	account.initAccount();
	controls.addJSONListener(account.updateMarkets);
})