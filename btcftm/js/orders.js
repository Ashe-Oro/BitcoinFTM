var orders = new Object();
orders.market = "MtGox";
orders.ordertype = "market";
orders.buyprice = -1;
orders.sellprice = -1;

orders.changeMarket = function(mname)
{
	orders.market = mname;
	$('#buysell-bitcoin-markets li').removeClass('active')
	$('#buysell-btcmarket-'+mname).addClass('active');

	if (!account.hasCapitalAtMarket(orders.market)){
		$('#buysell-not-available').stop().fadeIn('fast');
	} else {
		$('#buysell-not-available').stop().fadeOut('fast');
	}

	if (orders.ordertype == 'limit'){
		$('#order-limit-price').show();
		$('.order-limit').show();
		$('.order-value').hide();
		$('#order-vol-price').addClass('limit');
	} else {
		$('#order-limit-price').hide();
		$('.order-limit').hide();
		$('.order-value').show();
		$('#order-vol-price').removeClass('limit');
	}

	orders.updateCapital();
	if (controls.json){
		orders.updateBuySell();
	}
	orders.setButtonStates();
}

orders.changeOrderType = function(otype)
{
	if (otype != orders.ordertype){
		orders.ordertype = otype;
		$('#buysell .order-type-button').removeClass('active');
		$('#order-type-'+otype).addClass('active');

		if (orders.ordertype == 'limit'){
			$('#order-limit-price').fadeIn('fast');
			$('.order-value').hide();
			$('.order-limit').fadeIn('fast');
			$('#order-vol-price').addClass('limit');
			
		} else {
			$('#order-limit-price').fadeOut('fast');
			$('.order-limit').hide();
			$('.order-value').fadeIn('fast');
			$('#order-vol-price').removeClass('limit');
		}
	}
}

orders.updateBuySell = function()
{
	if (orders.ordertype == 'market') {
		orders.updateMarketBuySell();
	} else if (orders.ordertype == 'limit') {
		orders.updateLimitBuySell();
	}
}

orders.updateMarketBuySell = function()
{
	var mkt = controls.json.markets[orders.market+'USD'];
	var askPrice = mkt.ask;
	var bidPrice = mkt.bid;
	var btcVol = parseFloat($('#order-volume-val').val());

	$('#order-ask-value').html('$'+askPrice.toFixed(4));
	$('#order-bid-value').html('$'+bidPrice.toFixed(4));

	var buyComValue = 0;
	var sellComValue = 0;
	if (!isNaN(btcVol)){

		/***** THIS NEEDS TO BECOME MARKET-LEVEL LOGIC!!!!!!! *******/
		var buyTotalPreCom = askPrice * btcVol;
		var sellTotalPreCom = bidPrice * btcVol;

		var buyComValue = mkt.commission * buyTotalPreCom;
		var sellComValue = mkt.commission * sellTotalPreCom;

		var buyTotal = buyTotalPreCom - buyComValue;
		var sellTotal = sellTotalPreCom  - sellComValue;
		/*************************/

		$('#buy-button .order-commission-value').html('-$'+buyComValue.toFixed(4)+' (-'+mkt.commission+'%)');
		$('#sell-button .order-commission-value').html('-$'+sellComValue.toFixed(4)+' (-'+mkt.commission+'%)');
		$('#order-buy-total').html('-$'+buyTotal.toFixed(4));
		$('#order-sell-total').html('+$'+sellTotal.toFixed(4));

		orders.setButtonStates();

	} else {
		$('#buy-button .order-commission-value').html('... (-'+mkt.commission+'%)');
		$('#sell-button .order-commission-value').html('... (-'+mkt.commission+'%)');
		$('#order-buy-total').html('...');
		$('#order-sell-total').html('...');
	}
}

