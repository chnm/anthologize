<?php

//fire up some globals to use.
global $section;
global $partN;
global $partCount;
global $itemN;
global $itemCount;

$section = false;
$partN = -1;
$partCount = false;
$itemN = -1;
$itemCount = false;

/**
 * echo the project title
 */

function anth_the_project_title($valueOnly = false) {
	echo anth_get_the_project_title($valueOnly);
}

function anth_get_the_project_title($valueOnly = false) {
	global $api;
	return $api->getProjectTitle($valueOnly);
}

/**
 * echo the project subtitle
 */

function anth_the_project_subtitle($valueOnly = false) {
	echo anth_get_the_project_subtitle($valueOnly);
}

function anth_get_the_project_subtitle($valueOnly = false) {
	global $api;
	return $api->getProjectSubTitle();
}
/**
 * set the section to loop through ('front', 'body', or 'back') and prepare to loop through parts
 */

//TODO: handle front and back sections

function anth_section($section_name) {
	global $api;
	global $section;
	global $partCount;
	$section = $section_name;
	switch($section_name) {
		case 'front':
			$partCount = 1;
		break;

		case 'body':
			$partCount = $api->getSectionPartCount($section);
		break;

		case 'back':
			$partCount = 1;
		break;
	}

}

/**
 * loop through the parts of the current section
 * like while( anth_parts() { output } )
 */

function anth_parts() {
	global $partN;
	global $partCount;
	if ($partN < $partCount - 1) {
		return true;
	}
	$partCount = false;
	$partN = -1;
	return false;

}

/**
 * step through to the next part of the section to theme the output
 */

function anth_part() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	$partN++;
	$itemCount = $api->getSectionPartItemCount($section, $partN);
}

/**
 * check whether there are any items in the part
 */

function anth_part_has_items() {
	global $itemCount;
	return false !== $itemCount;
}

/**
 * loop through the items in a part
 * like while(anth_part_items() { output } )
 */

function anth_part_items() {
	global $itemN;
	global $itemCount;
	if ($itemN < $itemCount - 1 ) {
		return true;
	}
	$itemCount = false;
	$itemN = -1;
	return false;
}

/**
 * step to the next item for theming
 */

function anth_item() {
	global $itemN;
	$itemN++;
}


/**
 * echo the title of the current thing. If in an item, the item's title, if only to part level, the part title'
 */

function anth_the_title() {
	echo anth_get_the_title();
}

function anth_get_the_title() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if($itemN != -1) {
		return $api->getSectionPartItemTitle($section, $partN, $itemN);
	}

	if($partN != -1) {
		return $api->getSectionPartTitle($section, $partN);
	}
	return false;
}

/**
 * echo the author of the item, or part
 */

function anth_the_author() {
	echo anth_get_the_author();
}

function anth_get_the_author() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if(false !== $itemCount) {
		return $api->getSectionPartItemOriginalCreator($section, $partN, $itemN);
	}

	if(false !== $partCount) {
		return $api->getSectionPartOriginalCreator($section, $partN);
	}

	return false;
}

/**
 *  item content
 */

function anth_get_the_item_content() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if(false !== $itemN) {
		return $api->getSectionPartItemContent($section, $partN, $itemN);
	}

	return false;

}

function anth_the_item_content() {
	echo anth_get_the_item_content();
}


/* Functions requiring structured author information */

function anth_the_author_gravatar_url() {
	echo anth_get_the_author_gravatar_url();

}

function anth_get_the_author_gravatar_url() {
	return anth_get_the_author_detail('gravatarUrl');
}

function anth_author_meta() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $author_meta;

	if(false !== $partCount) {
		//$author = $api->getSectionPartCreator($section, $partN, false); TODO
	}

	if(false !== $itemCount) {
		$author =  $api->getSectionPartItemOriginalCreator($section, $partN, $itemN, false);
	}

	$author_meta = $api->getPersonByRef($author['atts']['ref']);
}



function anth_get_the_author_detail($detail) {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $author_meta;

	if(! isset($author_meta)) {

		if(false !== $partCount) {
			//$author = $api->getSectionPartCreator($section, $partN, false); TODO
		}

		if(false !== $itemCount) {
			$author =  $api->getSectionPartItemOriginalCreator($section, $partN, $itemN, false);
		}

		$author_meta = $api->getPersonByRef($author['atts']['ref']);
	}


	return $api->getPersonDetail($author_meta, $detail);
}

/* Functions requiring structured content information */







/* Functions requiring subject data */




function anth_tags() {

}

function anth_tag() {

}

function anth_categories() {

}

function anth_category() {

}

function anth_get_the_tag($valueOnly = false) {

}

function anth_the_tag($valueOnly = false) {


}

function anth_get_the_tag_detail($detail) {

}

function anth_the_tag_detail($detail) {

}

function anth_get_the_category($valueOnly = false) {

}

function anth_get_the_category_detail($detail) {

}

function anth_the_category($valueOnly = false) {

}

function anth_the_category_detail($detail) {

}






