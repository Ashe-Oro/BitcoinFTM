var arbitrage = new Object();
arbitrage.askmarket = "Bitstamp";
arbitrage.bidmarket = "Bitfinex";

arbitrage.setArbitrageMarkets = function(aname, bname)
{
  arbitrage.askmarket = aname;
  arbitrage.bidmarket = bname;
  
  arbitrage.updateCapital();
  if (controls.json){
    arbitrage.updateArbitage();
  }
}

arbitrage.updateCapital = function()
{
  var usd = (arbitrage.askmarket) ? account.balances[arbitrage.askmarket].usd : -1;
  var btc = (arbitrage.bidmarket) ? account.balances[arbitrage.bidmarket].btc : -1;
  if (usd != -1){
    $('#arbitrage-capital-usd').html(controls.printCurrency(usd, 'USD'));
  } else {
    $('#arbitrage-capital-usd').html(controls.printCurrency(0, 'USD'));
  }
  if (btc != -1){
    $('#arbitrage-capital-btc').html(controls.printCurrency(btc, 'BTC'));
  } else {
    $('#arbitrage-capital-btc').html(controls.printCurrency(0, 'BTC'));
  }
  $('#arbitrage-capital .ask-market-name').html(arbitrage.askmarket);
  $('#arbitrage-capital .bid-market-name').html(arbitrage.bidmarket);
  $('#arbitrage-ask-market').html(arbitrage.askmarket);
  $('#arbitrage-sell-market').html(arbitrage.bidmarket);
}

arbitrage.updateArbitage = function()
{
  if (!arbitrage.askmarket || !arbitrage.bidmarket) { return; }

  // do arbitrage buy/sell update here
  var buySelect = $('#arbitrage-select-buy');
  var sellSelect = $('#arbitrage-select-sell');

  buySelect.find('option:selected').prop('selected',false);
  buySelect.val(arbitrage.askmarket).prop('selected', true);
  sellSelect.find('option:selected').prop('selected',false);
  sellSelect.val(arbitrage.bidmarket).prop('selected', true);

  var amkt = controls.json.markets[arbitrage.askmarket];
  var bmkt = controls.json.markets[arbitrage.bidmarket];
  var askPrice = amkt.ask;
  var bidPrice = bmkt.bid;
  var acom = amkt.commission + controls.honey;
  var bcom = bmkt.commission + controls.honey;

  var btcVol = parseFloat($('#arbitrage-volume-val').val());

  $('#arbitrage-ask-value').html(controls.printCurrency(askPrice, 'USD'));
  $('#arbitrage-bid-value').html(controls.printCurrency(bidPrice, 'USD'));

  var buyComValue = 0;
  var sellComValue = 0;
  if (!isNaN(btcVol)){

    var buyTotalPreCom = askPrice * btcVol;
    var sellTotalPreCom = bidPrice * btcVol;

    var buyComValue = acom * buyTotalPreCom;
    var sellComValue = bcom * sellTotalPreCom;

    var buyTotal = buyTotalPreCom + buyComValue;
    var sellTotal = sellTotalPreCom  - sellComValue;
    var estProfit = sellTotal - buyTotal;
    
    var askComPrice = (acom*askPrice) + askPrice;

    var usd = account.balances[arbitrage.askmarket].usd;
    var btc = account.balances[arbitrage.bidmarket].btc;
    var usd2btc = usd / askComPrice;
    var maxBtcVolume = Math.min(usd2btc, btc);

    $('#arbitrage-max-btc').html(controls.printCurrency(maxBtcVolume, "BTC"));
    $('#arbitrage-max-usd').html(controls.printCurrency(maxBtcVolume*askComPrice, "USD"));

    $('#arbitrage-buy-info .arbitrage-commission-value').html('-'+controls.printCurrency(buyComValue, 'USD')+' ('+controls.printCommission(acom)+')');
    $('#arbitrage-sell-info .arbitrage-commission-value').html('-'+controls.printCurrency(sellComValue, 'USD')+' ('+controls.printCommission(bcom)+')');
    $('#arbitrage-buy-total').html('-'+controls.printCurrency(buyTotal, 'USD'));
    $('#arbitrage-sell-total').html('+'+controls.printCurrency(sellTotal, 'USD'));

    $('#arbitrage-profit-usd').html(controls.printCurrency(estProfit, 'USD'));
    $('#arbitrage-profit-btc').html(controls.printCurrency(btcVol, 'BTC'));
    
  } else {
    $('#arbitrage-buy-info .arbitrage-commission-value').html('... ('+controls.printCommission(acom)+')');
    $('#arbitrage-sell-info .arbitrage-commission-value').html('... ('+controls.printCommission(bcom)+')');
    $('#arbitrage-buy-total').html('...');
    $('#arbitrage-sell-total').html('...');
  }
  arbitrage.setButtonStates();
}

