function calc_height() {
	$('#ex__wrapper > div').each(function(i,e){
		if ($(e).outerHeight() >= $('#ex__wrapper').height()) {
			$(e).css({overflowY: 'scroll', height: $('#ex__wrapper').height() - 40});
		} else {
			$(e).css({overflowY: 'hidden', height: $('#ex__wrapper').height() - 40});
		}
	});

    update_toggle();
};

function update_toggle() {
    var toggle = $('#ex__left .toggle');
    
    if ($('#ex__left ul:visible li > ul:visible').length) {
        toggle.text('hide all');
    } else {
        toggle.text('show all');
    }
}

function toggle_sandbox() {
    if ($('#ex__sandbox').hasClass('expanded')) {
        $('#ex__sandbox').animate({height: '35px'});
        $('#ex__sandbox form .submit').fadeOut();
    } else {
        $('#ex__sandbox').animate({height: '75%'});
        $('#ex__sandbox form .submit').fadeIn();
    }
    $('#ex__sandbox').toggleClass('expanded');
    set_cookie('sandbox_expanded', $('#ex__sandbox').attr('class'));
}

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

    /**
     * TOGGLE
     */
	$('#ex__left .toggle').live('click', function() {
        if ($('#ex__left ul:visible li > ul:visible').length) {
            $('#ex__left ul:visible li > ul').hide();
        } else {
            $('#ex__left ul:visible li > ul').show();
        }
		update_toggle();
		set_open();
		return false;
	});

    if ($('#ex__left ul ul:visible').length) {
        $('#ex__left .toggle').text('hide all').toggleClass('show');
    }

    /**
     * SANDBOX
     */
    $('#ex__sandbox h2').live('click', function() {toggle_sandbox();});

    $('#ex__sandbox .parameters').live('focus', function(){
        $(this).animate({width: '75%'}).parent().find('.returns').animate({width: '20%'});
    });
    
    $('#ex__sandbox .returns').live('focus', function(){
        $(this).animate({width: '75%'}).parent().find('.parameters').animate({width: '20%'});
    });

    $('#ex__sandbox .parameters textarea').live('keyup', function() {
        var value = $(this).removeClass('error').val();
        
        try {
            if (value.replace(/\s/gi, '') != "{}" && value.replace(/\s/gi, '') != '')
                eval('var json = ' + value + ';');
            $('#ex__sandbox .submit').removeClass('disabled');
        } catch(e) {
            $('#ex__sandbox .submit').addClass('disabled');
            $(this).addClass('error');
        }
    });

    $('#ex__sandbox form').live('submit', function(){
        if ($(this).find('.submit').hasClass('disabled')) return false;
        
        var params = $('#ex__sandbox .parameters textarea').val();
        $.post(window.location, {ajax: 'runCommand', parameters: params}, function(result){
            $('#ex__sandbox .returns textarea').val(result);
        });
        return false;
    });

});