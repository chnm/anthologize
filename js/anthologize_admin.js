// return post_id
//
// Move an Item or Part
// (in case of moving parts, just duplicate and src and dest content)
// (in case of new=true, src vars = null)
seq_stringify = function(seq_obj) {
    seq_string = '{';
    jQuery.each(seq_obj, function(post_id, seq_num){
        seq_string += '"' + post_id + '"' + ':' + '"' + seq_num + '",';
    });
    seq_string = seq_string.substr(0,seq_string.length-1);
    seq_string += '}';
    return seq_string;
}

anth_admin_ajax = {
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
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // We're done
            if (config_obj.new_item == 'true') {
                //jQuery('li#' + item_id).attr('id', data.post_id);
                anthologize.updateAddedItem(data.post_id);
            }
            return true;
        },
        error: function(){
            // Move the Item back
            if (config_obj.new_item == 'true') {
                jQuery('li#new_new_new').fadeOut('normal', function() {
                    jQuery(this).remove();
                });
            } else {
                jQuery('li#' + config_obj.item_id).insertBefore($('li#' + config_obj.src_id + 'ul li').eq(config_obj.org_seq_num - 1));
            }
        }
    });

},
merge_items: function(config_obj) {
// Append/merge items into a single other item
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {action:'merge_items',
               project_id:project_id,
               post_id:item_id,
               child_post_ids:{},
               // TODO: create this data
               new_seq:seq_stringify(config_obj.merge_seq)},
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // Remove the other IDs
            jQuery.each(child_post_ids, function(post_id) {
                jQuery('li#' + post_id).fadeOut('normal', function() {
                    jQuery(this).remove();
                });
            });
        },
        error: function(){
            // Post error alert?
            alert('Error merging items');
        }
    });
},
update_post_metadata: function(config_obj){

// Change Part/Item metadata
// TODO: what does this metadata package look like?
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {action:'update_post_metadata',
               project_id:project_id,
               post_id:item_id},
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // We're done
            return true;
        },
        error: function(){
            alert('Error updating post metadata');
        }
    });
},
remove_item_part: function(config_obj){
// Remove an Item/Part
// TODO: What about removing a Part that still contains Items? Handled on the server side?
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {action:'remove_item_part',
               project_id:project_id,
               post_id:item_id,
               // TODO: create this data
               new_seq:seq_stringify(config_obj.new_seq)},
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // Remove the post from the display
            jQuery('li#' + item_id).fadeOut('normal', function(){
                jQuery(this).remove();
            });
        },
        error: function(){
            alert('Error removing post');
        }
    });
}};

// json return:
//
// Filter list of posts by Tag
/*$.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {tag_id:!!!tag_id!!!},
        dataType:json,
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // Replace posts list with data (loop and build HTML)
            $.each(data, function(post_id, post_obj) {
                // TODO: fill in the HTML 
            });
        },
        error: function(){
            // TODO: Revert form
            alert('Error filtering posts');
        }
});

*/
// Filter list of posts by Category
/*$.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {category_id:!!!category_id!!!},
        dataType:json,
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // Replace posts list with data (loop and build HTML)
            $.each(data, function(post_id, post_obj) {
                // TODO: fill in the HTML 
            });
        },
        error: function(){
            // TODO: Revert form
            alert('Error filtering posts');
        }
});
*/

//
// returns post_id, seq_num
//

// Insert a new Item
// Where do we get the title?
/*$.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {action:'insert_new_item',
               project_id:project_id,
               part_id:!!!part_id!!!,
               new_seq:!!!new_seq!!!},
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // TODO: Insert Item
            // What does the HTML look like?
        },
        error: function(){
            alert('Error adding new item');
        }
});*/

// Insert a new Part
// Where do we get the title?
/*$.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {action:'insert_new_part',
               project_id:project_id,
               new_seq:!!!new_seq!!!},
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // TODO: Insert Item
            // What does the HTML look like?
        },
        error: function(){
            alert('Error adding new part');
        }
});*/
