
jQuery(document).ready( function() {
	var j = jQuery;

	j('#sortby-dropdown').change( function() {
		var filter = j('#sortby-dropdown').val();

		if ( filter == 'category' )
			var action = 'get_cats';
		else
			var theaction = 'get_tags';

		j.post( ajaxurl, {
			action: theaction,
		},
		function(response)
		{
			var awesome = 'cool';
			j.each( response, function(index, value) {
				awesome = awesome + 1;

			});
alert(awesome);

		});

	});


});
