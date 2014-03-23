var matrix = new Object();
matrix.timeout = null;
matrix.timeMS = 15000;
matrix.sleepMS = 2000;
matrix.mob = null;

matrix.updateSpreadMOB = function()
{
  var orderbookCount = 0;
  for (key in orderbooks.markets) {
    if (orderbooks.markets.hasOwnProperty(key)) { orderbookCount++; }
  }

  if (controls.json && orderbookCount){
    matrix.mob = new Object();
    var mkts = controls.json.markets;
    $.each(controls.json.markets, function(aname, amkt) {
      aname = sanitizeMarketName(aname);
      matrix.mob[aname] = new Object();
      $.each(mkts, function(bname, bmkt){
        bname = sanitizeMarketName(bname);
        if (aname != bname && orderbooks.markets[aname] && orderbooks.markets[bname]){
          matrix.mob[aname][bname] = new Object();
          var askW = orderbooks.markets[aname].askW;
          var bidW = orderbooks.markets[bname].bidW;

          if (askW > 0 && bidW > 0){
            var askWVol = askW * controls.volume;
            var bidWVol = bidW * controls.volume;
            var askCom = askWVol * controls.json.markets[aname].commission;
            var bidCom = bidWVol * controls.json.markets[bname].commission;
            var askHoney = askWVol * controls.honey;
            var bidHoney = bidWVol * controls.honey;
            var askTotal = askWVol + askCom + askHoney;
            var bidTotal = bidWVol - bidCom - bidHoney;

            matrix.mob[aname][bname].ask = askTotal;
            matrix.mob[aname][bname].bid = bidTotal;
            matrix.mob[aname][bname].spread = bidTotal - askTotal;
          } else {
            matrix.mob[aname][bname] = null;
          }
        }
      });
    });
  }
};

matrix.updateMatrixOld = function()
{
  if (matrix.timeout) { clearTimeout(matrix.timeout); }
  if ($('#matrix').css("opacity") == 1) {
    $('#matrix .waiting').fadeOut(function() { $('#matrix .updating').fadeIn(); });
    $('#full-matrix').load("full-matrix.php", function() { 
      $("#matrix .updating").fadeOut(function() { 
        $('#matrix .waiting').fadeIn(); 
      }); 
      matrix.timeout = setTimeout(function() {
        matrix.updateMatrix();
      }, matrix.timeMS);
    });
  } else {
    matrix.timeout = setTimeout(function() {
      matrix.updateMatrix();
    }, matrix.timeMS);
  }
};

matrix.updateMatrix = function() 
{
  matrix.updateSpreadMOB();
  if (matrix.mob){
    $.each(matrix.mob, function(aname, amkt){
      aname = sanitizeMarketName(aname);
      $.each(amkt, function(bname, xchg){
         var cell = $('#matrix-'+aname+'-'+bname);
        if (xchg != null){
          bname = sanitizeMarketName(bname);
          var klass = (xchg.spread < 0) ? 'neg' : (xchg.spread > 0) ? 'pos' : 'neu';
          var op = (klass == 'pos') ? 'has-op' : 'no-op';
          cell.find('.matrix-cell-value').html("<span class='"+klass+" "+op+"'>"+controls.printCurrency(xchg.spread, "USD")+"</span>");
          
          //var spread = controls.json.deltas.mob[aname][bname].spread;
          //klass = (spread < 0) ? 'neg' : (spread > 0) ? 'pos' : 'neu';
          //cell.find('.matrix-cell-perc').html("<span class='"+klass+"'><span class='matrix-perc-icon'></span>"+controls.printCurrency(spread, "USD")+"</span>");
        } else {
          cell.find('.matrix-cell-value').html("<span class='no-op'>...</span>");
          //cell.find('.matrix-cell-perc').html('');
        }
      });
    });
  }
  matrix.highlightOpportunities();
};

matrix.updateVolume = function()
{
  $('#matrix-btcvol').val(controls.volume);
  matrix.updateMatrix();
};

matrix.highlightOpportunities = function() 
{
  if (matrix.mob){
   $.each(matrix.mob, function(aname, amkt){
    aname = sanitizeMarketName(aname);
    $.each(amkt, function(bname, xchg){
      bname = sanitizeMarketName(bname);

      usdMin = (matrix.mob[aname][bname]) ? matrix.mob[aname][bname].ask : -1;
      btcMin = controls.volume;

      if (usdMin != -1 && account.balances[aname].usd > usdMin && account.balances[bname].btc > btcMin){
        var cell = $('#matrix-'+aname+'-'+bname);
        if (cell.find('span').hasClass('has-op')){
          cell.addClass('highlight');
          cell.on('click', function(e){
            var askName = $(this).attr('data-ask');
            var bidName = $(this).attr('data-bid');
            arbitrage.setArbitrageMarkets(askName, bidName);
             arbitrage.setReferral('matrix');
            controls.changeFtmState('arbitrage');
            return noEvent(e);
          })
        } else {
          cell.removeClass('highlight');
          cell.off('click');
        }
      } else {
        $('#matrix-'+aname+'-'+bname).removeClass('highlight');
      }
    });
   });
 }
};

matrix.setupButtons = function()
{
  $('#matrix-btcvol').on('keyup', function(e){
    if (!isArrowKey(e)){
      controls.updateVolume($(this).val());
    }
  });
};

$(document).ready(function() {
  matrix.setupButtons();
  controls.addBalanceListener(matrix.updateMatrix);
	controls.addOrderbookListener(matrix.updateMatrix);
  controls.addVolumeListener(matrix.updateVolume);
 // matrix.updateMatrix();
});

