
jQuery(document).ready( function() {
	var j = jQuery;

	j('#sortby-dropdown').change( function() {
		var filter = j('#sortby-dropdown').val();

		if ( filter == 'category' )
			var theaction = 'get_cats';
		else
			var theaction = 'get_tags';

		j.post( ajaxurl, {
			action: theaction,
		},
		function(response)
		{
			var s = response.split(',');
			j('#filter').empty();
			j('#filter').append('<option value="" disabled="disabled"> - </option>');
			j.each( s, function(index, value) {
				var v = value.split(':');
				var h = '<option value="' + index + '">' + v[1] + '</option>';
				//alert(h); return false;
				j('#filter').append(h);
			});

		});

	});

	j('#filter').change( function() {
		var term = j('#filter').val();
		var tagorcat = j('#sortby-dropdown').val();

		j.post( ajaxurl, {
			action: theaction,
		},
		function(response)
		{
			var s = response.split(',');
			/* j('#filter').empty();
			j('#filter').append('<option value="" disabled="disabled"> - </option>');
			j.each( s, function(index, value) {
				var v = value.split(':');
				var h = '<option value="' + index + '">' + v[1] + '</option>';
				//alert(h); return false;
				j('#filter').append(h);
			}); */

		});


	});

});
