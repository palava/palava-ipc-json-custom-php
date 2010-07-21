$(document).ready(function(){
	$('#ex__left span').live('click', function(){
		$(this).nextAll('ul').slideToggle();
	});
});