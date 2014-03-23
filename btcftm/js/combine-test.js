var account = new Object();account.balances = new Array();account.initAccount = function() {account.totalusd = 0;account.totalbtc = 0;account.totalbtc2usd = -1;account.totalusd2btc = -1;account.totalvalueusd = -1;account.totalvaluebtc = -1;$('tr.account-mkt').each(function(){var mname = $(this).attr('id').replace('account-mkt-','');account.balances[mname] = new Object();account.balances[mname].usd = parseFloat($('#account-mkt-usdbal-'+mname).attr('data-usdbal'));account.balances[mname].btc = parseFloat($('#account-mkt-btcbal-'+mname).attr('data-btcbal'));account.balances[mname].usd2btc = -1;account.balances[mname].btc2usd = -1;account.balances[mname].totalvalueusd = -1;account.balances[mname].totalvaluebtc = -1;account.totalusd += (account.balances[mname].usd != -1) ? account.balances[mname].usd : 0;account.totalbtc += (account.balances[mname].btc != -1) ? account.balances[mname].btc : 0;if (account.balances[mname].usd == -1 && account.balances[mname].btc == -1) {$(this).hide(); }});};account.updateMarkets = function() {account.totalusd = 0;account.totalbtc = 0;account.totalusd2btc = 0;account.totalbtc2usd = 0;account.totalvalueusd = 0;account.totalvaluebtc = 0;var mktCount = 0;for (key in controls.json.markets) {if (controls.json.markets.hasOwnProperty(key)) { mktCount++; }}var i = 0;$.each(controls.json.markets, function(mname, mkt){mname = mname.replace("History","").replace("USD", "");$('#account-mkt-price-'+mname).html(controls.printCurrency(mkt.last, "USD", 2));$('#account-mkt-ask-'+mname).html(controls.printCurrency(mkt.ask, "USD", 2));$('#account-mkt-bid-'+mname).html(controls.printCurrency(mkt.bid, "USD", 2));var usdbal = account.balances[mname].usd;var btcbal = account.balances[mname].btc;if (usdbal != -1 && btcbal != -1) {account.totalusd += usdbal;account.totalbtc += btcbal;$('#account-mkt-usdbal-'+mname).attr('data-usdbal', usdbal);$('#account-mkt-usdbal-'+mname).html(controls.printCurrency(usdbal, "USD", 2));$('#account-mkt-btcbal-'+mname).attr('data-btcbal', btcbal);$('#account-mkt-btcbal-'+mname).html(controls.printCurrency(btcbal, "BTC"));var usd2btc = account.balances[mname].usd2btc = (usdbal/mkt.ask);var btc2usd = account.balances[mname].btc2usd = (btcbal*mkt.bid);account.balances[mname].totalvalueusd = usdbal+btc2usd;account.balances[mname].totalvaluebtc = btcbal+usd2btc;$('#account-mkt-usd2btc-'+mname).html(controls.printCurrency(usd2btc, "BTC"));$('#account-mkt-btc2usd-'+mname).html(controls.printCurrency(btc2usd, "USD", 2));$('#account-mkt-usdtotal-'+mname).html(controls.printCurrency((usdbal+btc2usd), "USD", 2));$('#account-mkt-btctotal-'+mname).html(controls.printCurrency((btcbal+usd2btc), "BTC"));account.totalusd2btc += usd2btc;account.totalbtc2usd += btc2usd;account.totalvalueusd += account.balances[mname].totalvalueusd;account.totalvaluebtc += account.balances[mname].totalvaluebtc;}if (++i == mktCount) {$('#account-mkt-usdbal-total').html(controls.printCurrency(account.totalusd, "USD", 2));$('#account-mkt-btcbal-total').html(controls.printCurrency(account.totalbtc, "BTC"));$('#account-mkt-usd2btc-total').html(controls.printCurrency(account.totalusd2btc, "BTC"));$('#account-mkt-btc2usd-total').html(controls.printCurrency(account.totalbtc2usd, "USD", 2));$('#account-mkt-usdtotal-total').html(controls.printCurrency(account.totalvalueusd, "USD", 2));$('#account-mkt-btctotal-total').html(controls.printCurrency(account.totalvaluebtc, "BTC"));account.updatePieCharts();}});};account.updateMarketPieChart = function(){var percData = new Array();var colorsList = new Array();var i = 0;var mktCount = 0;for (key in controls.json.markets) {if (controls.json.markets.hasOwnProperty(key)) { mktCount++; }}$.each(controls.json.markets, function(mname, mkt){var mname = mname.replace("History","").replace("USD", "");if (account.balances[mname].totalvalueusd > 0) {percData.push({ "label": mname, "color": controls.marketColors[mname].color, "value": account.balances[mname].totalvalueusd });colorsList.push(controls.marketColors[mname].color)}if (++i == mktCount){ nv.addGraph(function() {var chart = nv.models.pieChart().x(function(d) { return d.label }).y(function(d) { return d.value }).showLabels(true) .labelThreshold(.05) .labelType("percent") .donut(true).donutRatio(0.25).color(colorsList).tooltipContent(function(key, y, e, graph) {return '<h3>'+key+'</h3>'+'<p>'+controls.printCurrency(parseFloat(y.replace(',','')), 'USD', 2)+'</p>';}); d3.select("#account-market-chart svg").datum(percData).transition().duration(350).call(chart); return chart;});}});};account.updateDistributionPieChart = function(){var percData = new Array();var colorsList = new Array();var i = 0;var mktCount = 0;for (key in controls.json.markets) {if (controls.json.markets.hasOwnProperty(key)) { mktCount++; }}$.each(controls.json.markets, function(mname, mkt){var mname = mname.replace("History","").replace("USD", "");if (account.balances[mname].usd > 0) {percData.push({ "label": mname+' USD', "color": controls.marketColors[mname].dark1, "value": account.balances[mname].usd });colorsList.push(controls.marketColors[mname].dark1)}if (account.balances[mname].btc2usd > 0) {percData.push({ "label": mname+' BTC', "color": controls.marketColors[mname].dark3, "value": account.balances[mname].btc2usd });colorsList.push(controls.marketColors[mname].dark3)}if (++i == mktCount){ nv.addGraph(function() {var chart = nv.models.pieChart().x(function(d) { return d.label }).y(function(d) { return d.value }).showLabels(true) .labelThreshold(.05).labelType("percent") .donut(true).donutRatio(0.25) .color(colorsList).showLegend(false).tooltipContent(function(key, y, e, graph) {return '<h3>'+key+'</h3>'+'<p>'+controls.printCurrency(parseFloat(y.replace(',','')), 'USD')+'</p>';}); d3.select("#account-distribution-chart svg").datum(percData).transition().duration(350).call(chart); return chart;});}});};account.updateCurrencyPieChart = function(){var percData = new Array();var i = 0;var currencyCount = 2;percData.push({"label": 'USD', "color": controls.currencyColors['USD'], "value": account.totalusd});percData.push({"label": 'BTC', "color": controls.currencyColors['BTC'], "value": account.totalbtc2usd});var percColors = new Array(controls.currencyColors['USD'], controls.currencyColors['BTC']);nv.addGraph(function() {var chart = nv.models.pieChart().x(function(d) { return d.label }).y(function(d) { return d.value }).showLabels(true) .labelThreshold(.05).labelType("percent") .donut(true).donutRatio(0.25).color(percColors).tooltipContent(function(key, y, e, graph) {return '<h3>'+key+'</h3>'+'<p>'+controls.printCurrency(parseFloat(y.replace(',','')), 'USD')+'</p>';}); d3.select("#account-currency-chart svg").datum(percData).transition().duration(350).call(chart); return chart;});};account.updatePieCharts = function(){account.updateMarketPieChart();account.updateDistributionPieChart();account.updateCurrencyPieChart();};account.hasCapitalAtMarket = function(mname){return (account.balances[mname] && account.balances[mname].usd != -1 && account.balances[mname].btc != -1);};$(document).ready(function(){account.initAccount();controls.addBalanceListener(account.updateMarkets);controls.addJSONListener(account.updateMarkets);});
var dashboard = new Object();dashboard.best_ops = new Object();dashboard.best_ops.timeout = null;dashboard.best_ops.timeMS = 15000;dashboard.best_ops.sleepMS = 2000;dashboard.best_ops.updatebest_ops = function(){if (dashboard.best_ops.timeout) { clearTimeout(dashboard.best_ops.timeout); }if ($('#dashboard').css("opacity") == 1) {$('#best-ops .waiting').fadeOut(function() { $('#best-ops .updating').fadeIn(); });$('#best-opportunities').load("best-ops.php", function() { $("#best-ops .updating").fadeOut(function() { $('#best-ops .waiting').fadeIn(); }); dashboard.best_ops.timeout = setTimeout(function() {dashboard.best_ops.updatebest_ops();}, dashboard.best_ops.timeMS);});} else {dashboard.best_ops.timeout = setTimeout(function() {dashboard.best_ops.updatebest_ops();}, dashboard.best_ops.timeMS);}}$(document).ready(function() {return;dashboard.best_ops.updatebest_ops();});
var charts = new Object();charts.range = "-2 week";charts.intRange = "2";charts.hdwRange = "week";charts.display = "avg";charts.nomtgox = 0;charts.chartID = '#ftm-chart svg';charts.updateChart = function(){intSelect = $('#chart-int-range');dispSelect = $('#chart-hdw-range');intSelect.find('option:selected').prop('selected',false);intSelect.val(charts.intRange).prop('selected', true);dispSelect.find('option:selected').prop('selected',false);dispSelect.val(charts.hdwRange).prop('selected', true);charts.loadSVGChart();}charts.setRange = function(interval, hdw){charts.intRange = interval;charts.hdwRange = hdw;charts.range = "-"+interval+" "+hdw;}charts.setNoMtGox = function(){var nogox = $('#chart-nomtgox');charts.nomtgox = nogox.is(":checked") ? 1 : 0;}charts.sizeChart = function() {var cw = $('#charts').width();var ch = $('#charts').height();var dh = $('#charts h1').outerHeight();$('#ftm-chart').css({width: cw+'px', height: (ch-dh)+'px'});}charts.getJSONUrl = function(){var range = encodeURIComponent(charts.range);var disp = encodeURIComponent(charts.display);var gox = charts.nomtgox;return "test-chart-json.php?range="+range+"&disp="+disp+"&nomtgox="+gox;}charts.getDateFormat = function(){if (charts.hdwRange == 'hour') {return '%m/%d %H:%M';}if (charts.hdwRange == 'day') {return '%m/%d %H:%M';}return '%m/%d/%y';}charts.loadSVGChart = function(){$('#charts-overlay').fadeIn(200);d3.json(charts.getJSONUrl(), function(data) {nv.addGraph(function() {$('#charts-overlay').fadeOut(200);var chart = nv.models.lineChart().x(function(d) { return (d && d[0]) ? d[0]*1000 : 0 }).y(function(d) { return (d && d[1]) ? d[1] : 0 }) .color(d3.scale.category10().range()).useInteractiveGuideline(true);chart.xAxis.tickFormat(function(d) {return d3.time.format(charts.getDateFormat())(new Date(d))});chart.yAxis.tickFormat(function(d) { return "$" + d; });d3.select(charts.chartID).datum(data).transition().duration(500).call(chart);nv.utils.windowResize(chart.update);return chart;});});}charts.bindButtons = function() { $('#chart-int-range, #chart-hdw-range').change(function(){var intR = $("#chart-int-range option:selected").val();var intHDW = $("#chart-hdw-range option:selected").val();charts.setRange(intR, intHDW); }); $('#chart-display').change(function(){charts.display = $(this).find("option:selected").val(); }); $('#chart-nomtgox').click(function(e){charts.setNoMtGox(); }); $('#chart-submit').click(function(e){charts.updateChart();return noEvent(e); });}$(document).ready(function() {charts.bindButtons();charts.sizeChart();charts.updateChart();});
var markets = new Object();markets.timeout = null;markets.timeMS = 15000;markets.sleepMS = 2000;markets.updateMarketsLive = function(){if (markets.timeout) { clearTimeout(markets.timeout); }if ($('#markets').css("opacity") == 1) {$('#markets .waiting').fadeOut(function() { $('#markets .updating').fadeIn(); });$('#full-markets').load("full-markets.php", function() { $("#markets .updating").fadeOut(function() { $('#markets .waiting').fadeIn(); }); markets.timeout = setTimeout(function() {markets.updateMarkets();}, markets.timeMS);});} else {markets.timeout = setTimeout(function() {markets.updateMarkets();}, markets.timeMS);}}markets.updateMarkets = function(){var deltas = controls.json.deltas.markets; $.each(controls.json.markets, function(mname, mkt){var dlt = deltas[mname];mname = mname.replace("USD","");markets.updateMarketValue(mname, 'last', mkt.last);markets.updateMarketValue(mname, 'high', mkt.high);markets.updateMarketValue(mname, 'low', mkt.low);markets.updateMarketValue(mname, 'ask', mkt.ask);markets.updateMarketValue(mname, 'bid', mkt.bid);markets.updateMarketValue(mname, 'sma10', mkt.sma10);markets.updateMarketValue(mname, 'sma25', mkt.sma25);$('#mkt-volume-'+mname+' .val').html((mkt.volume > 0) ? mkt.volume.toFixed(6) : "--");markets.updateMarketPerc(mname, 'last', dlt.last.perc);markets.updateMarketPerc(mname, 'high', dlt.high.perc);markets.updateMarketPerc(mname, 'low', dlt.low.perc);markets.updateMarketPerc(mname, 'ask', dlt.ask.perc);markets.updateMarketPerc(mname, 'bid', dlt.bid.perc);markets.updateMarketPerc(mname, 'sma10', dlt.sma10.perc);markets.updateMarketPerc(mname, 'sma25', dlt.sma25.perc);markets.updateMarketPerc(mname, 'volume', dlt.volume.perc); });}markets.bindHoverState = function(){var mkt_cells = $("#markets").find("td, th");mkt_cells.on("mouseover", function() {var el = $(this),pos = el.index();el.parent().find("th, td").addClass("hover");mkt_cells.filter(":nth-child(" + (pos+1) + ")").addClass("hover");if (el.is('td')) { el.addClass("active"); }}).on("mouseout", function() {mkt_cells.removeClass("hover");mkt_cells.removeClass("active");}).on("click", function(e){var el = $(this), pos = el.index();var th = mkt_cells.filter("th.market:nth-child("+(pos+1)+")");if (th){var mname = th.attr('id').replace('market-th-','');orders.changeMarket(mname);controls.changeFtmState('orders');}return noEvent(e);});}markets.updateMarketValue = function(mname, valname, value){if (value > 0){ $('#mkt-'+valname+'-'+mname+' .val').html(controls.printCurrency(value, 'USD')); } else {$('#mkt-'+valname+'-'+mname+' .val').html('...'); }}markets.updateMarketPerc = function(mname, valname, value){var percVal = value.toFixed(3)+'%';var klass = (value > 0) ? 'pos' : (value < 0) ? 'neg' : 'neu';$('#mkt-'+valname+'-'+mname+' .perc').html("<span class='"+klass+"'><span class='market-perc-icon'></span>"+percVal+"</span>");}$(document).ready(function() {markets.bindHoverState();controls.addJSONListener(markets.updateMarkets);});
var transfer = new Object();transfer.frommarket = "Bitstamp";transfer.tomarket = "Bitfinex";transfer.setTransferMarkets = function(from, to){transfer.tomarket = to;transfer.frommarket = from;transfer.updateCapital();if (controls.json){transfer.updateTransfer();}}transfer.updateCapital = function(){ if (!transfer.frommarket || !transfer.tomarket) { return; }var from = (account.balances[transfer.frommarket]) ? account.balances[transfer.frommarket].btc : -1;var to = (account.balances[transfer.tomarket]) ? account.balances[transfer.tomarket].btc : -1;if (from != -1){$('#transfer-from-btc').html(controls.printCurrency(from, 'BTC'));} else {$('#transfer-from-btc').html(controls.printCurrency(0, 'BTC'));}if (to != -1){$('#transfer-to-btc').html(controls.printCurrency(to, 'BTC'));} else {$('#transfer-to-btc').html(controls.printCurrency(0, 'BTC'));}}transfer.updateVolume = function(){$('#transfer-volume-val').val(controls.volume);transfer.updateTransfer();}transfer.updateTransfer = function(){if (!transfer.frommarket || !transfer.tomarket || !controls.json) { return; }var fromSelect = $('#transfer-select-from');var toSelect = $('#transfer-select-to');fromSelect.find('option:selected').prop('selected',false);fromSelect.val(transfer.frommarket).prop('selected', true);toSelect.find('option:selected').prop('selected',false);toSelect.val(transfer.tomarket).prop('selected', true);var frommkt = controls.json.markets[transfer.frommarket];var tomkt = controls.json.markets[transfer.tomarket];var fromPrice = frommkt.bid;var toPrice = tomkt.bid;var fromWPrice = orderbooks.markets[transfer.frommarket].bidW;var toWPrice = orderbooks.markets[transfer.tomarket].bidW;var btcVol = parseFloat($('#transfer-volume-val').val());$('#transfer-from-bid').html(controls.printCurrency(fromWPrice, 'USD'));$('#transfer-to-bid').html(controls.printCurrency(toWPrice, 'USD'));var fromVal = btcVol * fromWPrice;var toVal = btcVol * toWPrice;$('#transfer-from-value').html(controls.printCurrency(fromVal, 'USD'));$('#transfer-to-value').html(controls.printCurrency(toVal, 'USD'));var spread = toWPrice - fromWPrice;var dVal = toVal - fromVal;$('#transfer-spread').html(controls.printCurrency(spread, 'USD'));$('#transfer-profit').html(controls.printCurrency(dVal, 'USD'));}transfer.beginTransfer = function(){var xfrBtn = $('#transfer-btn');if (controls.json){var fmkt = controls.json.markets[transfer.frommarket];var tmkt = controls.json.markets[transfer.tomarket];var btcVol = parseFloat($('#transfer-volume-val').val());var crypt = 'btc'; var opts = {cid: controls.client.cid,fmkt: transfer.frommarket,tmkt: transfer.tomarket,amt: btcVol,crypt: crypt,};xfrBtn.addClass('disabled');$.getJSON("ajax-transfer.php", opts, function(data) {if (data.success){account.balances[transfer.frommarket][crypt] = parseFloat(data.fmkt.bal); account.balances[transfer.tomarket][crypt] = parseFloat(data.tmkt.bal); $.growl.notice({title: "Success!",message: data.message});} else {$.growl.error({title: "Oh no!",message: data.message});}xfrBtn.removeClass('disabled');controls.updateBalance(); });}}transfer.initButtons = function(){$('#transfer-volume-val').on('keyup', function(e){controls.updateVolume($(this).val());});$('#transfer-select-from').change(function(e){var name = $(this).find('option:selected').val();transfer.setTransferMarkets(name, transfer.tomarket);});$('#transfer-select-to').change(function(e){var name = $(this).find('option:selected').val();transfer.setTransferMarkets(transfer.frommarket, name);});$('#transfer-btn').click(function(e){transfer.beginTransfer();return noEvent(e);})transfer.updateCapital();transfer.updateTransfer();}$(document).ready(function(){transfer.initButtons();controls.addJSONListener(transfer.updateTransfer);controls.addBalanceListener(function() {transfer.updateCapital();transfer.updateTransfer();});controls.addVolumeListener(transfer.updateVolume);controls.addOrderbookListener(transfer.updateTransfer);});
var matrix = new Object();matrix.timeout = null;matrix.timeMS = 15000;matrix.sleepMS = 2000;matrix.updateMatrixOld = function(){if (matrix.timeout) { clearTimeout(matrix.timeout); }if ($('#matrix').css("opacity") == 1) {$('#matrix .waiting').fadeOut(function() { $('#matrix .updating').fadeIn(); });$('#full-matrix').load("full-matrix.php", function() { $("#matrix .updating").fadeOut(function() { $('#matrix .waiting').fadeIn(); }); matrix.timeout = setTimeout(function() {matrix.updateMatrix();}, matrix.timeMS);});} else {matrix.timeout = setTimeout(function() {matrix.updateMatrix();}, matrix.timeMS);}}matrix.updateMatrix = function() {$.each(controls.json.mob, function(aname, amkt){aname = sanitizeMarketName(aname);$.each(amkt, function(bname, xchg){ var cell = $('#matrix-'+aname+'-'+bname);if (xchg != null){bname = sanitizeMarketName(bname);var klass = (xchg < 0) ? 'neg' : (xchg > 0) ? 'pos' : 'neu';var op = (klass == 'pos') ? 'has-op' : 'no-op';cell.find('.matrix-cell-value').html("<span class='"+klass+" "+op+"'>"+controls.printCurrency(xchg, "USD")+"</span>");var spread = controls.json.deltas.mob[aname][bname].spread;klass = (spread < 0) ? 'neg' : (spread > 0) ? 'pos' : 'neu';cell.find('.matrix-cell-perc').html("<span class='"+klass+"'><span class='matrix-perc-icon'></span>"+controls.printCurrency(spread, "USD")+"</span>");} else {cell.find('.matrix-cell-value').html("<span class='no-op'>...</span>");cell.find('.matrix-cell-perc').html('');}});});matrix.highlightOpportunities();}matrix.highlightOpportunities = function() { $.each(controls.json.mob, function(aname, amkt){aname = sanitizeMarketName(aname);$.each(amkt, function(bname, xchg){bname = sanitizeMarketName(bname);if (account.balances[aname].usd > 0 && account.balances[bname].btc > 0){var cell = $('#matrix-'+aname+'-'+bname);if (cell.find('span').hasClass('has-op')){cell.addClass('highlight');cell.on('click', function(e){var askName = $(this).attr('data-ask');var bidName = $(this).attr('data-bid');arbitrage.setArbitrageMarkets(askName, bidName);controls.changeFtmState('arbitrage');return noEvent(e);})} else {cell.removeClass('highlight');cell.off('click');}} else {$('#matrix-'+aname+'-'+bname).removeClass('highlight');}}); });}$(document).ready(function() {controls.addBalanceListener(matrix.updateMatrix);controls.addJSONListener(matrix.updateMatrix); });
var orderbooks = new Object();orderbooks.markets = new Array();orderbooks.updateOrderbooks = function(){setInterval(function(){orderbooks.getOrderbooksUpdate();}, controls.orderbookInt);}orderbooks.getOrderbooksUpdate = function(){$('#orderbooks-data').load('ajax-orderbooks.php', function(){orderbooks.updateMarketDepth();});}orderbooks.updateMarketDepth = function(){var mkts = $('#orderbooks .marketname');mkts.each(function(i){var mname = $(this).attr('id').replace('orderbooks-marketname-','');orderbooks.highlightColumn(mname, 'ask');orderbooks.highlightColumn(mname, 'bid');});}orderbooks.highlightColumn = function(mname, type){var volcol = $('.'+type+'-list-volume-'+mname);var vols = volcol.find('.orderbook-list-item');var rows = 0;var total = 0;var mVol = 0;var volsArray = new Array();var btcVol = $('#orderbooks-btcvol').val();vols.removeClass('highlight');vols.each(function(i){var v = parseFloat($(this).attr('data-volume'));if (total < btcVol){volsArray.push(v);$(this).addClass('highlight');total += v;rows = i;}mVol += v;if (i == vols.length-1){ var priceArray = new Array();var pricecol = $('.'+type+'-list-price-'+mname);var prices = pricecol.find('.orderbook-list-item');prices.removeClass('highlight');prices.each(function(j){if (j <= rows){var p = parseFloat($(this).attr('data-price'));priceArray.push(p);$(this).addClass('highlight');} if (j == prices.length-1){var totalVol = 0;var wVal = 0;for (k = 0; k < volsArray.length && k < priceArray.length; k++){wVal += volsArray[k] * priceArray[k];totalVol += volsArray[k];}var wTotal = (totalVol > 0) ? wVal / totalVol : 0;$('#'+type+'-list-wval-'+mname).html(controls.printCurrency(wTotal, 'USD'));orderbooks.markets[mname][type+'W'] = wTotal;orderbooks.markets[mname][type+'Vol'] = mVol;controls.updateOrderbooks();}});}});}orderbooks.updateVolume = function(){$('#orderbooks-btcvol').val(controls.volume);orderbooks.updateMarketDepth();}orderbooks.toggleMarket = function(el){var checked = $(el).is(":checked");var mname = $(el).attr('id').replace('orderbook-toggle-', '');var askVal = $('.asks-list-'+mname+' .list-wval');var bidVal = $('.bids-list-'+mname+' .list-wval');var mktcol = $('.orderbook-list-wrapper-'+mname);if (checked){askVal.show();bidVal.show();mktcol.fadeIn(200);} else {askVal.hide();bidVal.hide();mktcol.fadeOut(200);}}orderbooks.initOrderbooks = function(){var mkts = $('#orderbooks .marketname');mkts.each(function(i){var mname = $(this).attr('id').replace('orderbooks-marketname-','');orderbooks.markets[mname] = {askW: 0,bidW: 0,askVol: 0,bidVol: 0};});orderbooks.setupButtons();orderbooks.updateVolume();orderbooks.updateOrderbooks();}orderbooks.setupButtons = function(){$('#orderbooks-btcvol').on('keyup', function(e){controls.updateVolume($(this).val());});$('.orderbook-toggle').click(function(e){orderbooks.toggleMarket(this);});}$(document).ready(function(){orderbooks.initOrderbooks();controls.addVolumeListener(orderbooks.updateVolume);});
var orders = new Object();orders.market = "MtGox";orders.ordertype = "market";orders.buyprice = -1;orders.sellprice = -1;orders.changeMarket = function(mname){orders.market = mname;$('#buysell-bitcoin-markets li').removeClass('active')$('#buysell-btcmarket-'+mname).addClass('active');if (!account.hasCapitalAtMarket(orders.market)){$('#buysell-not-available').stop().fadeIn('fast');} else {$('#buysell-not-available').stop().fadeOut('fast');}if (orders.ordertype == 'limit'){$('#order-limit-price').show();$('.order-limit').show();$('.order-value').hide();$('#order-vol-price').addClass('limit');} else {$('#order-limit-price').hide();$('.order-limit').hide();$('.order-value').show();$('#order-vol-price').removeClass('limit');}orders.updateCapital();if (controls.json){orders.updateBuySell();}orders.setButtonStates();}orders.changeOrderType = function(otype){if (otype != orders.ordertype){orders.ordertype = otype;$('#buysell .order-type-button').removeClass('active');$('#order-type-'+otype).addClass('active');if (orders.ordertype == 'limit'){$('#order-limit-price').fadeIn('fast');$('.order-value').hide();$('.order-limit').fadeIn('fast');$('#order-vol-price').addClass('limit');} else {$('#order-limit-price').fadeOut('fast');$('.order-limit').hide();$('.order-value').fadeIn('fast');$('#order-vol-price').removeClass('limit');}}}orders.updateBuySell = function(){if (controls.json){if (orders.ordertype == 'market') {orders.updateMarketBuySell();} else if (orders.ordertype == 'limit') {orders.updateLimitBuySell();}}}orders.updateMarketBuySell = function(){var mkt = controls.json.markets[orders.market];var askPrice = mkt.ask;var bidPrice = mkt.bid;var btcVol = parseFloat($('#order-volume-val').val());var com = mkt.commission + controls.honey;$('#order-ask-value').html(controls.printCurrency(askPrice, 'USD'));$('#order-bid-value').html(controls.printCurrency(bidPrice, 'USD'));var buyComValue = 0;var sellComValue = 0;if (!isNaN(btcVol)){var buyTotalPreCom = askPrice * btcVol;var sellTotalPreCom = bidPrice * btcVol;var buyComValue = com * buyTotalPreCom;var sellComValue = com * sellTotalPreCom;var buyTotal = buyTotalPreCom - buyComValue;var sellTotal = sellTotalPreCom- sellComValue;$('#buy-button .order-commission-value').html('-$'+buyComValue.toFixed(4)+' ('+controls.printCommission(com)+')');$('#sell-button .order-commission-value').html('-$'+sellComValue.toFixed(4)+' ('+controls.printCommission(com)+')');$('#order-buy-total').html('-$'+buyTotal.toFixed(4));$('#order-sell-total').html('+$'+sellTotal.toFixed(4));orders.setButtonStates();} else {$('#buy-button .order-commission-value').html('... ('+controls.printCommission(com)+')');$('#sell-button .order-commission-value').html('... ('+controls.printCommission(com)+')');$('#order-buy-total').html('...');$('#order-sell-total').html('...');}}orders.updateLimitBuySell = function(){var mkt = controls.json.markets[orders.market];var btcVol = parseFloat($('#order-volume-val').val());var limitPrice = parseFloat($('#order-limit-price-val').val());var com = mkt.commission + controls.honey;if (!isNaN(btcVol) && !isNaN(limitPrice)){var buyTotalPreCom = limitPrice * btcVol;var sellTotalPreCom = limitPrice * btcVol;var buyComValue = com * buyTotalPreCom;var sellComValue = com * sellTotalPreCom;var buyTotal = buyTotalPreCom + buyComValue; var sellTotal = sellTotalPreCom- sellComValue;$('.order-limit-value').html('$'+limitPrice);$('#buy-button .order-commission-value').html(controls.printCurrency(-buyComValue, 'USD')+' ('+controls.printCommission(com)+')');$('#sell-button .order-commission-value').html(controls.printCurrency(-sellComValue, 'USD')+' ('+controls.printCommission(com)+')');$('#order-buy-total').html(controls.printCurrency(-buyTotal, 'USD'));$('#order-sell-total').html('+'+controls.printCurrency(sellTotal, 'USD'));orders.setButtonStates();} else {$('.order-limit-value').html('...');$('#buy-button .order-commission-value').html('... ('+controls.printCommission(com)+')');$('#sell-button .order-commission-value').html('... ('+controls.printCommission(com)+')');$('#order-buy-total').html('...');$('#order-sell-total').html('...');}}orders.updateCapital = function(){if (account.balances[orders.market]){var usd = account.balances[orders.market].usd;var btc = account.balances[orders.market].btc;if (usd != -1 && btc != -1){$('#order-capital-usd').html(controls.printCurrency(usd, 'USD'));$('#order-capital-btc').html(controls.printCurrency(btc, 'BTC'));} else {$('#order-capital-usd').html(controls.printCurrency(0, 'USD'));$('#order-capital-btc').html(controls.printCurrency(0, 'BTC'));}}}orders.placeOrder = function(buysell){var bsBtn = $('#'+buysell+'-button');bsBtn.addClass('disabled');if (orders.ordertype == 'limit') {$.growl.notice("Limit orders coming soon");bsBtn.removeClass('disabled');} else {var mkt = controls.json.markets[orders.market];var price = 0;if (buysell == 'buy') {price = mkt.ask;} else if (buysell == 'sell') {price = mkt.bid;}var btcVol = parseFloat($('#order-volume-val').val());var opts = {cid: controls.client.cid,mkt: orders.market,amt: btcVol,val: price,crypt: 'BTC',fiat: 'USD',action: buysell};$.getJSON("ajax-market-buysell.php", opts, function(data) {if (data.success){account.balances[orders.market].usd = parseFloat(data.usd); account.balances[orders.market].btc = parseFloat(data.btc); $.growl({title: "Success",message: data.message});} else {$.growl.error({title: "Oh noes!",message: data.message});}bsBtn.removeClass('disabled');controls.updateBalance(); });}}orders.updateVolume = function(){$('#order-volume-val').val(controls.volume);orders.updateBuySell();}orders.setButtonStates = function(){if (controls.json) {var mkt = controls.json.markets[orders.market];var usd = account.balances[orders.market].usd;var btc = account.balances[orders.market].btc;var btcVol = parseFloat($('#order-volume-val').val());var limitPrice = parseFloat($('#order-limit-price-val').val());if (orders.ordertype == 'limit') {if (btc < btcVol) {$('#sell-button').addClass('disabled');} else {$('#sell-button').removeClass('disabled');}if (usd < btcVol*limitPrice) {$('#buy-button').addClass('disabled');} else {$('#buy-button').removeClass('disabled');}} else if (orders.ordertype == 'market') {var askPrice = mkt.ask;var bidPrice = mkt.bid;if (btc < btcVol) {$('#sell-button').addClass('disabled');} else {$('#sell-button').removeClass('disabled');}if (usd < btcVol*askPrice) {$('#buy-button').addClass('disabled');} else {$('#buy-button').removeClass('disabled');}}if (btc == -1 || usd == -1){$('#buy-button').addClass('disabled');$('#sell-button').addClass('disabled');}} else {$('#buy-button').addClass('disabled');$('#sell-button').addClass('disabled');}}orders.initButtons = function(){var mktbuttons = $('#buysell-bitcoin-markets li');mktbuttons.click(function(e){var mname = $(this).attr('id').replace('buysell-btcmarket-','');orders.changeMarket(mname);return noEvent(e);});mktbuttons.find('a').click(function(e){$(this).parent('li').click();return noEvent(e);});var orderbuttons = $('#buysell .order-type-button');orderbuttons.click(function(e){var ordertype = $(this).attr('id').replace('order-type-','');orders.changeOrderType(ordertype);return noEvent(e);});$('#order-volume-val').on('keyup', function(e){controls.updateVolume($(this).val());});$('#order-limit-price-val').on('keyup', function(e){orders.updateBuySell();});$('#buy-button').click(function() {if (!$(this).hasClass('disabled')){orders.placeOrder('buy');}});$('#sell-button').click(function() {if (!$(this).hasClass('disabled')){orders.placeOrder('sell');}});orders.setButtonStates();}$(document).ready(function() {orders.initButtons();orders.changeMarket("MtGox");controls.addBalanceListener(function(){orders.updateCapital();orders.updateBuySell();})controls.addJSONListener(orders.updateBuySell);controls.addVolumeListener(orders.updateVolume);controls.addOrderbookListener(orders.updateBuySell);});
$(document).ready(function(){});

