var matrix = new Object();
matrix.timeout = null;
matrix.timeMS = 15000;
matrix.sleepMS = 2000;

matrix.updateMatrixOld = function()
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

matrix.updateMatrix = function() 
{
  $.each(controls.json.mob, function(aname, amkt){
    $.each(amkt, function(bname, xchg){
      var klass = (xchg < 1) ? 'neg' : (xchg > 1) ? 'pos' : 'neu';
      $('#matrix-'+aname+'-'+bname).html("<span class='"+klass+"'>"+xchg.toFixed(4)+"</span>");
    });
  });
}

$(document).ready(function() {
	controls.addJSONListener(matrix.updateMatrix);
 // matrix.updateMatrix();
});

