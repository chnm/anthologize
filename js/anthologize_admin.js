var seq_stringify = function(seq_obj) {
	seq_string = '';
	jQuery.each(seq_obj, function(post_id, seq_num){
		seq_string += '"' + post_id + '"' + ':' + '"' + seq_num + '",';
	});
	if (seq_string.length > 0){
		seq_string = seq_string.substr(0,seq_string.length-1);
	}
	return '{' + seq_string + '}';
}

var ajax_error_refresh = function() {
    jQuery('#ajaxErrorMsg').show();
    location.reload();
}

jQuery.blockUI.defaults.onUnblock = function() {
    jQuery('#blockUISpinner').hide();
    jQuery('#ajaxErrorMsg').hide();
}

var anth_admin_ajax = {
    place_item: function(config_obj) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {action:'place_item',
                   project_id:config_obj.project_id,
                   post_id:config_obj.item_id,
                   new_post:config_obj.new_item,
                   dest_id:config_obj.dest_id,
                   src_id:config_obj.src_id,
                   dest_seq:seq_stringify(config_obj.dest_seq),
                   src_seq:seq_stringify(config_obj.src_seq)},
            async:false,
            timeout:20000,
            success: function(data){
                if (config_obj.new_item == 'true') {
                    anthologize.updateAddedItem(data.post_id);
                }
                anthologize.setAppendStatus();
                jQuery.unblockUI();
            },
            error: function(){
                ajax_error_refresh();
            }
        });

    },
    place_items: function(config_obj) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {action:'place_items',
                   project_id:config_obj.project_id,
                   post_ids:seq_stringify(config_obj.item_ids),
                   dest_id:config_obj.dest_id,
                   dest_seq:seq_stringify(config_obj.dest_seq)},
            async:false,
            timeout:20000,
            success: function(data){
								for (var i in data.post_ids){
									anthologize.newItem = jQuery('#added-' + i);
                	anthologize.updateAddedItem(data.post_ids[i]);
								}
                anthologize.setAppendStatus();
                jQuery.unblockUI();
            },
            error: function(){
                ajax_error_refresh();
            }
        });

    },
    merge_items: function(config_obj) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {action:'merge_items',
                   project_id:config_obj.project_id,
                   post_id:config_obj.post_id,
                   child_post_ids:config_obj.child_post_ids,
                   new_seq:seq_stringify(config_obj.merge_seq)},
            async:false,
            timeout:20000,
            success: function(data){
                anthologize.updateAppendedItems(config_obj.child_post_ids);
                jQuery.unblockUI();
            },
            error: function(){
                ajax_error_refresh();
            }
        });
    }
};