$(document).ready(function(){});
$(document).ready(function(){});
var arbitrage = new Object();arbitrage.askmarket = "Bitstamp";arbitrage.bidmarket = "Bitfinex";arbitrage.setArbitrageMarkets = function(aname, bname){arbitrage.askmarket = aname;arbitrage.bidmarket = bname;arbitrage.updateCapital();if (controls.json){arbitrage.updateArbitage();}}arbitrage.updateCapital = function(){var usd = (arbitrage.askmarket) ? account.balances[arbitrage.askmarket].usd : -1;var btc = (arbitrage.bidmarket) ? account.balances[arbitrage.bidmarket].btc : -1;if (usd != -1){$('#arbitrage-capital-usd').html(controls.printCurrency(usd, 'USD'));} else {$('#arbitrage-capital-usd').html(controls.printCurrency(0, 'USD'));}if (btc != -1){$('#arbitrage-capital-btc').html(controls.printCurrency(btc, 'BTC'));} else {$('#arbitrage-capital-btc').html(controls.printCurrency(0, 'BTC'));}$('#arbitrage-capital .ask-market-name').html(arbitrage.askmarket);$('#arbitrage-capital .bid-market-name').html(arbitrage.bidmarket);$('#arbitrage-ask-market').html(arbitrage.askmarket);$('#arbitrage-sell-market').html(arbitrage.bidmarket);}arbitrage.updateArbitage = function(){if (!controls.json || !arbitrage.askmarket || !arbitrage.bidmarket) { return; }var buySelect = $('#arbitrage-select-buy');var sellSelect = $('#arbitrage-select-sell');buySelect.find('option:selected').prop('selected',false);buySelect.val(arbitrage.askmarket).prop('selected', true);sellSelect.find('option:selected').prop('selected',false);sellSelect.val(arbitrage.bidmarket).prop('selected', true);var amkt = controls.json.markets[arbitrage.askmarket];var bmkt = controls.json.markets[arbitrage.bidmarket];var askPrice = amkt.ask;var bidPrice = bmkt.bid;var acom = amkt.commission + controls.honey;var bcom = bmkt.commission + controls.honey;var btcVol = parseFloat($('#arbitrage-volume-val').val());$('#arbitrage-ask-value').html(controls.printCurrency(askPrice, 'USD'));$('#arbitrage-bid-value').html(controls.printCurrency(bidPrice, 'USD'));var buyComValue = 0;var sellComValue = 0;if (!isNaN(btcVol)){var buyTotalPreCom = askPrice * btcVol;var sellTotalPreCom = bidPrice * btcVol;var buyComValue = acom * buyTotalPreCom;var sellComValue = bcom * sellTotalPreCom;var buyTotal = buyTotalPreCom + buyComValue;var sellTotal = sellTotalPreCom- sellComValue;var estProfit = sellTotal - buyTotal;var askComPrice = (acom*askPrice) + askPrice;var usd = account.balances[arbitrage.askmarket].usd;var btc = account.balances[arbitrage.bidmarket].btc;var usd2btc = usd / askComPrice;var maxBtcVolume = Math.min(usd2btc, btc);$('#arbitrage-max-btc').html(controls.printCurrency(maxBtcVolume, "BTC"));$('#arbitrage-max-usd').html(controls.printCurrency(maxBtcVolume*askComPrice, "USD"));$('#arbitrage-buy-info .arbitrage-commission-value').html('-'+controls.printCurrency(buyComValue, 'USD')+' ('+controls.printCommission(acom)+')');$('#arbitrage-sell-info .arbitrage-commission-value').html('-'+controls.printCurrency(sellComValue, 'USD')+' ('+controls.printCommission(bcom)+')');$('#arbitrage-buy-total').html('-'+controls.printCurrency(buyTotal, 'USD'));$('#arbitrage-sell-total').html('+'+controls.printCurrency(sellTotal, 'USD'));$('#arbitrage-profit-usd').html(controls.printCurrency(estProfit, 'USD'));$('#arbitrage-profit-btc').html(controls.printCurrency(btcVol, 'BTC'));} else {$('#arbitrage-buy-info .arbitrage-commission-value').html('... ('+controls.printCommission(acom)+')');$('#arbitrage-sell-info .arbitrage-commission-value').html('... ('+controls.printCommission(bcom)+')');$('#arbitrage-buy-total').html('...');$('#arbitrage-sell-total').html('...');}arbitrage.setButtonStates();}arbitrage.setButtonStates = function(){var arbBtn = $('#arbitrage-btn');var arbBuy = $('#arbitrage-buy-info');var arbSell = $('#arbitrage-sell-info');if (controls.json) {var amkt = controls.json.markets[arbitrage.askmarket];var bmkt = controls.json.markets[arbitrage.bidmarket];var usd = account.balances[arbitrage.askmarket].usd;var btc = account.balances[arbitrage.bidmarket].btc;var btcVol = parseFloat($('#arbitrage-volume-val').val());var askPrice = amkt.ask;var bidPrice = bmkt.bid;if(btc < btcVol) {arbSell.addClass('disabled');} else {arbSell.removeClass('disabled');}if (usd < btcVol*askPrice) {arbBuy.addClass('disabled');} else {arbBuy.removeClass('disabled');}if (btc == -1 || usd == -1){arbBuy.addClass('disabled');arbSell.addClass('disabled');}if(!arbBuy.hasClass('disabled') && !arbSell.hasClass('disabled')){arbBtn.removeClass('disabled');arbBtn.find('.arbitrage-click-label').html('Click to begin Arbitrage');} else {arbBtn.addClass('disabled');arbBtn.find('.arbitrage-click-label').html('Insufficient funds for Arbitrage');}} else {arbBtn.addClass('disabled');arbBuy.addClass('disabled');arbSell.addClass('disabled');}}arbitrage.beginArbitrage = function(){var arbBtn = $('#arbitrage-btn');if (controls.json){var amkt = controls.json.markets[arbitrage.askmarket];var bmkt = controls.json.markets[arbitrage.bidmarket];var askPrice = amkt.ask;var bidPrice = bmkt.bid;var btcVol = parseFloat($('#arbitrage-volume-val').val());var opts = {cid: controls.client.cid,amkt: arbitrage.askmarket,bmkt: arbitrage.bidmarket,amt: btcVol,ask: askPrice,bid: bidPrice,crypt: 'BTC',fiat: 'USD',};arbBtn.addClass('disabled');$.getJSON("ajax-arbitrage.php", opts, function(data) {if (data.success){account.balances[arbitrage.askmarket].usd = parseFloat(data.amkt.usd); account.balances[arbitrage.askmarket].btc = parseFloat(data.amkt.btc); account.balances[arbitrage.bidmarket].usd = parseFloat(data.bmkt.usd); account.balances[arbitrage.bidmarket].btc = parseFloat(data.bmkt.btc); $.growl.notice({title: "Success!",message: data.message});} else {$.growl.error({title: "Oh no!",message: data.message});}arbBtn.removeClass('disabled');controls.updateBalance(); });}}arbitrage.updateVolume = function(){$('#arbitrage-volume-val').val(controls.volume);arbitrage.updateArbitage();}arbitrage.initButtons = function(){$('#arbitrage-volume-val').on('keyup', function(e){controls.updateVolume($(this).val());});$('#arbitrage-select-buy').change(function(e){var aname = $(this).find('option:selected').val();arbitrage.setArbitrageMarkets(aname, arbitrage.bidmarket);});$('#arbitrage-select-sell').change(function(e){var bname = $(this).find('option:selected').val();arbitrage.setArbitrageMarkets(arbitrage.askmarket, bname);});$('#return-to-the-matrix a').click(function(e){controls.changeFtmState('matrix');return noEvent(e);});$('#arbitrage-btn').click(function(e){arbitrage.beginArbitrage();return noEvent(e);})}$(document).ready(function(){arbitrage.initButtons();controls.addJSONListener(arbitrage.updateArbitage);controls.addBalanceListener(function() {arbitrage.updateCapital();arbitrage.updateArbitage();});controls.addVolumeListener(arbitrage.updateVolume);controls.addOrderbookListener(arbitrage.updateArbitage);});