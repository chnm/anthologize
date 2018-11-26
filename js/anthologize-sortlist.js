var anthologize = {
  "src_id": null,
  "new_item_org_seq_num": null,
  "org_seq_num": null,
  "src_seq" : {},
  "fromNew": false,
  "newItem": null,
  "appending": false,
  "callBack" : function(event, ui){
    jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
                    message: jQuery('#blockUISpinner').show() });

    var dest_part_id;
    var dest_seq = {};
    var offset = 1;
    var new_item = "false";
    var src_id = anthologize.src_id;
    var item_id = anthologize.cleanPostIds(ui.item.attr("id"));
    var project_id = anthologize.getProjectId();
    var org_seq_num = anthologize.org_seq_num;

    if (anthologize.fromNew){
      new_item = "true";
      org_seq_num = anthologize.new_item_org_seq_num;
      ui.item.attr("id", "new_new_new");
    }

    offset = 1;
    ui.item.siblings().addBack().each(function(){
      dest_seq[anthologize.cleanPostIds(jQuery(this).attr("id"))] = offset;
			offset++;
    });

    if (ui.item.hasClass("item")){
      dest_id = ui.item.closest("li.part").attr("id");
      // src_id is null if we didn't receive it from another list,
      // so keep the parent id
      if (src_id == null){
	       src_id = dest_id;
	       anthologize.src_seq = {};
      }
    }else{
	    //dest and src for parts is the project id
      dest_id = project_id;
      anthologize.src_id = project_id;
      anthologize.src_seq = {};
    }

    var ajax_options = {
	    "project_id": project_id,
	    "src_id": this.cleanPostIds(src_id),
	    "dest_id": this.cleanPostIds(dest_id),
	    "new_item": new_item,
	    "item_id": item_id,
	    "org_seq_num": org_seq_num,
	    "dest_seq":  dest_seq,
	    "src_seq": anthologize.src_seq
    };
    if (anthologize.didSortChange(ajax_options)){
      anth_admin_ajax.place_item(ajax_options);
    }else{
	    jQuery.unblockUI();
    }
  },
  "addMultiItems": function(event, ui){
	/*jQuery.blockUI({
		css: {width: '12%',top:'40%',left:'45%'},
		message: jQuery('#blockUISpinner').show()
	});*/

	var dest_seq = {};
	var item_ids = [];
	var project_id = anthologize.getProjectId();
	var i = 0;

	ui.item.after(jQuery("#sidebar-posts-clone li").clone().each(function(){
		var sidePostId = jQuery(this).find("span.fromNewId").first().text();
		var orig_id = anthologize.cleanPostIds(sidePostId);
		var added_id = "item-" + orig_id;
		jQuery(this).attr("id", added_id);
		item_ids[i] = added_id;
		i++;
	}));

	var startOffset = ui.item.next();
	var dest_id = ui.item.closest("li.part").attr("id");
	ui.item.detach();

	offset = 1;
	startOffset.siblings().andSelf().each(function(){
		dest_seq[jQuery(this).attr("id")] = offset;
		offset++;
	});

	var ajax_options = {
	    "project_id": project_id,
	    "dest_id": this.cleanPostIds(dest_id),
	    "item_ids": item_ids,
	    "dest_seq":  dest_seq,
	};

    	anth_admin_ajax.place_items(ajax_options);

	},
  "didSortChange" : function(ajax_options){
    if (! (((ajax_options.src_id == ajax_options.dest_id) ||
           (ajax_options.src_id == null && ajax_options.dest_id == ajax_options.project_id))
	      && ajax_options.src_seq[ajax_options.item_id] == ajax_options.dest_seq[ajax_options.item_id])){
      return true;
    }else{
	    return false;
    }
  },
  "initSidebar" : function(){
	  jQuery("#sidebar-posts li").draggable({
	    connectToSortable: ".part-items ul",
	    helper: "clone",
	    revert: "invalid",
	    zIndex: 2700,
	    distance: 3,
	    start: function(event, ui){
	      anthologize.new_item_org_seq_num = jQuery(this).index() + 1;
	    },
	    drag: function(event, ui){
		    if (anthologize.fromNew == false){
			    anthologize.fromNew = true;
		    }
	    }
	  });
	  jQuery("#customlinkdiv .part-header").draggable({
	    connectToSortable: ".part-items ul",
	    helper: function(){
                var cloned_items = jQuery( '#sidebar-posts' ).clone().attr( 'id', 'sidebar-posts-clone' );
                return cloned_items;
            },
	    revert: "invalid",
	    zIndex: 2700,
	    distance: 3,
	    start: function(event, ui){
	      anthologize.new_item_org_seq_num = jQuery(this).index() + 1;
	    },
	    drag: function(event, ui){
		    if (anthologize.fromNew == false){
			    anthologize.fromNew = true;
		    }
	    }
	  });
  },
  "getProjectId" : function(){
	  return this.cleanPostIds(jQuery(".wrap").attr("id"));
  },
  "cleanPostIds" : function(dom_id){
	  var clean_id = dom_id;
	  if (clean_id != null){
	    clean_id = clean_id.replace("project-", "");
	    clean_id = clean_id.replace("part-", "");
	    clean_id = clean_id.replace("item-", "");
	    clean_id = clean_id.replace("new-", "");
	    clean_id = clean_id.replace("append-", "");
	    clean_id = clean_id.replace("comment-", "");
    }
	  return clean_id;
  },
  "getAppendableItems" : function(item_id){
	  var itemInfo = {};
	  var part = jQuery("#" + item_id).closest("li.part");
	  var items = jQuery("#" + item_id).siblings();
	  var i = 0;
	  items.each(function(){
		  itemInfo[jQuery(this).attr("id")] = jQuery(this).find("span.part-title").text();
		  i++;
	  });
	  return itemInfo;
  },
  "getComments" : function(item_id,item){
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
		   action:'get_item_comments',
		   post_id:item_id
		},
		async:false,
		timeout:20000,
		success: function(response){
			if (! response){
				jQuery(item).find('.comments-panel').html('<p>' + anth_strings.no_comments + '</p><br /><input type="button" class="cancelComments" value="' + anth_strings.cancel + '" />');
			} else {

				var w = jQuery(item).find('.comment-table tbody');

				for (var itemId in response){
					var commentid = response[itemId].comment_ID;
					if ( response[itemId].is_included ) {
						var checked = ' checked="checked"';
					} else {
						var checked = '';
					}

					var comment = '<tr><td class="checkbox"><input type="checkbox" class="select-comment" name="comments[]" id="comment-' + commentid + '"' + checked + ' /></td>';

					comment += '<td class="comment-author-email">' + response[itemId].comment_author_email + '</td>';

					comment += '<td class="comment-content">' + anthologize.trimToLength( response[itemId].comment_content, 30, commentid ) + '</td>';

					comment += '<td class="comment-date">' + response[itemId].comment_date + '</td>';

					comment += '</tr>';

					w.append(comment);
				}
			}

			jQuery(".project-parts").sortable("disable");
			jQuery(".part-items ul").sortable("disable");
			jQuery("a.toggle").addClass("disabled");
			jQuery("span.toggle-sep").addClass("disabled");
			jQuery(item).removeClass("disabled");
			jQuery(item).siblings().removeClass("disabled");
			jQuery(item).find("a.comments").removeClass("disabled");
			jQuery(item).find("span.comments-sep").removeClass("disabled");
			item.find("div.comments-panel").first().slideToggle();
			anthologize.appending = true;
		},
		error: function(){
			ajax_error_refresh();
		}
	});


  },
  "updateAddedItem" : function (new_item_id,comment_count,original_id){
		newItem = anthologize.newItem;

		/* Multi-add items get their ids from the original post id, so we swap the selector here */
		var id_selector = original_id !== false ? original_id : new_item_id;

		newItem.attr("id", "item-" + id_selector);

		newItem.children("h3").wrapInner('<span class="part-title" />');

		/* Comments are being held until 0.7 */
		/*
		var buttons = 	'<div class="part-item-buttons">' +
		'<a href="post.php?post=' + new_item_id + '&amp;action=edit">' + anth_strings.edit + '</a> | '+
		'<a href="#comments" class="comments toggle">' + anth_strings.comments + ' (<span class="included-comment-count">0</span>/' + comment_count + ')</a><span class="comments-sep toggle-sep"> |</span> ' +
		'<a class="append" href="#append">' + anth_strings.append + ' | </a> ' +
		'<a class="confirm" href="admin.php?page=anthologize&amp;action=edit&amp;' +
		'project_id=' + anthologize.getProjectId() + '&amp;remove=' + new_item_id + '">' + anth_strings.remove + '</a>' +
		'</div>';
		*/

		var buttons = 	'<div class="part-item-buttons anth-buttons">' +
		'<a href="post.php?post=' + new_item_id + '&amp;action=edit">' + anth_strings.edit + '</a> | ' +
		'<a class="append" href="#append">' + anth_strings.append + '</a><span class="append-sep toggle-sep"> | </span>' +
		'<a class="anth-preview anth-preview-item" href="admin.php?page=anthologize&anth_preview=1&post_type=anth_library_item&post_id=' + new_item_id + '" target="new">' + anth_strings.preview + '</a><span class="toggle-sep"> | </span>' +
		'<a class="confirm" href="admin.php?page=anthologize&amp;action=edit&amp;' + 'project_id=' + anthologize.getProjectId() + '&amp;remove=' + new_item_id + '">' + anth_strings.remove + '</a>' +
		'</div>';

		newItem.children("h3").append(buttons);

		/* Ugh. Now we switch back to the proper id, so the comments slider will work */
		if ( original_id !== false ) {
			newItem.attr("id", "item-" + new_item_id);
		}
  },
  "updateAppendedItems" : function(appended_items){
	  var appendedTo = jQuery(".active-append").closest("li.item");
	  for (var i in appended_items){
		  var remove = jQuery("#item-" + appended_items[i]);
		  remove.fadeOut('slow');
		  remove.remove();
	  }
	  appendedTo.find("a.cancelAppend").click();
	  anthologize.setAppendStatus();
  },
  "setAppendStatus" : function(){
	   jQuery("a.toggle").removeClass("disabled");
	   jQuery("span.toggle-sep").removeClass("disabled");
	   jQuery(".part-items").each(function(){
		   var items = jQuery(this).find("li.item");
		   if (items.length == 1){
			   items.first().find("a.append").addClass("disabled");
			   items.first().find("span.append-sep").addClass("disabled");
		   }
	   });
  },
  "toggleCollapseCookie": function(id){
	  var cp = jQuery.cookie('collapsedparts');
	  var parts = new Array();
		if (cp){
			parts = cp.split(',');
  	}
	  var partsWithoutId = new Array();
	  var idFound = false;
	  for (var i = 0; i < parts.length; i++){
		  if (parts[i] != id){
			  partsWithoutId.push(parts[i]);
		  }else{
			  idFound = true;
		  }
	  }
	  if (idFound){
		  jQuery.cookie('collapsedparts', partsWithoutId.join(','));
	  }else{
			parts.push(id);
			jQuery.cookie('collapsedparts', parts.join(','));
		}
  },
  "trimToLength": function( string, length, commentid ) {
	var result = string;
	var resultArray = result.split(" ");
	if(resultArray.length > length){
		resultArray.splice(length,0, '<a href="#more" class="more">[' + anth_strings.more + ']</a><span class="hide hidden-text">');
		resultArray.push(' <a href="#less" class="less">[' + anth_strings.less + ']</a></span>');
		result = resultArray.join(' ');
	}
	return result;
  },
  "fixFromNewId": function ( newEl ) {
      var fromNewId = jQuery(newEl).find("span.fromNewId");
      if (fromNewId.length){
          jQuery(newEl).attr("id", jQuery(fromNewId[0]).text());
          jQuery(fromNewId).remove();
      }
  }
};

