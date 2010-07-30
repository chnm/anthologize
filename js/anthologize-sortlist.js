var anthologize = {
  "src_id": null,
  "new_item_org_seq_num": null,
  "org_seq_num": null,
  "fromNew": false,
  "callBack" : function(event, ui){
    var dest_part_id;
    //var dest_seq = "";
    var dest_seq = {};
    var offset = 1;
    var new_item = "false";
    var src_id = anthologize.src_id;

    offset = ui.item.index() + 1
    //dest_seq = ui.item.attr("id") + ":" + offset;
    dest_seq[ui.item.attr("id")] = offset;
    ui.item.nextAll().each(function(){
      offset++;
      //dest_seq += "," + jQuery(this).attr("id") + ":" + offset;
      dest_seq[jQuery(this).attr("id")] = offset;
    })

    if (ui.item.hasClass("item")){
      dest_id = ui.item.closest("li.part").attr("id");
    }else{
      dest_id = ui.item.closest("ul").attr("id");
    }

    var org_seq_num = anthologize.org_seq_num;
    // if (src_id == "sidebar-posts"){
    //   new_item = "true;"
    //   org_seq_num = anthologize.new_item_org_seq_num;
    //   ui.item.attr("id", "new-new-new");
    // }
    if (anthologize.fromNew){
      new_item = "true";
      org_seq_num = anthologize.new_item_org_seq_num;
      ui.item.attr("id", "new-new-new");
    }
    // console.log("----Do it----");
    // console.log("project_id: " + jQuery(".wrap").attr("id"))
    // console.log("src_id: " + anthologize.src_id);
    // console.log("dest_id: " + dest_id);
    // console.log("new_item: " + new_item);
    // console.log("item_id: " + ui.item.attr('id'));
    // console.log("org_seq_num: " + org_seq_num);
    // //console.log("dest_seq: " + dest_seq);
    // console.log("dest_seq:");
    // console.log(dest_seq);
    

    var ajax_options = {
	    "project_id": this.cleanPostIds(jQuery(".wrap").attr("id")),
	    "src_id": this.cleanPostIds(anthologize.src_id),
	    "dest_id": this.cleanPostIds(dest_id),
	    "new_item": new_item,
	    "item_id": this.cleanPostIds(ui.item.attr('id')),
	    "org_seq_num": org_seq_num,
	    "dest_seq":  dest_seq
    };
    console.log(ajax_options);
  },
  "cleanPostIds" : function(dom_id){
	  var clean_id = dom_id;
	  clean_id = clean_id.replace("project-", "");
	  clean_id = clean_id.replace("part-", "");
	  clean_id = clean_id.replace("item-", "");
	  return clean_id;
  },
  "getAppendableItems" : function(item_id){
	  var itemInfo = {};
	  var part = jQuery("#" + item_id).closest("li.part");
	  var items = jQuery("#" + item_id).siblings();
	  console.log(items);
	  var i = 0;
	  items.each(function(){
		  itemInfo[jQuery(this).attr("id")] = jQuery(this).find("span.part-title").text();
		  i++;
	  });
	  return itemInfo;
  }
};

jQuery.fn.anthologizeSortList = function (options){
  
  var settings = jQuery.extend({
    placeholder: 'anthologize-drop-item',
    start: function(event, ui){
      anthologize.src_id = null;
      anthologize.org_seq_num = ui.item.index() + 1;
      anthologize.fromNew = false;
      ui.item.addClass("anthologize-drag-selected");
    },
    stop: function (event, ui){
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
     
  jQuery("#sidebar-posts li").draggable({
    connectToSortable: ".part-items ul",
    helper: "clone",
    revert: "invalid",
    start: function(event, ui){
      anthologize.new_item_org_seq_num = jQuery(this).index() + 1;
      //anthologize.fromNew = true;
    },
    drag: function(event, ui){
	    if (anthologize.fromNew == false){
		    anthologize.fromNew = true;
	    }
    }
  });

  jQuery("body").delegate("a.append", "click", function(){
	  var item = jQuery(this).closest("li.item");
	  if (item.children("div.append-panel").length == 0){
		  var appendPanel = '<div class="append-panel">Feed me!<br /><a href="#" class="cancelAppend">Cancel</a></div>';
		  item.append(appendPanel);
		  var panel = jQuery(this).children("div.append-panel").first();
		  panel.slideToggle("slow");
		  var appendable = anthologize.getAppendableItems(item.attr("id"));
		  for (var itemId in appendable){
			  console.log(itemId + " - " + appendable[itemId]);
			  panel.append('<input type="checkbox" name="append[]" id="append-"' + itemId + '/> <label for="append-' + itemId+ '">' + appendable[itemId] + '</label>');
		  }
	  }
  });
 
  jQuery("body").delegate("a.cancelAppend", "click", function(){
	  var item = jQuery(this).closest("li.item");
	  var appendPanel = item.children("div.append-panel").first();
	  appendPanel.slideToggle("slow");
	  jQuery("div.append-panel").remove();
  });

});