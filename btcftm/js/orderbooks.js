var orderbooks = new Object();
orderbooks.markets = new Array();

orderbooks.updateOrderbooks = function()
{
  setInterval(function(){
    orderbooks.getOrderbooksUpdate();
  }, controls.orderbookInt);
};

orderbooks.getOrderbooksUpdate = function()
{
  $('#orderbooks-data').load('ajax-orderbooks.php', function(){
    orderbooks.updateMarketDepth();
  });
};

orderbooks.updateMarketDepth = function()
{
  var mkts = $('#orderbooks .marketname');
  mkts.each(function(i){
    var mname = $(this).attr('id').replace('orderbooks-marketname-','');
    orderbooks.highlightColumn(mname, 'ask');
    orderbooks.highlightColumn(mname, 'bid');
  }).promise().done(function(){
    controls.updateOrderbooks();
  });
};

orderbooks.highlightColumn = function(mname, type)
{
  var volcol = $('.'+type+'-list-volume-'+mname);
  var vols = volcol.find('.orderbook-list-item');

  var rows = 0;
  var total = 0;
  var mVol = 0;

  var volsArray = new Array();
  var btcVol = $('#orderbooks-btcvol').val();

  vols.removeClass('highlight');
  vols.each(function(i){
    var v = parseFloat($(this).attr('data-volume'));

    if (total < btcVol){
      volsArray.push(v);
      $(this).addClass('highlight');
      total += v;
      rows = i;
    }
    mVol += v;

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

          orderbooks.markets[mname][type+'W'] = wTotal;
          orderbooks.markets[mname][type+'Vol'] = mVol;
        }
      });
    }
  });
};

orderbooks.getWeightedPrice = function(mname, type, vol)
{
  var volcol = $('.'+type+'-list-volume-'+mname);
  var vols = volcol.find('.orderbook-list-item');

  var rows = 0;
  var total = 0;
  var mVol = 0;

  var volsArray = new Array();
  var btcVol = vol;

  var ret = new Object();
  ret.price = 0;
  ret.vol = 0;

  vols.each(function(i){
    var v = parseFloat($(this).attr('data-volume'));
    if (total < btcVol){
      volsArray.push(v);
      total += v;
      rows = i;
    }
    mVol += v;

    if (i == vols.length-1){ // all done
      var priceArray = new Array();
      var pricecol = $('.'+type+'-list-price-'+mname);
      var prices = pricecol.find('.orderbook-list-item');

      prices.each(function(j){
        if (j <= rows){
          var p = parseFloat($(this).attr('data-price'));
          priceArray.push(p);
        } 

        if (j == prices.length-1){
          var totalVol = 0;
          var wVal = 0;
          for (k = 0; k < volsArray.length && k < priceArray.length; k++){
            wVal += volsArray[k] * priceArray[k];
            totalVol += volsArray[k];
          }
          var wTotal = (totalVol > 0) ? wVal / totalVol : 0;
          ret.price = wTotal;
          ret.vol = mVol;

          return ret;
        }
      });
    }
  });
  return ret;
}

orderbooks.updateVolume = function()
{
  $('#orderbooks-btcvol').val(controls.volume);
  orderbooks.updateMarketDepth();
};

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
};

orderbooks.initOrderbooks = function()
{
  var mkts = $('#orderbooks .marketname');
  mkts.each(function(i){
    var mname = $(this).attr('id').replace('orderbooks-marketname-','');
    orderbooks.markets[mname] = {
      askW: 0,
      bidW: 0,
      askVol: 0,
      bidVol: 0
    };
  });

  orderbooks.setupButtons();
  orderbooks.updateVolume();
  orderbooks.updateOrderbooks();
};

orderbooks.setupButtons = function()
{
  $('#orderbooks-btcvol').on('keyup', function(e){
    if (!isArrowKey(e)){
      controls.updateVolume($(this).val());
    }
  });

  $('.orderbook-toggle').click(function(e){
    orderbooks.toggleMarket(this);
  });
  orderbooks.updateMarketDepth();
};

$(document).ready(function(){
  orderbooks.initOrderbooks();
  controls.addVolumeListener(orderbooks.updateVolume);
  //controls.addJSONListener()
});