(function($){
	var currentFilterBy,
	  currentOrderBy,
		$dateFilterSection,
		$filterType,
		$orderByDropdown,
		$termFilter,
		$termFilterSection;

	function showHideSubFilter() {
		// Ensure null is converted to a string.
		if ( null === currentFilterBy ) {
			currentFilterBy = '';
		}

		switch ( currentFilterBy ) {
			case 'date' :
				$dateFilterSection.show();
				$termFilterSection.hide();
			break;

			case '' :
				$dateFilterSection.hide();
				$termFilterSection.hide();
			break;

			default :
				$dateFilterSection.hide();
				$termFilterSection.show();
			break;
		}
	}

	function do_filter(){
		var j = jQuery;
		var cookieExpires = new Date();
		cookieExpires.setTime(cookieExpires.getTime() + (60 * 60 * 1000));

		j.blockUI({css:{width: '12%',top:'40%',left:'45%'},
													message: jQuery('#blockUISpinner').show() });

		if (currentFilterBy == '') {
			filterby = 'cat';
		}

		currentOrderBy = $orderByDropdown.val();
		j.cookie( 'anth-orderby', currentOrderBy, { expires: cookieExpires } );

		var data = {
			action: 'get_posts_by',
			filterby: currentFilterBy,
			orderby: currentOrderBy
		};

		if (currentFilterBy == 'date'){
			data['startdate'] = j("#startdate").val();
				data['enddate'] = j("#enddate").val();
			j.cookie( 'anth-startdate', j("#startdate").val(), { expires: cookieExpires } );
			j.cookie( 'anth-enddate', j("#enddate").val(), { expires: cookieExpires } );
		}else{
			var term = $termFilter.val();
			data['term'] = term;
			j.cookie('anth-term', term, { expires: cookieExpires });
		}

		j.ajax({
			url: ajaxurl,
			type: 'POST',
			timeout: 10000,
			dataType:'json',
			data: data,
			success: function(response){
				j('#sidebar-posts').empty();
				j.each( response, function(post_index, post_data) {
					var post_id = post_data.ID;
					var h = '';
					h += '<li class="part-item item has-accordion accordion-closed">';
					h +=   '<span class="fromNewId">new-' + post_id + '</span>';
					h +=   '<h3 class="part-item-title">' + post_data.title + '</h3>';
					h +=   '<span class="accordion-toggle hide-if-no-js"><span class="accordion-toggle-glyph"></span> <span class="screen-reader-text">' + anth_strings.show_details + '</span></span>';

					h += '<div class="item-details"><ul>';
					for ( var md in post_data.metadata ) {
						h += '<li>' + post_data.metadata[ md ] + '</li>';
					}
					h += '</ul></div>';

					h += '</li>';

					j('#sidebar-posts').append(h);
				});
				anthologize.initSidebar();
			},
			complete: function(){
				j.unblockUI();
			}
		});
	}

	$(document).ready( function() {
		var j = jQuery;

		$filterType = $('#sortby-dropdown');
		$orderByDropdown = $('#orderby-dropdown');
		$termFilter = $('#filter');

		$dateFilterSection = $('#datefilter');
		$termFilterSection = $('#termfilter');

		// Set parent id for new parts and account for autosave
		var anth_parent = j('#anth_parent_id');
		if (anth_parent.length){
			var wp_parent = j('input[name="parent_id"]').not(anth_parent);
			if (wp_parent.length){
				wp_parent.val(anth_parent.val());
				anth_parent.remove();
			}else{
				anth_parent.attr('id', 'parent_id');
			}
		}

		// Put the proper selector on the parent_id box to ensure that it doesn't get wiped on
					// autosave
					if (!j('input#parent_id').length) {
									j('input[name="parent_id"]').first().attr('id', 'parent_id');
					}

		// Set orderby based on last visit
		currentOrderBy = j.cookie( 'anth-orderby' );
		if ( 'undefined' === currentOrderBy ) {
			currentOrderBy = $orderByDropdown.val();
		}

		// Set filter based on last visit
		currentFilterBy = j.cookie('anth-filter');
		if ( 'undefined' === currentFilterBy ) {
			currentFilterBy = $filterType.val();
		}

		if ( currentFilterBy == 'date' ) {
			$datefilter.show();
			var cstartdate = j.cookie('anth-startdate');
			var cenddate = j.cookie('anth-enddate');
			j("#startdate").val(cstartdate);
			j("#enddate").val(cenddate);
		} else {
			var cterm = j.cookie('anth-term');
		}

		showHideSubFilter();

		$filterType.change( function() {

			jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
													message: jQuery('#blockUISpinner').show() });

			currentFilterBy = $filterType.val();

			var cookieExpires = new Date();
			cookieExpires.setTime(cookieExpires.getTime() + (60 * 60 * 1000));
			j.cookie('anth-filter', currentFilterBy, { expires: cookieExpires });

			showHideSubFilter();

			if (currentFilterBy !== 'date') {
				j("#startdate").val('');
				j("#enddate").val('');
			}

			if (currentFilterBy == '') {
				$termFilter.empty();
				$termFilter.append('<option value=""> - </option>');
				$termFilter.trigger('change');
				j.unblockUI();
				return true;
			}

			j.ajax({
				url: ajaxurl,
				type: 'POST',
				timeout: 10000,
				data: {
					action: 'get_filterby_terms',
					filtertype: currentFilterBy
				},
				dataType: 'json',
				success: function(response){
					$termFilter.empty();

					if (currentFilterBy == 'tag') {
						$termFilter.append('<option value="">All Tags</option>');
					} else if (currentFilterBy == 'category') {
						$termFilter.append('<option value="">All Categories</option>');
					} else if (currentFilterBy == 'post_type') {
						$termFilter.append('<option value="">All Post Types</option>');
					} else {
						$termFilter.append('<option value=""> - </option>');
					}
					j.each( response, function(tagcat_index, tagcat) {
						var h = '<option value="' + tagcat_index + '">' + tagcat + '</option>';
						$termFilter.append(h);
					});
				},
				complete: function(){
					$termFilter.trigger('change');
					j.unblockUI();
				}
			});
		});

		$orderByDropdown.change( function() {
			do_filter();
		} );

		$termFilter.change( function() {
			do_filter();
		});

		j("#launch_date_filter").click(function(){
			do_filter();
		});

		j("#startdate").datepicker({dateFormat: 'yy-mm-dd'});
		j("#enddate").datepicker({dateFormat: 'yy-mm-dd'});

		j('#project-id-dropdown').change( function() {

			jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
													message: jQuery('#blockUISpinner').show() });

			var proj_id = j('#project-id-dropdown').val();
			j.ajax({
				url: ajaxurl,
				type: 'POST',
				timeout: 10000,
				dataType:'json',
				data: {action:'get_project_meta',proj_id:proj_id},
				success: function(response){
					var meta = j.parseJSON(response);

					if ( meta['cctype'] )
						j('#cctype').val(meta['cctype']);
					else
						j('#cctype').val('by');
					if ( meta['authors'] )
						j('#authors').val(meta['authors']);
					else
						j('#authors').val('');



					var inputs = j('#export-form').find('input');
					j.each(inputs, function( index, input ) {
						var theid = j(input).attr('id');

						if ( theid == 'export-step' || theid == 'submit' )
							return true;

						if ( meta[theid] )
							j(input).val(meta[theid]);
						else
							j(input).val('');
					}
				)},
				complete: function(){
					j.unblockUI();
				}
			});
		});

		j('.confirm-delete').click( function() {
			var answer = confirm("Are you sure you want to delete this project?")
			if (answer){
				return true;
			}
			else{
				return false;
			}
		});

	});
}(jQuery));
