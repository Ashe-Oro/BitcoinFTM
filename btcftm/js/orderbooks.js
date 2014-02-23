var orderbooks = new Object();
orderbooks.volume = 0;

orderbooks.updateMarketDepth = function()
{
  var mkts = $('#orderbooks .marketname');
  mkts.each(function(i){
    var mname = $(this).attr('id').replace('orderbooks-marketname-','');
    orderbooks.highlightColumn(mname, 'ask');
    orderbooks.highlightColumn(mname, 'bid');
  });
}

orderbooks.highlightColumn = function(mname, type)
{
  var volcol = $('.'+type+'-list-volume-'+mname);
  var vols = volcol.find('.orderbook-list-item');

  var rows = 0;
  var total = 0;

  var volsArray = new Array();

  vols.removeClass('highlight');
  vols.each(function(i){
    if (total < orderbooks.volume){
      var v = parseFloat($(this).attr('data-volume'));
      volsArray.push(v);
      $(this).addClass('highlight');
      total += v;
      rows = i;
    }
    if (i == vols.length-1){ // all done

      var priceArray = new Array();
      var pricecol = $('.'+type+'-list-price-'+mname);
      var prices = pricecol.find('.orderbook-list-item');

      prices.removeClass('highlight');
      prices.each(function(j){
        if (j <= rows){
          var p = parseFloat($(this).attr('data-price'));
          priceArray.push(p);
          $(this).addClass('highlight');
        } 

        if (j == prices.length-1){
          var totalVol = 0;
          var wVal = 0;
          for (k = 0; k < volsArray.length && k < priceArray.length; k++){
            wVal += volsArray[k] * priceArray[k];
            totalVol += volsArray[k];
          }
          var wTotal = (totalVol > 0) ? wVal / totalVol : 0;
          $('#'+type+'-list-wval-'+mname).html(controls.printCurrency(wTotal, 'USD'));
        }
      });
    }
  });
}

orderbooks.updateVolume = function()
{
  orderbooks.volume = parseFloat($('#orderbooks-btcvol').val());
  orderbooks.updateMarketDepth();
}

orderbooks.toggleMarket = function(el)
{
  var checked = $(el).is(":checked");
  var mname = $(el).attr('id').replace('orderbook-toggle-', '');

  var askVal = $('.asks-list-'+mname+' .list-wval');
  var bidVal = $('.bids-list-'+mname+' .list-wval');

  var mktcol = $('.orderbook-list-wrapper-'+mname);
  if (checked){
    askVal.show();
    bidVal.show();
    mktcol.fadeIn(200);
  } else {
    askVal.hide();
    bidVal.hide();
    mktcol.fadeOut(200);
  }
}

orderbooks.setupButtons = function()
{
  $('#orderbooks-btcvol').on('keyup', function(e){
    orderbooks.updateVolume();  
  });

  $('.orderbook-toggle').click(function(e){
    orderbooks.toggleMarket(this);
  });
}

$(document).ready(function(){
  orderbooks.setupButtons();
  orderbooks.updateVolume();
  //controls.addJSONListener()
});