orders.updateLimitBuySell = function()
{
	var mkt = controls.json.markets[orders.market+'USD'];
	var btcVol = parseFloat($('#order-volume-val').val());
	var limitPrice = parseFloat($('#order-limit-price-val').val());

	if (!isNaN(btcVol) && !isNaN(limitPrice)){
		/***** THIS NEEDS TO BECOME MARKET-LEVEL LOGIC!!!!!!! *******/
		var buyTotalPreCom = limitPrice * btcVol;
		var sellTotalPreCom = limitPrice * btcVol;

		var buyComValue = mkt.commission * buyTotalPreCom;
		var sellComValue = mkt.commission * sellTotalPreCom;

		var buyTotal = buyTotalPreCom + buyComValue; // inv since buyCom is neg
		var sellTotal = sellTotalPreCom  - sellComValue;
		/*************************/

		$('.order-limit-value').html('$'+limitPrice);

		$('#buy-button .order-commission-value').html('-$'+buyComValue.toFixed(4)+' (-'+mkt.commission+'%)');
		$('#sell-button .order-commission-value').html('-$'+sellComValue.toFixed(4)+' (-'+mkt.commission+'%)');
		$('#order-buy-total').html('-$'+buyTotal.toFixed(4));
		$('#order-sell-total').html('+$'+sellTotal.toFixed(4));

		orders.setButtonStates();

	} else {
		$('.order-limit-value').html('...');

		$('#buy-button .order-commission-value').html('... (-'+mkt.commission+'%)');
		$('#sell-button .order-commission-value').html('... (-'+mkt.commission+'%)');
		$('#order-buy-total').html('...');
		$('#order-sell-total').html('...');
	}
}

orders.updateCapital = function()
{
	if (account.balances[orders.market]){
		var usd = account.balances[orders.market].usd;
		var btc = account.balances[orders.market].btc;
		if (usd != -1 && btc != -1){
			$('#order-capital-usd').html('$'+usd.toFixed(4));
			$('#order-capital-btc').html(btc.toFixed(8)+' BTC');
		} else {
			$('#order-capital-usd').html('$0.0000');
			$('#order-capital-btc').html('0.00000000 BTC');
		}
	}
}

orders.setButtonStates = function()
{
	if (controls.json) {
		var mkt = controls.json.markets[orders.market+'USD'];
		var usd = account.balances[orders.market].usd;
		var btc = account.balances[orders.market].btc;
		var btcVol = parseFloat($('#order-volume-val').val());
		var limitPrice = parseFloat($('#order-limit-price-val').val());

		if (orders.ordertype == 'limit') {
			if (btc < btcVol) {
				$('#sell-button').addClass('disabled');
			} else {
				$('#sell-button').removeClass('disabled');
			}

			if (usd < btcVol*limitPrice) {
				$('#buy-button').addClass('disabled');
			} else {
				$('#buy-button').removeClass('disabled');
			}
		} else if (orders.ordertype == 'market') {
			var askPrice = mkt.ask;
			var bidPrice = mkt.bid;
			if (btc < btcVol) {
				$('#sell-button').addClass('disabled');
			} else {
				$('#sell-button').removeClass('disabled');
			}

			if (usd < btcVol*askPrice) {
				$('#buy-button').addClass('disabled');
			} else {
				$('#buy-button').removeClass('disabled');
			}
		}

		if (btc == -1 || usd == -1){
			$('#buy-button').addClass('disabled');
			$('#sell-button').addClass('disabled');
		}
	} else {
		$('#buy-button').addClass('disabled');
		$('#sell-button').addClass('disabled');
	}
}

orders.initButtons = function()
{
	var mktbuttons = $('#buysell-bitcoin-markets li');
	mktbuttons.click(function(e){
		var mname = $(this).attr('id').replace('buysell-btcmarket-','');
		orders.changeMarket(mname);
		return noEvent(e);
	});

	mktbuttons.find('a').click(function(e){
		$(this).parent('li').click();
		return noEvent(e);
	});

	var orderbuttons = $('#buysell .order-type-button');
	orderbuttons.click(function(e){
		var ordertype = $(this).attr('id').replace('order-type-','');
		orders.changeOrderType(ordertype);
		return noEvent(e);
	});

	$('#order-volume-val').on('keyup', function(e){
		orders.updateBuySell();
	});

	$('#order-limit-price-val').on('keyup', function(e){
		orders.updateBuySell();
	});

	$('#buy-button').click(function() {
		if (!$(this).hasClass('disabled')){
			alert("Buy functionality coming soon.")
		}
	});

	$('#sell-button').click(function() {
		if (!$(this).hasClass('disabled')){
			alert("Sell functionality coming soon.")
		}
	});

	orders.setButtonStates();
}

$(document).ready(function() {
	orders.initButtons();
	orders.changeMarket("MtGox");
	controls.addJSONListener(orders.updateBuySell);
});

