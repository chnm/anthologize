
	
jQuery(document).ready(function(){
  jQuery("body").delegate("a.append", "click", function(){
	  if (jQuery(this).children("div.append-panel").length == 0){
		  var appendPanel = '<div class="append-panel" style="display:none">Feed me!<br /><a href="#" class="cancelAppend">Cancel</a></div>';
		  var item = jQuery(this).closest("li.item");
		  item.append(appendPanel);
		  var panel = jQuery(this).children("div.append-panel").first();
		  panel.slideToggle("slow");
	  }
  });
 
  jQuery("appendPanel").delegate("a.cancelAppend", "click", function(){
	  var item = jQuery(this).closest("li.item");
	  var appendPanel = jQuery(this).children("div.append-panel").first();
	  appendPanel.slideToggle("slow");
	  item.remove("div.append-panel");
  });
});