<?php
$starttime = isset($_GET['start']) ? strtotime($_GET['start']) : strtotime("-14 day");
$endtime = isset($_GET['end']) ? strtotime($_GET['end']) : time();
?>
<html>
<head>
<title>Test Chart</title>
<script language="javascript" src="jquery/jquery-1.8.2.js"></script>
<script language="javascript" src="jquery/d3-master/d3.min.js"></script>
<script language="javascript" src="jquery/nvd3/nv.d3.js"></script>
<link href="jquery/nvd3/nv.d3.css" rel="stylesheet" type="text/css" />
<script language="javascript">
d3.json("test-chart-json.php?start=<?php echo $starttime; ?>&end=<?php echo $endtime; ?>", function(data) {
  nv.addGraph(function() {
    var chart = nv.models.lineChart()
                .x(function(d) { return (d && d[0]) ? d[0]*1000 : 0 })
                .y(function(d) { return (d && d[1]) ? d[1] : 0 }) //adjusting, 100% is 1.00, not 100 as it is in the data
                .color(d3.scale.category10().range())
                .useInteractiveGuideline(true)
                ;

  chart.xAxis
      .axisLabel("Date")
      .tickFormat(function(d) {
        return d3.time.format('%x')(new Date(d))
      });

  chart.yAxis
      .axisLabel("USD per Bitcoin")
      .tickFormat(function(d) { return "$" + d; });

  d3.select('#chart svg')
      .datum(data)
    .transition().duration(500)
      .call(chart);

  nv.utils.windowResize(chart.update);

  return chart;
  });
});

</script>

<style type="text/css">
html, body {
  margin: 0;
  width: 100%;
  height: 100%;
}
#chart svg {
  height: 100%;
}

.nv-legendWrap {
  background-color: #fff;
}

</style>
</head>


<body>

<div id="chart">
  <svg></svg>
</div>

</body>

</html>