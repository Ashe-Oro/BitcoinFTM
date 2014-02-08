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
controls.ftmStateList = ["dashboard","markets","orders","orderbooks","matrix","charts","bots","sims","settings","portfolio"];
controls.currencies = new Array();

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
}

controls.printCurrency = function(amount, abbr)
{
  if (controls.currencies[abbr]) {
    var c = controls.currencies[abbr];
    return (c.prefix) ? c.symbol + amount.toFixed(c.precision) : amount.toFixed(c.precision) + c.symbol;
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

controls.getMasterJSON = function() {
  $('#loading-data').stop().fadeIn();
	$.getJSON("master-json.php", function( data ) {
		controls.json = data;
		for(i = 0; i < controls.jsonListeners.length; i++){
			controls.jsonListeners[i]();
		}
     $('#loading-data').stop().fadeOut();
	});
}

controls.addJSONListener = function(callback)
{
	controls.jsonListeners.push(callback);
}

controls.changeFtmState = function(state)
{
  if ($.inArray(state, controls.ftmStateList) == -1) { return; }
	if (controls.ftmState != state){
		$('#'+controls.ftmState).addClass('hide');
		controls.ftmState = state;
		$('#'+controls.ftmState).removeClass('hide');
	}
}

controls.updateMarketTicker = function()
{
  var tw = $('#bitcoin-market-ticker .ticker-wrapper');
  tw.stop().fadeOut(500, function() { 
    tw.css({'left': 0});

    var feed = "";
    $.each(controls.json.markets, function(mname, mkt) {
      feed += "<div class='market-ticker' id='market-ticker-"+mname+"'>";
      feed += "<span class='market-ticker-name'>"+mname+": </span>";
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
      tw.animate({'left': -dH+"px"}, 'linear', controls.jsonInt);
    });
  });

  //tw.marquee();
}

controls.bindSidebarMenu = function()
{
	$('#sidebar li').click(function(e) {
		if ($(this).hasClass('active')) { return; }

		var newState = $(this).attr('class');

		$('#sidebar li.active').removeClass('active');
		$(this).addClass('active');


		//window.location.hash = '#'+controls.ftmState;
		controls.changeFtmState(newState);
		
		e.preventDefault();
		e.stopPropagation();
		return false;
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
  var hash = window.location.hash.replace("#","");
  controls.changeFtmState(hash);

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
  controls.loadCurrencies();
	controls.updateMasterJSON();
  controls.addJSONListener(controls.updateMarketTicker);
	controls.bindSidebarMenu();
	controls.bindAccountMenu();
  controls.startControls();
});
