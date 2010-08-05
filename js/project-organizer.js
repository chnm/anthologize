
jQuery(document).ready( function() {
	var j = jQuery;

	// Set filter based on last visit
	var cfilter = j.cookie('anth-filter');
	var cterm = j.cookie('anth-term');

	// uses setTimeout in place of a real callback. Hackapotomous?
	/*if ( cfilter != null && cterm != null ) {
		j('#sortby-dropdown').val(cfilter);
		setTimeout( function() {
			j('#sortby-dropdown').change();
			setTimeout( function() {
				j('#filter').val(cterm);
				j('#filter').change();
			}, 500 );
		}, 1 );
	} else {
		j('#sortby-dropdown').val('');
	}*/


	j('#sortby-dropdown').change( function() {

        jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
                        message: jQuery('#blockUISpinner').show() });

		var filter = j('#sortby-dropdown').val();

		j.cookie('anth-filter', filter);

		if ( filter == 'category' ) {
			var theaction = 'get_cats';
        } else if ( filter == 'tag' ) {
			var theaction = 'get_tags';
        } else {
            var theaction = 'default';
			j('#filter').empty();
			j('#filter').append('<option value=""> - </option>');
        }

        j('#filter').val('');
        j('#filter').trigger('change');

        if (theaction == 'default') {
            j.unblockUI();
            return true;
        }

        j.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 10000,
            data: {action:theaction},
            success: function(response){
                var s = response.split(',');
                j('#filter').empty();

                if (filter == 'tag') {
                    j('#filter').append('<option value="">All Tags</option>');
                } else if (filter == 'category') {
                    j('#filter').append('<option value="">All Categories</option>');
                } else {
                    j('#filter').append('<option value=""> - </option>');
                }
                j.each( s, function(index, value) {
                    var v = value.split(':');
                    var h = '<option value="' + v[0] + '">' + v[1] + '</option>';
                    //alert(h); return false;
                    j('#filter').append(h);
                });
            },
            complete: function(){
                j.unblockUI();
            }
        });

	});

	j('#filter').change( function() {

        jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
                        message: jQuery('#blockUISpinner').show() });

		var term = j('#filter').val();

		j.cookie('anth-term', term);

		var tagorcat = j('#sortby-dropdown').val();
        if (tagorcat == '') {
            tagorcat = 'cat';
        }

        j.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 10000,
            data: {action:'get_posts_by',term:term,tagorcat:tagorcat},
            success: function(response){
                var s = response.split(',');
                j('#sidebar-posts').empty();
                j.each( s, function(index, value) {
                    var v = value.split(':');
                    var h = '<li class="item" id="new-' + v[0] + '"><h3 class="part-item">' + v[1] + '</h3></li>';
                    j('#sidebar-posts').append(h);
                });
                anthologize.initSidebar();
                },
            complete: function(){
                j.unblockUI();
            }
        });


	});

});
