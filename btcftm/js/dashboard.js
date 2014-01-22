var dashboard = new Object();
dashboard.bestops = new Object();

dashboard.bestops.timeout = null;
dashboard.bestops.timeMS = 15000;
dashboard.bestops.sleepMS = 2000;

dashboard.bestops.updateBestOps = function()
{
  if (dashboard.bestops.timeout) { clearTimeout(dashboard.bestops.timeout); }
  if ($('#dashboard').css("opacity") == 1) {
    $('#best-ops .waiting').fadeOut(function() { $('#best-ops .updating').fadeIn(); });
    $('#best-opportunities').load("best-ops.php", function() { 
      $("#best-ops .updating").fadeOut(function() { 
        $('#best-ops .waiting').fadeIn(); 
      }); 
      dashboard.bestops.timeout = setTimeout(function() {
        dashboard.bestops.updateBestOps();
      }, dashboard.bestops.timeMS);
    });
  } else {
    dashboard.bestops.timeout = setTimeout(function() {
      dashboard.bestops.updateBestOps();
    }, dashboard.bestops.timeMS);
  }
}

$(document).ready(function() {
	dashboard.bestops.updateBestOps();
});