jQuery.fn.anthologizeSortList = function (options){

  var settings = jQuery.extend({
    placeholder: 'anthologize-drop-item',
    distance: 3,
    start: function(event, ui){
      anthologize.src_id = null;
      anthologize.org_seq_num = ui.item.index() + 1;
      anthologize.src_seq = {};
      var pos = 1;
      ui.item.siblings().each(function(){
          if (! jQuery(this).hasClass("anthologize-drop-item")){
              anthologize.src_seq[anthologize.cleanPostIds(jQuery(this).attr("id"))] = pos;
              pos++;
          }
      });
      anthologize.fromNew = false;
      anthologize.newItem = null;
      ui.item.addClass("anthologize-drag-selected");
    },
    stop: function (event, ui){
      ui.item.css( 'width', '' );
      anthologize.newItem = ui.item;
      if (ui.item.get(0).tagName == 'UL'){
        ui.item.find("li.item").each(function(postItem) {
          anthologize.fixFromNewId(postItem);
        });

	anthologize.addMultiItems(event, ui);
      }else{
        anthologize.fixFromNewId(ui.item);
        anthologize.callBack(event, ui);
      }
      ui.item.removeClass("anthologize-drag-selected");
    },
    receive: function(event, ui){
      var senderLi = ui.sender.closest("li");
      var senderId = senderLi.attr("id");
      if (senderLi.hasClass("item")){
          var fromNewId = senderLi.find("span.fromNewId").first();
          if (fromNewId.length){
              senderId = jQuery(fromNewId).text();
          }
      }
      anthologize.src_id = senderId;
    }
  },
  options);

  return this.each(function(){jQuery(this).sortable(settings)});
}

