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

	$('select[name=doc-sorter]').change(function(){
		var select = $(this),
		sortby = select.val(),
		taxonomy = select.data('term');
		select.parents('.tab-pane').find('ul.documents').addClass('loading');
		$.ajax({
	        url: ajax_object.ajaxurl,
	        type: 'post',
	        data: {
	            'action' :'get_docs',
	            'taxonomy' : taxonomy,
	            'sortby' : sortby
	        },
	        success: function( response ) {
	        	select.parents('.tab-pane').find('ul.documents').html(response);
	        	select.parents('.tab-pane').find('ul.documents').removeClass('loading');
	        },
	    });
	});
});
