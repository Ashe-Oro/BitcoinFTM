function noEvent(e)
{
	e.preventDefault();
	e.stopPropagation();
	return false;
}

function sanitizeMarketName(mname)
{
	return mname.replace("History","").replace("USD","");
}

/******* CONTROLS OBJECT *********/

var controls = new Object();
controls.ftmState = "dashboard";
controls.json = null;
controls.jsonInt = 15000; // update every 15s for now
controls.jsonListeners = new Array();
controls.balanceListeners = new Array();
controls.ftmStateList = ["dashboard","markets","orders","transfer","orderbooks","matrix","charts","bots","sims","settings","portfolio","arbitrage"];
controls.currencies = new Array();
controls.marketColors = new Array();
controls.currencyColors = new Array();
controls.honey = 0;
controls.client = new Object();


controls.loadClient = function() {
  var cd = $('#client-data');
  controls.client.cid = cd.attr('data-cid');
  controls.client.uname = cd.attr('data-uname');
  controls.client.fname = cd.attr('data-fname');
  controls.client.lname = cd.attr('data-lname');
}

controls.loadCurrencies = function()
{
  $('.currency-data').each(function(){
    var abbr = $(this).attr('id').replace('currency-','');
    controls.currencies[abbr] = new Object();
    controls.currencies[abbr].abbr = abbr;
    controls.currencies[abbr].symbol = $(this).attr('data-symbol');
    controls.currencies[abbr].prefix = $(this).attr('data-prefix') == '1' ? true : false;
    controls.currencies[abbr].precision = parseInt($(this).attr('data-precision'));
  });

  controls.honey = parseFloat($('#honeypot-data').attr('data-honey'));
}

controls.printCurrency = function(amount, abbr, precision)
{
  if (controls.currencies[abbr]) {
    var c = controls.currencies[abbr];
    var sym = c.symbol;
    var prec = (precision) ? precision : c.precision;
    if (amount < 0 && c.prefix) {
      sym = '-'+c.symbol;
      amount = Math.abs(amount);
    }
    return (c.prefix) ? sym + amount.toFixed(prec) : amount.toFixed(prec) + sym;
  }
  return "";
}

controls.printCommission = function(com)
{
  if (com) {
    return "-"+(com*100).toFixed(2)+"%";
  }
  return "";
}

controls.updateMasterJSON = function()
{
	setInterval(function(){
		controls.getMasterJSON();
	}, controls.jsonInt);
  controls.getMasterJSON();
}

controls.updateBalance = function() {
  for(i = 0; i < controls.balanceListeners.length; i++){
    controls.balanceListeners[i]();
  }
}

controls.getMasterJSON = function() {
  $('#loading-data').stop().fadeIn();
	$.getJSON("master-json.php", function( data ) {
		controls.json = data;
		for(i = 0; i < controls.jsonListeners.length; i++){
			controls.jsonListeners[i]();
		}
     $('#loading-data').stop().fadeOut();
	})
  .fail(function() {
    $.growl.warning({title: "Uh oh...", message: "Live update feed failed to load."});
  });
}

controls.addJSONListener = function(callback)
{
	controls.jsonListeners.push(callback);
}

controls.addBalanceListener = function(callback)
{
  controls.balanceListeners.push(callback);
}

controls.changeFtmState = function(state)
{
  if ($.inArray(state, controls.ftmStateList) == -1) { return; }
	if (controls.ftmState != state){
		$('#'+controls.ftmState).addClass('hide');
		controls.ftmState = state;
		$('#'+controls.ftmState).removeClass('hide');

    $.cookie('btcftm_ftmstate', state);
	}
}

controls.updateMarketTicker = function()
{
  var tw = $('#bitcoin-market-ticker .ticker-wrapper');
  tw.stop().fadeOut(500, function() { 
    tw.css({'left': 0});

    var feed = "";
    var deltas = controls.json.deltas.markets;
    $.each(controls.json.markets, function(mname, mkt) {
      var dlt = deltas[mname];
      var trend = (dlt.last.perc > 0) ? "pos" : (dlt.last.perc < 0) ? "neg" : "neu";

      feed += "<div class='market-ticker' id='market-ticker-"+mname+"'>";
      feed += "<span class='market-ticker-name'>"+mname+": </span>";

       feed += "<span class='market-trend "+trend+"'>";
       feed += dlt.last.perc.toFixed(3)+'%';
      feed += "<span class='market-trend-icon'></span>";
      feed += "</span>";

      feed += "<span class='market-ticker-last'>"+controls.printCurrency(mkt.last, "USD")+"</span>";
      feed += " (<span class='market-ticker-ask'>"+controls.printCurrency(mkt.ask, "USD")+"</span>";
      feed += "/<span class='market-ticker-bid'>"+controls.printCurrency(mkt.bid, "USD")+"</span>)";

      feed += "</div>";
    });

    tw.html(feed).fadeIn(500, function() {
      var tWidth = 0;
      tw.find('div').each(function(){
        tWidth += $(this).outerWidth();
      });
      tw.css({width: (tWidth+20)+'px'});

      var header = $('#header');
      var hWidth = header.outerWidth();
      var titleW = header.find('.title').outerWidth();
      var accountW = header.find('.account').outerWidth();
      var welcomeW = header.find('.welcome').outerWidth();
      var tickerW = hWidth - (titleW + accountW + welcomeW);

      var dH = Math.max(0, tWidth - tickerW) + 100;
      tw.animate({'left': -dH+"px"}, controls.jsonInt, 'linear');
    });
  });

  //tw.marquee();
}

controls.bindSidebarMenu = function()
{
	$('#sidebar li').click(function(e) {
		if ($(this).hasClass('active')) { return noEvent(e); }

		var newState = $(this).attr('class');

		$('#sidebar li.active').removeClass('active');
		$(this).addClass('active');


		//window.location.hash = '#'+controls.ftmState;
		controls.changeFtmState(newState);
		
		return noEvent(e);
	});

	/*$('#sidebar li a').click(function(e){
		return false;
	});*/
}

controls.bindAccountMenu = function()
{
	$('#header .account li').click(function(e) {
		if ($(this).hasClass('signout')) { 
			window.location.href = $(this).find('a').attr('href');
			e.preventDefault();
			e.stopPropagation();
			return false;
		}

		if (!$(this).hasClass('active')) {
			var newState = $(this).attr('class');

			$('#sidebar li.active').removeClass('active');
			$(this).addClass('active');

			controls.changeFtmState(newState);
		}
		
		e.preventDefault();
		e.stopPropagation();
		return false;
	});

	$('#header .account li a').click(function(e){
		if ($(this).parent().hasClass('signout')){
			return true;
		} else {
			return false;
		}
	});
}

controls.startControls = function()
{
  if ($.cookie('btcftm_ftmstate')) {
    var hash = $.cookie('btcftm_ftmstate');
    controls.changeFtmState(hash);
  }
  //var hash = window.location.hash.replace("#","");
  //controls.changeFtmState(hash);

  $('#main-content .content').each(function(){
    if ($(this).attr('id') == controls.ftmState) {
      $(this).removeClass('hide');
    } else {
      $(this).addClass('hide');
    }
    $(this).removeClass('init');
  });
}

$(document).ready(function(){
  controls.loadClient();
  controls.loadCurrencies();
	controls.updateMasterJSON();
  controls.addJSONListener(controls.updateMarketTicker);
	controls.bindSidebarMenu();
	controls.bindAccountMenu();
  controls.startControls();
});
