var matrix = new Object();
matrix.timeout = null;
matrix.timeMS = 15000;
matrix.sleepMS = 2000;

matrix.updateMatrix = function()
{
  if (matrix.timeout) { clearTimeout(matrix.timeout); }
  if ($('#matrix').css("opacity") == 1) {
    $('#matrix .waiting').fadeOut(function() { $('#matrix .updating').fadeIn(); });
    $('#full-matrix').load("full-matrix.php", function() { 
      $("#matrix .updating").fadeOut(function() { 
        $('#matrix .waiting').fadeIn(); 
      }); 
      matrix.timeout = setTimeout(function() {
        matrix.updateMatrix();
      }, matrix.timeMS);
    });
  } else {
    matrix.timeout = setTimeout(function() {
      matrix.updateMatrix();
    }, matrix.timeMS);
  }
}

$(document).ready(function() {
	matrix.updateMatrix();
});

