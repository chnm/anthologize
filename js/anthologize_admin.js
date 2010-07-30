//
// Boolean return:
//
// Move an Item or Part
// (in case of moving parts, just duplicate and src and dest content)
// (in case of new=true, src vars = null)

// Also, return post_id
$.ajax({
        url: 'placeItem.php',
        data: {project_id:project_id,
               post_id:item_id,
               new_post:new_item,
               dest_id:dest_id,
               src_id:src_id,
               dest_seq:dest_seq,
               // TODO: create this data
               src_seq:src_seq
               },
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // We're done
            if (new_item == 'true') {
                $('li#' + item_id).attr('id', data.post_id);
            }
            return true;
        },
        error: function(){
            // Move the Item back
            if (new_item == 'true') {
                $('li#' + item_id).fadeOut('normal', function() {
                    $(this).remove();
                });
            } else {
                $('li#' + item_id).insertBefore($('li#' + src_id + 'ul li').eq(org_seq_num - 1));
            }
        }
});


// Append/merge items into a single other item
$.ajax({
        url: 'mergeItems.php',
        data: {project_id:project_id,
               post_id:item_id,
               child_post_ids:{},
               // TODO: create this data
               new_seq:merge_seq,
               author_str:xxx,
               title_str:xxx},
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // Remove the other IDs
            $.each(child_post_ids, function(post_id) {
                $('li#' + post_id).fadeOut('normal', function() {
                    $(this).remove();
                });
            });
        },
        error: function(){
            // Post error alert?
            alert('Error merging items');
        }
});


// Change Part/Item metadata
$.ajax({
        url: 'updatePostMetadata.php',
        data: {project_id:project_id,
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

// Remove an Item/Part
// TODO: What about removing a Part that still contains Items? Handled on the server side?
$.ajax({
        url: 'removePost.php',
        data: {project_id:project_id,
               post_id:item_id,
               // TODO: create this data
               new_seq:new_seq},
        async:false,
        timeout:20000,
        beforeSend:function() {
            // TODO: spinny popup
        },
        success: function(data){
            // Remove the post from the display
            $('li#' + item_id).fadeOut('normal', function(){
                $(this).remove();
            });
        },
        error: function(){
            alert('Error removing post');
        }
});

// json return:
//
// Filter list of posts by Tag
$.ajax({
        url: 'filterPostsByTag.php',
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


// Filter list of posts by Category
$.ajax({
        url: 'filterPostsByCategory.php',
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


//
// returns post_id, seq_num
//

// Insert a new Item
// Where do we get the title?
$.ajax({
        url: 'insertBlankItem.php',
        data: {project_id:project_id,
               part_id:!!!part_id!!!},
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
});

// Insert a new Part
// Where do we get the title?
$.ajax({
        url: 'insertNewPart.php',
        data: {project_id:project_id},
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
});
