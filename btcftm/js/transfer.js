var transfer = new Object();
transfer.frommarket = "Bitstamp";
transfer.tomarket = "Bitfinex";

transfer.setTransferMarkets = function(from, to)
{
  transfer.tomarket = to;
  transfer.frommarket = from;
  
  transfer.updateCapital();
  if (controls.json){
    transfer.updateTransfer();
  }
}

transfer.updateCapital = function()
{
   if (!transfer.frommarket || !transfer.tomarket) { return; }

  var from = (account.balances[transfer.frommarket]) ? account.balances[transfer.frommarket].btc : -1;
  var to = (account.balances[transfer.tomarket]) ? account.balances[transfer.tomarket].btc : -1;

  if (from != -1){
    $('#transfer-from-btc').html(controls.printCurrency(from, 'BTC'));
  } else {
    $('#transfer-from-btc').html(controls.printCurrency(0, 'BTC'));
  }
  if (to != -1){
    $('#transfer-to-btc').html(controls.printCurrency(to, 'BTC'));
  } else {
    $('#transfer-to-btc').html(controls.printCurrency(0, 'BTC'));
  }
}

transfer.updateTransfer = function()
{
  if (!transfer.frommarket || !transfer.tomarket || !controls.json) { return; }

  // do arbitrage buy/sell update here
  var fromSelect = $('#transfer-select-from');
  var toSelect = $('#transfer-select-to');

  fromSelect.find('option:selected').prop('selected',false);
  fromSelect.val(transfer.frommarket).prop('selected', true);
  toSelect.find('option:selected').prop('selected',false);
  toSelect.val(transfer.tomarket).prop('selected', true);

  var frommkt = controls.json.markets[transfer.frommarket];
  var tomkt = controls.json.markets[transfer.tomarket];
  var fromPrice = frommkt.bid;
  var toPrice = tomkt.bid;

  var btcVol = parseFloat($('#transfer-volume-val').val());

  $('#transfer-from-bid').html(controls.printCurrency(fromPrice, 'USD'));
  $('#transfer-to-bid').html(controls.printCurrency(toPrice, 'USD'));

  var fromVal = btcVol * fromPrice;
  var toVal = btcVol * toPrice;

  $('#transfer-from-value').html(controls.printCurrency(fromPrice, 'USD'));
  $('#transfer-to-value').html(controls.printCurrency(toPrice, 'USD'));

  var spread = toPrice - fromPrice;
  var dVal = toVal - fromVal;

  $('#transfer-spread').html(controls.printCurrency(spread, 'USD'));
  $('#transfer-profit').html(controls.printCurrency(dVal, 'USD'));
}

transfer.beginTransfer = function()
{
  var xfrBtn = $('#transfer-btn');
  if (controls.json){
    var fmkt = controls.json.markets[transfer.frommarket];
    var tmkt = controls.json.markets[transfer.tomarket];
    var btcVol = parseFloat($('#transfer-volume-val').val());

    var crypt = 'btc'; // hard-code this for now

    var opts = {
      cid: controls.client.cid,
      fmkt: transfer.frommarket,
      tmkt: transfer.tomarket,
      amt: btcVol,
      crypt: crypt,
    };

    xfrBtn.addClass('disabled');
    $.getJSON("ajax-transfer.php", opts, function(data) {
      if (data.success){
        account.balances[transfer.frommarket][crypt] = parseFloat(data.fmkt.bal); 
        account.balances[transfer.tomarket][crypt] = parseFloat(data.tmkt.bal); 

        $.growl.notice({
          title: "Success!",
          message: data.message
        });
      } else {
        $.growl.error({
          title: "Oh no!",
          message: data.message
        });
      }

      xfrBtn.removeClass('disabled');
      controls.updateBalance(); 
    });
  }
}

transfer.initButtons = function()
{
  $('#transfer-volume-val').on('keyup', function(e){
    transfer.updateTransfer();
  });

  $('#transfer-select-from').change(function(e){
    var name = $(this).find('option:selected').val();
    transfer.setTransferMarkets(name, transfer.tomarket);
  });

  $('#transfer-select-to').change(function(e){
    var name = $(this).find('option:selected').val();
    transfer.setTransferMarkets(transfer.frommarket, name);
  });

  $('#transfer-btn').click(function(e){
    transfer.beginTransfer();
    return noEvent(e);
  })

  transfer.updateCapital();
  transfer.updateTransfer();
}


$(document).ready(function(){
  transfer.initButtons();
  controls.addJSONListener(transfer.updateTransfer);
  controls.addBalanceListener(function() {
    transfer.updateCapital();
    transfer.updateTransfer();
  });
});