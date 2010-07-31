var seq_stringify = function(seq_obj) {
    seq_string = '{';
    jQuery.each(seq_obj, function(post_id, seq_num){
        seq_string += '"' + post_id + '"' + ':' + '"' + seq_num + '",';
    });
    seq_string = seq_string.substr(0,seq_string.length-1);
    seq_string += '}';
    return seq_string;
}

jQuery.blockUI.defaults.onUnblock = function() {
    jQuery('#blockUISpinner').hide();
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
                src_seq:seq_stringify(config_obj.src_seq)
                },
            async:false,
            timeout:20000,
            success: function(data){
                if (config_obj.new_item == 'true') {
                    anthologize.updateAddedItem(data.post_id);
                }
                return true;
            },
            complete: function(){
                jQuery.unblockUI();
            },
            error: function(){
                // Move the Item back
                if (config_obj.new_item == 'true') {
                    jQuery('li#new_new_new').fadeOut('normal', function() {
                        jQuery(this).remove();
                    });
                } else {
                    if (config_obj.dest_id == config_obj.project_id) {
                        item_selector = 'li#part-' + config_obj.item_id;
                        home_selector = 'ul.project-parts';
                        item_rev = jQuery(item_selector);
                        item_rev.appendTo(home_selector);
                        // TODO: put the item in the right sequence
                    } else {
                        item_selector = 'li#item-' + config_obj.item_id;
                        home_selector = 'li#part-' + config_obj.src_id + ' .part-items ul';
                        item_rev = jQuery(item_selector);
                        item_rev.appendTo(home_selector);
                        // TODO: put the item in the right sequence
                        //item_rev.insertBefore(home_selector + ' li').eq(config_obj.org_seq_num-1).not(item_rev);
                    }
                }
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
            complete: function(){
                jQuery.unblockUI();
            },
            success: function(data){
                anthologize.updateAppendedItems(config_obj.child_post_ids);
            },
            error: function(){
                // Post error alert?
                alert('Error merging items');
            }
        });
    }
};
