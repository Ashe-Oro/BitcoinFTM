var best_ops = new Object();
best_ops.mob = null;

best_ops.updateOpsMOB = function()
{
  var orderbookCount = 0;
  for (key in orderbooks.markets) {
    if (orderbooks.markets.hasOwnProperty(key)) { orderbookCount++; }
  }

  if (controls.json && controls.json.markets && orderbookCount){    
    best_ops.mob = new Array();
    var mkts = controls.json.markets;
    $.each(controls.json.markets, function(aname, amkt) {
      aname = sanitizeMarketName(aname);
      best_ops.mob[aname] = new Array();
      $.each(mkts, function(bname, bmkt){
        bname = sanitizeMarketName(bname);
        if (aname != bname && orderbooks.markets[aname] && orderbooks.markets[bname]){
          best_ops.mob[aname][bname] = new Object();
          var maxCrypto = (account.balances[aname] && account.balances[bname]) ? Math.min(account.balances[aname].usd2btc, account.balances[bname].btc) : 0;
          var askW = orderbooks.getWeightedPrice(aname, 'ask', maxCrypto);
          var bidW = orderbooks.getWeightedPrice(bname, 'bid', maxCrypto);

          if (askW.price > 0 && bidW.price > 0){
            var askWVol = askW.price * maxCrypto;
            var bidWVol = bidW.price * maxCrypto;
            var askCom = askWVol * controls.json.markets[aname].commission;
            var bidCom = bidWVol * controls.json.markets[bname].commission;
            var askHoney = askWVol * controls.honey;
            var bidHoney = bidWVol * controls.honey;
            var askTotal = askWVol + askCom + askHoney;
            var bidTotal = bidWVol - bidCom - bidHoney;
            var spreadTotal = bidTotal - askTotal;

            best_ops.mob[aname][bname].ask = askTotal;
            best_ops.mob[aname][bname].bid = bidTotal;
            best_ops.mob[aname][bname].crypt = maxCrypto;
            best_ops.mob[aname][bname].spread = spreadTotal;
            best_ops.mob[aname][bname].perc = spreadTotal / askWVol;
          } else {
            best_ops.mob[aname][bname] = null;
          }
        }
      });
    });
  }
};

best_ops.updateMatrix = function() 
{
  best_ops.updateOpsMOB();
  if (best_ops.mob){
    var mIdx = 0;
    var mktCount = getObjectSize(controls.json.markets);
    var mktSqrd = mktCount*mktCount;
    var mkts = controls.json.markets;
    $.each(controls.json.markets, function(aname, amkt){
      aname = sanitizeMarketName(aname);
      if (amkt){
        $.each(mkts, function(bname, bmkt){
          var cell = $('#best_ops-'+aname+'-'+bname);
          var xchg = best_ops.mob[aname][bname];
          if (xchg != null){
            bname = sanitizeMarketName(bname);
            var klass = (xchg.spread < 0) ? 'neg' : (xchg.spread > 0) ? 'pos' : 'neu';
            var op = (klass == 'pos') ? 'has-op' : 'no-op';
            cell.find('.matrix-cell-value').html("<span class='"+klass+" "+op+"'>"+controls.printCurrency(xchg.spread, "USD")+"</span>");
            
            cell.find('.matrix-cell-vol').html("<span class='"+klass+" "+op+"'>Vol: "+controls.printCurrency(xchg.crypt, "BTC")+"</span>");
            //var spread = controls.json.deltas.mob[aname][bname].spread;
            klass = (xchg.perc < 0) ? 'neg' : (xchg.perc > 0) ? 'pos' : 'neu';
            cell.find('.matrix-cell-perc').html("<span class='"+klass+"'><span class='matrix-perc-icon'></span>"+controls.printPercentage(xchg.perc)+"</span>");
          } else {
            cell.find('.matrix-cell-value').html("<span class='no-op'>...</span>");
            //cell.find('.matrix-cell-perc').html('');
          }
        });
      }
      mIdx += mktCount;
      if (mIdx == mktSqrd){
        best_ops.highlightOpportunities();
      }
    });
  }
  best_ops.highlightOpportunities();
};

best_ops.highlightOpportunities = function() 
{
  if (best_ops.mob){
    var mkts = controls.json.markets;
    $.each(controls.json.markets, function(aname, amkt){
      aname = sanitizeMarketName(aname);
      $.each(mkts, function(bname, bmkt){
        bname = sanitizeMarketName(bname);
        var cell = $('#best_ops-'+aname+'-'+bname);
        if (cell.find('span').hasClass('has-op')){
          cell.addClass('highlight');
          cell.on('click', function(e){
            var askName = $(this).attr('data-ask');
            var bidName = $(this).attr('data-bid');
            arbitrage.setArbitrageMarkets(askName, bidName);
            arbitrage.setVolume(best_ops.mob[askName][bidName].crypt);
            arbitrage.setReferral('dashboard');
            controls.changeFtmState('arbitrage');
            return noEvent(e);
          });
        } else {
          cell.removeClass('highlight');
          cell.off('click');
        }
      });
    });
  }
};

$(document).ready(function() {
  controls.addJSONListener(best_ops.updateMatrix);
  controls.addBalanceListener(best_ops.updateMatrix);
  controls.addOrderbookListener(best_ops.updateMatrix);
 // matrix.updateMatrix();
});

