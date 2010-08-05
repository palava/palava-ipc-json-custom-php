function calc_height() {
	$('#ex__wrapper > div').each(function(i,e){
		if ($(e).outerHeight() >= $('#ex__wrapper').height()) {
			$(e).css({overflowY: 'scroll', height: $('#ex__wrapper').height() - 40});
		} else {
			$(e).css({overflowY: 'hidden', height: $('#ex__wrapper').height() - 40});
		}
	});
};

function set_cookie(name, value) {
	document.cookie = name + '=' + value;
	document.cookie = 'expires=' + (new Date()).getTime() + (60 * 60 * 24 * 3); // 3 Tage
}

function set_open() {
	open = '';
	$('#ex__left ul:visible li > ul:visible').each(function(){
		open += ' ' + $(this).attr('id');
	});
	set_cookie('open',open);
}

$(document).ready(function(){
	calc_height();
	
	// resizing
	$(window).resize(function(){calc_height();});
	
	// click-slide
	$('#ex__left span').live('click', function(){
		$(this).nextAll('ul').slideToggle('normal',function(){
			calc_height();
			set_open();
		});
	});
	
	// toggle all
	$('#ex__left .toggle').live('click', function() {
		if ($(this).hasClass('show')) {
			$(this).text('show all');
			$('#ex__left ul ul').hide();
		} else {
			$(this).text('hide all');
			$('#ex__left ul ul').show();
		}
		set_open();
		$(this).toggleClass('show');
		return false;
	});

    if ($('#ex__left ul ul:visible').length) {
        $('#ex__left .toggle').text('hide all').toggleClass('show');
    }
	
	// sandbox
	$('#ex__sandbox h2').live('click', function() {
		if ($('#ex__sandbox').hasClass('expanded')) {
			$('#ex__sandbox').animate({height: '35px'});
			$('#ex__sandbox form .submit').fadeOut();
		} else {
			$('#ex__sandbox').animate({height: '75%'});
			$('#ex__sandbox form .submit').fadeIn();
		}
		$('#ex__sandbox').toggleClass('expanded');
		set_cookie('sandbox_expanded', $('#ex__sandbox').attr('class'));
	});

    $('#ex__sandbox form').live('submit', function(){
        var params = $('#ex__sandbox .parameters textarea').val();
        $.post(window.location, {ajax: 'runCommand', parameters: params}, function(result){
            $('#ex__sandbox .returns textarea').val(result);
        });
        return false;
    });
});