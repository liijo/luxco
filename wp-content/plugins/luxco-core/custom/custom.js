jQuery(document).ready(function($){
	$('.nav-pills a').click(function(e){
		e.preventDefault();
		var divId = $(this).attr('href');
		if( ! $(this).hasClass('active') ){
		console.log(divId);
			$('.nav-pills a').removeClass('active');
			$(this).addClass('active');
			$('#pills-tabContent > div').removeClass('show active');
			$('#pills-tabContent '+divId).addClass('show active');
		}
	});
});
