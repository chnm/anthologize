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

function anth_the_person($role = 'author') {
	echo anth_get_the_person($role);
}

function anth_get_the_person($role = 'author') {

	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if(false !== $itemCount) {

		switch ($role) {
			case 'author':
				return $api->getSectionPartItemOriginalAuthor($section, $partN, $itemN);
			break;

			case 'anthologizer':
					return $api->getSectionPartItemAnthologizer($section, $partN, $itemN);
			break;

			case 'assertedAuthor':
				$author = $api->getSectionPartItemAssertedAuthor($section, $partN, $itemN);
				if($author && ($author != '')) {
					return $author;
				}
			break;
		}



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

function anth_the_person_gravatar_url($role = 'author') {
	echo anth_get_the_person_gravatar_url();

}

function anth_get_the_person_gravatar_url($role = 'author') {
	return anth_get_the_person_detail('gravatarUrl', $role);
}

function anth_person_details($role = 'author') {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $person_details;

	if(false !== $itemCount) {
		switch ($role) {
			case 'author':
				$person =  $api->getSectionPartItemOriginalAuthor($section, $partN, $itemN, false);
			break;

			case 'anthologizer':
				$person = $api->getSectionPartItemAnthologizer($section, $partN, $itemN, false);
			break;

			case 'assertedAuthor':
				$person = $api->getSectionPartItemAssertedAuthor($section, $partN, $itemN, false);
			break;
		}

	}

	$person_details = $api->getDetailsByRef($person['atts']['ref']);
}



function anth_get_the_person_detail($detail, $role = 'author') {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $person_details;

	if(! isset($person_details)) {

		if(false !== $itemCount) {
			$person =  anth_get_the_person($role);
		}

		$person_details = $api->getDetailsByRef($person['atts']['ref']);
	}

	return $api->getPersonDetail($person_details, $detail);
}



/* Functions requiring structured content information */







/* Functions requiring subject data */


function anth_tags() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $tags;
	global $tagIndex;

	if(! is_array($tags) ) {
		$tags = $api->getSectionPartItemTags($section, $partN, $itemN);
		$tagIndex = -1;
	}


	if($tags) {
		$tagIndex++;
		if($tagIndex >= count($tags)) {
			unset($tags);
			$tagIndex = -1;
			return false;
		} else {
			return true;
		}
	}
}

/**
 * sets the deep data array for the tag. access details via anth_tag_detail($detail)
 */

function anth_tag_details() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $tags;
	global $tagIndex;
	global $tag_details;


	if(is_array($tags) && isset($tags[$tagIndex] ) ) {
		$ref = $tags[$tagIndex]['atts']['ref'];
		$tag_details = $api->getDetailsByRef($ref);
	}
}


function anth_get_the_tag() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $tags;
	global $tagIndex;

	return $tags[$tagIndex]['spans'][0]['value'];

}

function anth_the_tag() {

	echo anth_get_the_tag();
}

function anth_get_the_tag_detail($detail) {
	global $tag_details;

	switch($detail) {
		case 'count':
			$retValue = $tag_details['nums'][0]['value'];
		break;

		case 'description':
			$retValue = $tag_details['descs'][0]['divs'][0]['value'];
		break;

		case 'url':
			$retValue = $tag_details['idents'][0]['value'];
		break;
	}
	return $retValue;

}

function anth_the_tag_detail($detail) {
	echo anth_get_the_tag_detail($detail);
}



function anth_categories() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $categories;
	global $catIndex;

	if(! is_array($categories) ) {
		$categories = $api->getSectionPartItemCategories($section, $partN, $itemN);
		$catIndex = -1;
	}


	if($categories) {
		$catIndex++;
		if($catIndex >= count($categories)) {
			unset($categories);
			$catIndex = -1;
			return false;
		} else {
			return true;
		}
	}
}

function anth_category_details() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $categories;
	global $catIndex;
	global $cat_details;


	if(is_array($categories) && isset($categories[$catIndex] ) ) {
		$ref = $categories[$catIndex]['atts']['ref'];
		$cat_details = $api->getDetailsByRef($ref);
	}
}



function anth_get_the_category() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $categories;
	global $catIndex;

	return $categories[$catIndex]['spans'][0]['value'];
}


function anth_the_category() {
	echo anth_get_the_category();
}


function anth_get_the_category_detail($detail) {
	global $cat_details;

	switch($detail) {
		case 'count':
			$retValue = $cat_details['nums'][0]['value'];
		break;

		case 'description':
			$retValue = $cat_details['descs'][0]['divs'][0]['value'];
		break;

		case 'url':
			$retValue = $cat_details['idents'][0]['value'];
		break;
	}
	return $retValue;
}

function anth_the_category_detail($detail) {
	echo anth_get_the_category_detail($detail);
}


/* Indexing functions */



function anth_index($name) {
	global $api;
	global $index;
	global $indexItemIndex;

	$indexIndex = -1;
	$index = $api->getIndex($name);

}

function anth_index_items() {
	global $api;
	global $index;
	global $indexItemIndex;


	if($index) {
		$indexItemIndex++;
		if($indexItemIndex >= $api->getIndexItemCount($index)) {
			unset($index);
			$IndexItemIndex = -1;
			return false;
		} else {
			return true;
		}
	}
}

function anth_index_item() {
	global $api;
	global $index;
	global $indexItemIndex;
	global $indexItem;
	global $indexItemTargetIndex;

	$indexItemTargetIndex = -1;
	$indexItem =  $api->getIndexItem($index, $indexItemIndex);
}

function anth_index_get_the_item_label() {
	global $api;
	global $indexItem;

	return $api->getIndexItemLabel($indexItem);

}

function anth_index_the_item_label() {
	global $api;
	global $indexItem;

	echo anth_index_get_the_item_label();
}

function anth_index_item_ref() {
	global $api;
	global $indexItem;
	global $indexItemRef;

	$indexItemRef = $api->getIndexItemRef($indexItem);

}

function anth_index_item_targets() {
	global $api;
	global $indexItem;
	global $indexItemTargetIndex;

	if($indexItem) {
		$indexItemTargetIndex++;
		if($indexItemTargetIndex >= $api->getIndexItemTargetCount($indexItem)) {
			unset($indexItem);
			$indexItemTargetIndex = -1;
			return false;
		} else {
			return true;
		}
	}
}

function anth_index_item_target() {
	global $api;
	global $indexItem;
	global $indexItemTarget;
	global $indexItemTargetIndex;

	$indexItemTarget = $api->getIndexItemTarget($indexItem, $indexItemTargetIndex);
}


function anth_index_item_get_the_target($detail = 'label') {
	global $api;
	global $indexItemTarget;

	return $api->getIndexItemTargetDetail($indexItemTarget, $detail);
}



function anth_index_item_the_target($detail = 'label') {
	echo anth_index_item_get_the_target($detail);
}




