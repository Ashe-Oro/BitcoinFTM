var dashboard = new Object();
dashboard.best_ops = new Object();

dashboard.best_ops.timeout = null;
dashboard.best_ops.timeMS = 15000;
dashboard.best_ops.sleepMS = 2000;

dashboard.best_ops.updatebest_ops = function()
{
  if (dashboard.best_ops.timeout) { clearTimeout(dashboard.best_ops.timeout); }
  if ($('#dashboard').css("opacity") == 1) {
    $('#best-ops .waiting').fadeOut(function() { $('#best-ops .updating').fadeIn(); });
    $('#best-opportunities').load("best-ops.php", function() { 
      $("#best-ops .updating").fadeOut(function() { 
        $('#best-ops .waiting').fadeIn(); 
      }); 
      dashboard.best_ops.timeout = setTimeout(function() {
        dashboard.best_ops.updatebest_ops();
      }, dashboard.best_ops.timeMS);
    });
  } else {
    dashboard.best_ops.timeout = setTimeout(function() {
      dashboard.best_ops.updatebest_ops();
    }, dashboard.best_ops.timeMS);
  }
};

$(document).ready(function() {
	return;
  dashboard.best_ops.updatebest_ops();
});

