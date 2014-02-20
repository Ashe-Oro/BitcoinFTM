var matrix = new Object();
matrix.timeout = null;
matrix.timeMS = 15000;
matrix.sleepMS = 2000;

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
}

matrix.updateMatrix = function() 
{
  $.each(controls.json.mob, function(aname, amkt){
    aname = sanitizeMarketName(aname);
    $.each(amkt, function(bname, xchg){
       var cell = $('#matrix-'+aname+'-'+bname);
      if (xchg != null){
        bname = sanitizeMarketName(bname);
        var klass = (xchg < 0) ? 'neg' : (xchg > 0) ? 'pos' : 'neu';
        var op = (klass == 'pos') ? 'has-op' : 'no-op';
        cell.find('.matrix-cell-value').html("<span class='"+klass+" "+op+"'>"+controls.printCurrency(xchg, "USD")+"</span>");
        
        var spread = controls.json.deltas.mob[aname][bname].spread;
        klass = (spread < 0) ? 'neg' : (spread > 0) ? 'pos' : 'neu';
        cell.find('.matrix-cell-perc').html("<span class='"+klass+"'><span class='matrix-perc-icon'></span>"+controls.printCurrency(spread, "USD")+"</span>");
      } else {
        cell.find('.matrix-cell-value').html("<span class='no-op'>...</span>");
        cell.find('.matrix-cell-perc').html('');
      }
    });
  });
  matrix.highlightOpportunities();
}

matrix.highlightOpportunities = function() 
{
 $.each(controls.json.mob, function(aname, amkt){
  aname = sanitizeMarketName(aname);
  $.each(amkt, function(bname, xchg){
    bname = sanitizeMarketName(bname);
    if (account.balances[aname].usd > 0 && account.balances[bname].btc > 0){
      var cell = $('#matrix-'+aname+'-'+bname);
      if (cell.find('span').hasClass('has-op')){
        cell.addClass('highlight');
        cell.on('click', function(e){
          var askName = $(this).attr('data-ask');
          var bidName = $(this).attr('data-bid');
          arbitrage.setArbitrageMarkets(askName, bidName);
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

$(document).ready(function() {
  controls.addBalanceListener(matrix.updateMatrix);
	controls.addJSONListener(matrix.updateMatrix);
 // matrix.updateMatrix();
});

