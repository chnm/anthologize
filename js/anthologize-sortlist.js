var anthologize = {
  "src_id": null,
  "new_item_org_seq_num": null,
  "org_seq_num": null,
  "src_seq" : {},
  "fromNew": false,
  "newItem": null,
  "appending": false,
  "callBack" : function(event, ui){
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

    offset = ui.item.index() + 1
    dest_seq[this.cleanPostIds(ui.item.attr("id"))] = offset;
    ui.item.nextAll().each(function(){
      offset++;
      dest_seq[anthologize.cleanPostIds(jQuery(this).attr("id"))] = offset;
    });

    if (ui.item.hasClass("item")){
      dest_id = ui.item.closest("li.part").attr("id");
      // src_id is null if we didn't receive it from another list,
      // so keep the parent id
      if (src_id == null){
	       src_id = dest_id;
      }
    }else{
	    //dest and src for for parts is the project id
      dest_id = project_id;
      anthologize.src_id = project_id;
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
    anth_admin_ajax.place_item(ajax_options);
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
  "updateAddedItem" : function (new_item_id){
	  newItem = anthologize.newItem;
	  newItem.attr("id", "item-" + new_item_id);
	  newItem.children("h3").wrapInner('<span class="part-title" />');
	
	  var buttons = '<div class="part-item-buttons">' +
							'<a href="post.php?post=' + new_item_id + '&amp;action=edit">Edit</a> | '+
							'<a class="append" href="#">Append</a> | ' +
							'<a class="confirm" href="admin.php?page=anthologize&amp;action=edit&amp;project_id=4&amp;remove=' + new_item_id + '">Remove</a>' +
						  '</div>';
		newItem.children("h3").append(buttons);
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
	   jQuery("a.append").removeClass("disabled");
	   jQuery(".part-items").each(function(){
		   var items = jQuery(this).find("li.item");
		   if (items.length == 1){
			   items.first().find("a.append").addClass("disabled");
		   }
	   });
  }
};

jQuery.fn.anthologizeSortList = function (options){
  
  var settings = jQuery.extend({
    placeholder: 'anthologize-drop-item',
    start: function(event, ui){
      anthologize.src_id = null;
      anthologize.org_seq_num = ui.item.index() + 1;
	    offset = anthologize.org_seq_num;
	    anthologize.src_seq = {};
	    anthologize.src_seq[anthologize.cleanPostIds(ui.item.attr("id"))] = offset;
	    ui.item.nextAll().each(function(){
		     if (! jQuery(this).hasClass("anthologize-drop-item")){
	         offset++;
	         anthologize.src_seq[anthologize.cleanPostIds(jQuery(this).attr("id"))] = offset;
         }
	    });
      anthologize.fromNew = false;
      anthologize.newItem = null;
      ui.item.addClass("anthologize-drag-selected");
    },
    stop: function (event, ui){
	    anthologize.newItem = ui.item;
      anthologize.callBack(event, ui);
      ui.item.removeClass("anthologize-drag-selected");
    },
    receive: function(event, ui){
      anthologize.src_id = ui.sender.closest("li").attr('id');
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
  jQuery("#sidebar-posts li").draggable({
    connectToSortable: ".part-items ul",
    helper: "clone",
    revert: "invalid",
    start: function(event, ui){
      anthologize.new_item_org_seq_num = jQuery(this).index() + 1;
    },
    drag: function(event, ui){
	    if (anthologize.fromNew == false){
		    anthologize.fromNew = true;
	    }
    }
  });

  jQuery("body").delegate("a.append", "click", function(){
	  var item = jQuery(this).closest("li.item");
    if (anthologize.appending == false && ! jQuery(this).hasClass("disabled")){
	    jQuery(this).addClass("active-append");
		  var appendPanel = '<div class="append-panel"><form><div class="append-items"></div>' +
		                    '<input type="button" class="doAppend" name="doAppend" value="Append" /> ' + 
		                    '<a href="#" class="cancelAppend">Cancel</a></form></div>';
		  item.append(appendPanel);
		  var panel = item.find("div.append-items").first();
		  var appendable = anthologize.getAppendableItems(item.attr("id"));
		  for (var itemId in appendable){
			  panel.append('<input type="checkbox" name="append[append-' + itemId + ']" id="append-' + itemId + '"  value="' + itemId + '"/> <label for="append-' + itemId+ '">' + appendable[itemId] + '</label><br />');
		  }
		
		  jQuery(".project-parts").sortable("disable");
		  jQuery(".part-items ul").sortable("disable");
		  jQuery("a.append").addClass("disabled");
		  anthologize.appending = true;
	  }
  });
 
  jQuery("body").delegate("a.cancelAppend", "click", function(){
	  var item = jQuery(this).closest("li.item");
	  jQuery(this).parents("li.item").find("a.append").removeClass("active-append");
	  jQuery("div.append-panel").remove();
	  jQuery(".project-parts").sortable("enable");
	  jQuery(".part-items ul").sortable("enable");
	  //jQuery("a.append").removeClass("disabled");
	  anthologize.setAppendStatus();
	  anthologize.appending = false;
  });

  jQuery("body").delegate("input.doAppend", "click", function(){
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
});