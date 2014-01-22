var markets = new Object();
markets.timeout = null;
markets.timeMS = 15000;
markets.sleepMS = 2000;

markets.updateMarkets = function()
{
  if (markets.timeout) { clearTimeout(markets.timeout); }
  if ($('#markets').css("opacity") == 1) {
    $('#markets .waiting').fadeOut(function() { $('#markets .updating').fadeIn(); });
    $('#full-markets').load("full-markets.php", function() { 
      $("#markets .updating").fadeOut(function() { 
        $('#markets .waiting').fadeIn(); 
      }); 
      markets.timeout = setTimeout(function() {
        markets.updateMarkets();
      }, markets.timeMS);
    });
  } else {
    markets.timeout = setTimeout(function() {
      markets.updateMarkets();
    }, markets.timeMS);
  }
}

$(document).ready(function() {
	markets.updateMarkets();
});

