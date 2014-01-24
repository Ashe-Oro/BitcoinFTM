var controls = new Object();
controls.ftmState = "dashboard";
controls.json = null;
controls.jsonInt = 10000;

$(document).ready(function(){
	updateMasterJSON();
	bindSidebarMenu();
	bindAccountMenu();

	$('#main-content .content').each(function(){
		if ($(this).attr('id') == controls.ftmState) {
			$(this).removeClass('hide');
		} else {
			$(this).addClass('hide');
		}
	});
});

function updateMasterJSON()
{
	setInterval(function(){
		$.getJSON("json/master.json", function( data ) {
			controls.json = data;
			alert('json updated');
		});
	}, controls.jsonInt);
}

function changeFtmState(state)
{
	if (controls.ftmState != state){
		$('#'+controls.ftmState).addClass('hide');
		controls.ftmState = state;
		$('#'+controls.ftmState).removeClass('hide');
	}
}

function bindSidebarMenu()
{
	$('#sidebar li').click(function(e) {
		if ($(this).hasClass('active')) { return; }

		var newState = $(this).attr('class');

		$('#sidebar li.active').removeClass('active');
		$(this).addClass('active');

		changeFtmState(newState);
		
		e.preventDefault();
		e.stopPropagation();
		return false;
	});

	$('#sidebar li a').click(function(e){
		return false;
	});
}

function bindAccountMenu()
{
	$('#header .account li').click(function(e) {
		if ($(this).hasClass('signout')) { 
			window.location.href = $(this).find('a').attr('href');
			e.preventDefault();
			e.stopPropagation();
			return false;
		}

		if (!$(this).hasClass('active')) {
			var newState = $(this).attr('class');

			$('#sidebar li.active').removeClass('active');
			$(this).addClass('active');

			changeFtmState(newState);
		}
		
		e.preventDefault();
		e.stopPropagation();
		return false;
	});

	$('#header .account li a').click(function(e){
		if ($(this).parent().hasClass('signout')){
			return true;
		} else {
			return false;
		}
	});
}