jQuery(document).ready(function(){
  jQuery(".project-parts").anthologizeSortList({});
  jQuery(".part-items ul").anthologizeSortList({
    connectWith: ".part-items ul"
  });
  anthologize.setAppendStatus();
  anthologize.initSidebar();

  jQuery("body").delegate("a.append", "click", function(){
	  var item = jQuery(this).closest("li.item");

    if (anthologize.appending == false && ! jQuery(this).hasClass("disabled")){
	    jQuery(this).addClass("active-append");
		  var appendPanel = '<div class="append-panel" style="display:none;"><form><div class="append-items"></div>' +
		                    '<input type="button" class="doAppend" name="doAppend" value="Append" /> ' +
		                    '<a href="#cancel" class="cancelAppend">Cancel</a></form></div>';
		  item.append(appendPanel);
		  var panelItems = item.find("div.append-items").first();
		  var appendable = anthologize.getAppendableItems(item.attr("id"),item);
		  for (var itemId in appendable){
			  panelItems.append('<div><input type="checkbox" name="append[append-' + itemId + ']" id="append-' + itemId + '"  value="' + itemId + '"/> ' +
			               '<label for="append-' + itemId+ '">' + appendable[itemId] + '</label></div>');
		  }

		  jQuery(".project-parts").sortable("disable");
		  jQuery(".part-items ul").sortable("disable");
		  jQuery("a.toggle").addClass("disabled");
		  jQuery("span.append-sep").addClass("disabled");
		  jQuery(this).removeClass("disabled");
		  jQuery(this).siblings("a.append").removeClass("disabled");
		  jQuery(this).siblings("span.append-sep").removeClass("disabled");
		  item.find("div.append-panel").first().slideToggle();
		  anthologize.appending = true;
	  }else{
		  item.find("a.cancelAppend").click();
	  }
  });

  jQuery("body").delegate("a.comments", "click", function(){
	  var item = jQuery(this).closest("li.item");

    if (anthologize.appending == false && ! jQuery(this).hasClass("disabled")){
	    jQuery(this).addClass("active-comments");
		var commentPanel =
			'<div class="comments-panel" style="display:none;">' +
			'<p>' + anth_strings.comments_explain +
			' <br/><span class="comment-select-links"><a href="#select-all" class="select-multiple select-all">' + anth_strings.select_all + '</a> | <a href="#select-none" class="select-multiple select-none">' + anth_strings.select_none + '</a></span></p>' +
			'<table class="comment-table"><thead><tr>' +
				'<td class="comment-check" scope="col"></td>' +
				'<td class="comment-commenter" scope="col">' + anth_strings.commenter + '</td>' +
				'<td class="comment-content" scope="col">' + anth_strings.comment_content + '</td>' +
				'<td class="comment-posted" scope="col">' + anth_strings.posted + '</td>' +
			'</tr></thead><tbody></tbody></table>' +
		        '<input type="button" class="cancelComments" value="' + anth_strings.done + '" /></div>';
		  item.append(commentPanel);

		  anthologize.getComments(item.attr("id"),item);

	  }else{
		  item.find("input.cancelComments").click();
	  }
  });

  jQuery("body").delegate("a.cancelAppend", "click", function(){
	  var item = jQuery(this).closest("li.item");
	  jQuery(this).parents("li.item").find("a.append").removeClass("active-append");
	  var panel = jQuery("div.append-panel");
	  jQuery("div.append-panel").slideToggle('slow', function(){
		  jQuery(this).remove();
		  anthologize.setAppendStatus();
	  });
	  jQuery(".project-parts").sortable("enable");
	  jQuery(".part-items ul").sortable("enable");
	  anthologize.appending = false;
  });

  jQuery("body").delegate("input.cancelComments", "click", function(){
	  var item = jQuery(this).closest("li.item");
	  jQuery(this).parents("li.item").find("a.comments").removeClass("active-comments");
	  var panel = jQuery("div.comments-panel");
	  jQuery("div.comments-panel").slideToggle('slow', function(){
		  jQuery(this).remove();
		  anthologize.setAppendStatus();
	  });
	  jQuery(".project-parts").sortable("enable");
	  jQuery(".part-items ul").sortable("enable");
	  anthologize.appending = false;
  });

  jQuery("body").delegate("input.doAppend", "click", function(){
      jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
                      message: jQuery('#blockUISpinner').show() });

	  var item = jQuery(this).closest("li.item");
	  var append_items = {};
	  var merge_seq = {};
	  var i = 0;

	  jQuery(".append-items input:checkbox:checked").each(function(){
		  append_items[i] = anthologize.cleanPostIds(this.value);
		  i++;
	  });
	  var j = 1;
	  item.parent().children().each(function(){
		  var skip = false;
		  var id = anthologize.cleanPostIds(jQuery(this). attr("id"));
		  for (var k in append_items){
			  if (id == append_items[k]){
				  skip = true;
				  break;
			  }
			  k++;
			}
			if (! skip){
			  merge_seq[id] = j;
			  j++;
			}
	  });
	  var project_id = anthologize.getProjectId();
	  var post_id = anthologize.cleanPostIds(item.attr("id"));
	  anth_admin_ajax.merge_items({"project_id":project_id, "post_id":post_id, "child_post_ids":append_items, "merge_seq": merge_seq});
  });

  jQuery("body").delegate("input.select-comment", "change", function(){
	jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'}, message: jQuery('#blockUISpinner').show() });

	var comment_id = anthologize.cleanPostIds(jQuery(this).attr('id'));
	var item = jQuery(this).closest("li.item");
	var post_id = anthologize.cleanPostIds(item.attr("id"));

	var check_action = jQuery(this).is(':checked') ? 'add' : 'remove';

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
			action:'include_comments',
			check_action:check_action,
			post_id:post_id,
			comment_id:comment_id
		},
		async:false,
		timeout:20000,
		success: function(response){
			jQuery(item).find('.included-comment-count').html(response.length);
			jQuery.unblockUI();
			return false;
		},
		error: function(){
			jQuery.unblockUI();
			return false;
		}
	});

  });

  /* [more] unhides the '.hide' text and hides the [more] link */
  jQuery("body").delegate("a.more", "click", function(){
  	var hiddentext = jQuery(this).siblings(".hide");
  	jQuery(hiddentext).removeClass('hidden-text');
  	jQuery(this).addClass('hidden-text');
  	return false;
  });

  /* [less] unhides the [more] text and hides the .hide text */
  jQuery("body").delegate("a.less", "click", function(){
  	var hiddentext = jQuery(this).parent();
  	jQuery(hiddentext).addClass('hidden-text');
  	jQuery(hiddentext).siblings('.more').removeClass('hidden-text');
  	return false;
  });

  /* Select All / Select None */
  jQuery("body").delegate("a.select-multiple", "click", function(){
  	jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'}, message: jQuery('#blockUISpinner').show() });

  	var item = jQuery(this).closest("li.item");
	var item_id = anthologize.cleanPostIds(item.attr("id"));

	var check_action = jQuery(this).hasClass('select-none') ? 'remove' : 'add';

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: {
		   action:'include_all_comments',
		   check_action:check_action,
		   post_id:item_id
		},
		async:false,
		timeout:20000,
		success: function(response){
			var checkboxes = jQuery(item).find( ':checkbox' );
			var cvalue = check_action == 'remove' ? '' : 'checked';
			jQuery(checkboxes).attr('checked', cvalue);
			jQuery(item).find('.included-comment-count').html(response.length);
			jQuery.unblockUI();
		},
		error: function(){
			ajax_error_refresh();
		}
	});
  });

	jQuery("body").delegate("ul.project-parts li.part a.collapsepart", "click", function(){
		var part = jQuery(this).parents('li.part');
		part.children("div.part-items").slideToggle('slow', function(){
			var collapseButton = jQuery(this).parent().find("a.collapsepart");
			if (collapseButton.text() == ' - '){
			  collapseButton.text(' + ');
		  }else{
				collapseButton.text(' - ');
			}
		});
		anthologize.toggleCollapseCookie(part.attr('id'));
	});

	jQuery('#project-organizer-frame').on('click', '.accordion-toggle', function( e ) {
		jQuery(e.target).closest('.has-accordion').toggleClass('accordion-closed');
	} );

	var cp = jQuery.cookie('collapsedparts');
	if (cp){
		var parts = cp.split(',');
		for (var i = 0; i < parts.length; i++){
	  	var p = jQuery('#' + parts[i]);
	  	if (p.length > 0){
		  	p.children("div.part-items").toggle();
				p.find("a.collapsepart").text(' + ');
	  	}
		}
	}
});
