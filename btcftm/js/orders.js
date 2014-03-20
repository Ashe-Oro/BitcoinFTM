var orders = new Object();
orders.market = "Bitstamp";
orders.ordertype = "market";
orders.buyprice = -1;
orders.sellprice = -1;

orders.changeMarket = function(mname)
{
	orders.market = mname;
	$('#buysell-bitcoin-markets li').removeClass('active');
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
};


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
};

orders.updateBuySell = function()
{
	if (controls.json){
		if (orders.ordertype == 'market') {
			orders.updateMarketBuySell();
		} else if (orders.ordertype == 'limit') {
			orders.updateLimitBuySell();
		}
	}
};

orders.updateMarketBuySell = function()
{
	var mkt = controls.json.markets[orders.market];
	var askPrice = mkt.ask;
	var bidPrice = mkt.bid;
	var btcVol = parseFloat($('#order-volume-val').val());
	var com = mkt.commission + controls.honey;

	$('#order-ask-value').html(controls.printCurrency(askPrice, 'USD'));
	$('#order-bid-value').html(controls.printCurrency(bidPrice, 'USD'));

	var buyComValue = 0;
	var sellComValue = 0;
	if (!isNaN(btcVol)){

		var buyTotalPreCom = askPrice * btcVol;
		var sellTotalPreCom = bidPrice * btcVol;

		var buyComValue = com * buyTotalPreCom;
		var sellComValue = com * sellTotalPreCom;

		var buyTotal = buyTotalPreCom - buyComValue;
		var sellTotal = sellTotalPreCom  - sellComValue;

		$('#buy-button .order-commission-value').html('-$'+buyComValue.toFixed(4)+' ('+controls.printCommission(com)+')');
		$('#sell-button .order-commission-value').html('-$'+sellComValue.toFixed(4)+' ('+controls.printCommission(com)+')');
		$('#order-buy-total').html('-$'+buyTotal.toFixed(4));
		$('#order-sell-total').html('+$'+sellTotal.toFixed(4));

		orders.setButtonStates();

	} else {
		$('#buy-button .order-commission-value').html('... ('+controls.printCommission(com)+')');
		$('#sell-button .order-commission-value').html('... ('+controls.printCommission(com)+')');
		$('#order-buy-total').html('...');
		$('#order-sell-total').html('...');
	}
};

orders.updateLimitBuySell = function()
{
	var mkt = controls.json.markets[orders.market];
	var btcVol = parseFloat($('#order-volume-val').val());
	var limitPrice = parseFloat($('#order-limit-price-val').val());
	var com = mkt.commission + controls.honey;

	if (!isNaN(btcVol) && !isNaN(limitPrice)){
		var buyTotalPreCom = limitPrice * btcVol;
		var sellTotalPreCom = limitPrice * btcVol;

		var buyComValue = com * buyTotalPreCom;
		var sellComValue = com * sellTotalPreCom;

		// inv since buyCom is neg
		var buyTotal = buyTotalPreCom + buyComValue; 
		var sellTotal = sellTotalPreCom  - sellComValue;

		$('.order-limit-value').html('$'+limitPrice);

		$('#buy-button .order-commission-value').html(controls.printCurrency(-buyComValue, 'USD')+' ('+controls.printCommission(com)+')');
		$('#sell-button .order-commission-value').html(controls.printCurrency(-sellComValue, 'USD')+' ('+controls.printCommission(com)+')');
		$('#order-buy-total').html(controls.printCurrency(-buyTotal, 'USD'));
		$('#order-sell-total').html('+'+controls.printCurrency(sellTotal, 'USD'));

		orders.setButtonStates();

	} else {
		$('.order-limit-value').html('...');

		$('#buy-button .order-commission-value').html('... ('+controls.printCommission(com)+')');
		$('#sell-button .order-commission-value').html('... ('+controls.printCommission(com)+')');
		$('#order-buy-total').html('...');
		$('#order-sell-total').html('...');
	}
};

orders.updateCapital = function()
{
	if (account.balances[orders.market]){
		var usd = account.balances[orders.market].usd;
		var btc = account.balances[orders.market].btc;
		if (usd != -1 && btc != -1){
			$('#order-capital-usd').html(controls.printCurrency(usd, 'USD'));
			$('#order-capital-btc').html(controls.printCurrency(btc, 'BTC'));
		} else {
			$('#order-capital-usd').html(controls.printCurrency(0, 'USD'));
			$('#order-capital-btc').html(controls.printCurrency(0, 'BTC'));
		}
	}
};

orders.placeOrder = function(buysell)
{
	var bsBtn = $('#'+buysell+'-button');
	bsBtn.addClass('disabled');
	if (orders.ordertype == 'limit') {
		$.growl.notice("Limit orders coming soon");
		bsBtn.removeClass('disabled');
	} else {
		var mkt = controls.json.markets[orders.market];
		var price = 0;
		if (buysell == 'buy') {
			price = mkt.ask;
		} else if (buysell == 'sell') {
			price = mkt.bid;
		}
		var btcVol = parseFloat($('#order-volume-val').val());

		var opts = {
			cid: controls.client.cid,
			mkt: orders.market,
			amt: btcVol,
			val: price,
			crypt: 'BTC',
			fiat: 'USD',
			action: buysell
		};

		$.getJSON("ajax-market-buysell.php", opts, function(data) {
			if (data.success){
				account.balances[orders.market].usd = parseFloat(data.usd); 
				account.balances[orders.market].btc = parseFloat(data.btc); 
				$.growl({
					title: "Success",
	      	message: data.message
	      });
			} else {
				$.growl.error({
					title: "Oh noes!",
	      	message: data.message
	      });
			}

			bsBtn.removeClass('disabled');
			controls.updateBalance(); 
		});
	}
};

orders.updateVolume = function()
{
	$('#order-volume-val').val(controls.volume);
	orders.updateBuySell();
};


orders.setButtonStates = function()
{
	if (controls.json) {
		var mkt = controls.json.markets[orders.market];
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
};

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
		if (!isArrowKey(e)){
			controls.updateVolume($(this).val());
		}
	});

	$('#order-limit-price-val').on('keyup', function(e){
		if (!isArrowKey(e)){
			orders.updateBuySell();
		}
	});

	$('#buy-button').click(function() {
		if (!$(this).hasClass('disabled')){
			orders.placeOrder('buy');
		}
	});

	$('#sell-button').click(function() {
		if (!$(this).hasClass('disabled')){
			orders.placeOrder('sell');
		}
	});

	orders.setButtonStates();
};

$(document).ready(function() {
	orders.initButtons();
	orders.changeMarket("Bitstamp");
	controls.addBalanceListener(function(){
		orders.updateCapital();
		orders.updateBuySell();
	});
	controls.addJSONListener(orders.updateBuySell);
	controls.addVolumeListener(orders.updateVolume);
	controls.addOrderbookListener(orders.updateBuySell);
});


