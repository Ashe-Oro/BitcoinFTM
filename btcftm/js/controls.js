var ftmState = "dashboard";

$(document).ready(function(){
	bindSidebarMenu();
	bindAccountMenu();

	$('#main-content .content').each(function(){
		if ($(this).attr('id') == ftmState) {
			$(this).removeClass('hide');
		} else {
			$(this).addClass('hide');
		}
	});
});

function changeFtmState(state)
{
	if (ftmState != state){
		$('#'+ftmState).addClass('hide');
		ftmState = state;
		$('#'+ftmState).removeClass('hide');
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