arbitrage.setButtonStates = function()
{
  var arbBtn = $('#arbitrage-btn');
  var arbBuy = $('#arbitrage-buy-info');
  var arbSell = $('#arbitrage-sell-info');
  
  if (controls.json) {
    var amkt = controls.json.markets[arbitrage.askmarket];
    var bmkt = controls.json.markets[arbitrage.bidmarket];
    var usd = account.balances[arbitrage.askmarket].usd;
    var btc = account.balances[arbitrage.bidmarket].btc;
    var btcVol = parseFloat($('#arbitrage-volume-val').val());

    var askPrice = amkt.ask;
    var bidPrice = bmkt.bid;
    
    if  (btc < btcVol) {
      arbSell.addClass('disabled');
    } else {
      arbSell.removeClass('disabled');
    }

    if (usd < btcVol*askPrice) {
      arbBuy.addClass('disabled');
    } else {
      arbBuy.removeClass('disabled');
    }

    if (btc == -1 || usd == -1){
      arbBuy.addClass('disabled');
      arbSell.addClass('disabled');
    }

    if(!arbBuy.hasClass('disabled') && !arbSell.hasClass('disabled')){
      arbBtn.removeClass('disabled');
      arbBtn.find('.arbitrage-click-label').html('Click to begin Arbitrage');
    } else {
      arbBtn.addClass('disabled');
      arbBtn.find('.arbitrage-click-label').html('Insufficient funds for Arbitrage');
    }

  } else {
    arbBtn.addClass('disabled');
    arbBuy.addClass('disabled');
    arbSell.addClass('disabled');
  }
}

arbitrage.beginArbitrage = function(){
  var arbBtn = $('#arbitrage-btn');
  if (controls.json){
    var amkt = controls.json.markets[arbitrage.askmarket];
    var bmkt = controls.json.markets[arbitrage.bidmarket];
    var askPrice = amkt.ask;
    var bidPrice = bmkt.bid;
    var btcVol = parseFloat($('#arbitrage-volume-val').val());

    var opts = {
      cid: controls.client.cid,
      amkt: arbitrage.askmarket,
      bmkt: arbitrage.bidmarket,
      amt: btcVol,
      ask: askPrice,
      bid: bidPrice,
      crypt: 'BTC',
      fiat: 'USD',
    };

    arbBtn.addClass('disabled');
    $.getJSON("ajax-arbitrage.php", opts, function(data) {
      if (data.success){
        account.balances[arbitrage.askmarket].usd = parseFloat(data.amkt.usd); 
        account.balances[arbitrage.askmarket].btc = parseFloat(data.amkt.btc); 
        account.balances[arbitrage.bidmarket].usd = parseFloat(data.bmkt.usd); 
        account.balances[arbitrage.bidmarket].btc = parseFloat(data.bmkt.btc); 

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

      arbBtn.removeClass('disabled');
      controls.updateBalance(); 
    });
  }
}

arbitrage.initButtons = function()
{
  $('#arbitrage-volume-val').on('keyup', function(e){
    arbitrage.updateArbitage();
  });

  $('#arbitrage-select-buy').change(function(e){
    var aname = $(this).find('option:selected').val();
    arbitrage.setArbitrageMarkets(aname, arbitrage.bidmarket);
  });

  $('#arbitrage-select-sell').change(function(e){
    var bname = $(this).find('option:selected').val();
    arbitrage.setArbitrageMarkets(arbitrage.askmarket, bname);
  });

  $('#return-to-the-matrix a').click(function(e){
    controls.changeFtmState('matrix');
    return noEvent(e);
  });

  $('#arbitrage-btn').click(function(e){
    arbitrage.beginArbitrage();
    return noEvent(e);
  })
}

$(document).ready(function(){
  arbitrage.initButtons();
  controls.addJSONListener(arbitrage.updateArbitage);
  controls.addBalanceListener(function() {
    arbitrage.updateCapital();
    arbitrage.updateArbitage();
  });
});