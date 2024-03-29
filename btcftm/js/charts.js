var charts = new Object();
charts.range = "-2 week";
charts.intRange = "2";
charts.hdwRange = "week";
charts.display = "avg";
charts.nomtgox = 1;
charts.chartID = '#ftm-chart svg';
//charts.bitwisdom = "http://bitcoinwisdom.com/markets/";
//charts.market = "MtGoxUSD";

charts.updateChart = function()
{
  intSelect = $('#chart-int-range');
  dispSelect = $('#chart-hdw-range');

  intSelect.find('option:selected').prop('selected',false);
  intSelect.val(charts.intRange).prop('selected', true);
  dispSelect.find('option:selected').prop('selected',false);
  dispSelect.val(charts.hdwRange).prop('selected', true);

  charts.loadSVGChart();
};

charts.setRange = function(interval, hdw)
{
  charts.intRange = interval;
  charts.hdwRange = hdw;
  charts.range = "-"+interval+" "+hdw;
  //charts.updateChart();
};

charts.setNoMtGox = function()
{
  var nogox = $('#chart-nomtgox');
  charts.nomtgox = nogox.is(":checked") ? 1 : 0;
};


charts.sizeChart = function() {
  var cw = $('#charts').width();
  var ch = $('#charts').height();

  var dh = $('#charts h1').outerHeight();
  $('#ftm-chart').css({width: cw+'px', height: (ch-dh)+'px'});
  //var dh = $('#bitcoin-markets').outerHeight() + $('#charts h1').outerHeight();
  //$('#bitcoin-chart iframe').css({width: cw+'px', height: (ch-dh)+'px'});
};

charts.getJSONUrl = function()
{
  var range = encodeURIComponent(charts.range);
  var disp = encodeURIComponent(charts.display);
  var gox = charts.nomtgox;
  return "test-chart-json.php?range="+range+"&disp="+disp+"&nomtgox="+gox;
};

charts.getDateFormat = function()
{
  if (charts.hdwRange == 'hour') {
    return '%m/%d %H:%M';
  }
  if (charts.hdwRange == 'day') {
    return '%m/%d %H:%M';
  }
  return '%m/%d/%y';
};

charts.loadSVGChart = function()
{
  $('#charts-overlay').fadeIn(200);

  d3.json(charts.getJSONUrl(), function(data) {
    nv.addGraph(function() {
      $('#charts-overlay').fadeOut(200);

      var chart = nv.models.lineChart()
                  .x(function(d) { return (d && d[0]) ? d[0]*1000 : 0 })
                  .y(function(d) { return (d && d[1]) ? d[1] : 0 }) 
                  .color(d3.scale.category10().range())
                  .useInteractiveGuideline(true)
                  ;

    chart.xAxis
        .tickFormat(function(d) {
          return d3.time.format(charts.getDateFormat())(new Date(d))
        });

    chart.yAxis
        .tickFormat(function(d) { return "$" + d; });

    d3.select(charts.chartID)
        .datum(data)
      .transition().duration(500)
        .call(chart);

    nv.utils.windowResize(chart.update);

    return chart;
    });
  });
};

charts.bindButtons = function() {
 $('#chart-int-range, #chart-hdw-range').change(function(){
  var intR = $("#chart-int-range option:selected").val();
  var intHDW = $("#chart-hdw-range option:selected").val();
  charts.setRange(intR, intHDW);
 });

 $('#chart-display').change(function(){
  charts.display = $(this).find("option:selected").val();
  //charts.updateChart();
 });

 $('#chart-nomtgox').click(function(e){
  charts.setNoMtGox();
 });

 $('#chart-submit').click(function(e){
  charts.updateChart();
  return noEvent(e);
 });
};

$(document).ready(function() {
  charts.bindButtons();
  charts.sizeChart();
  //charts.bindChartLinks();
	//charts.showChart();
  charts.updateChart();
});

