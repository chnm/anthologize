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

	//TODO branch around whether the auther has been manually set in the export UI

	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if(false !== $itemCount) {
		$author = $api->getSectionPartItemAnthAuthor($section, $partN, $itemN);
		if($author && ($author != '')) {
			return $author;
		}
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

	$author_meta = $api->getDetailsByRef($author['atts']['ref']);
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

		$author_meta = $api->getDetailsByRef($author['atts']['ref']);
	}

	return $api->getPersonDetail($author_meta, $detail);
}


function anth_anthologizer_meta() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $anthologizer_meta;

	if(false !== $partCount) {
		//$anthologizer = $api->getSectionPartCreator($section, $partN, false); TODO
	}

	if(false !== $itemCount) {
		$anthologizer =  $api->getSectionPartItemOriginalCreator($section, $partN, $itemN, false);
	}

	$anthologizer_meta = $api->getDetailsByRef($anthologizer['atts']['ref']);
}

function anth_the_anthologizer() {
	echo anth_get_the_anthologizer();
}

function anth_get_the_anthologizer() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if(false !== $itemCount) {
		return $api->getSectionPartItemAnthologizer($section, $partN, $itemN);
	}

	if(false !== $partCount) {
		//return $api->getSectionPartAnthologizer($section, $partN); TODO
	}

	return false;
}

function anth_get_the_anthologizer_detail($detail) {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $anthologizer_meta;

	if(! isset($anthologizer_meta)) {

		if(false !== $partCount) {
			//$author = $api->getSectionPartCreator($section, $partN, false); TODO
		}

		if(false !== $itemCount) {
			$anthologizer =  $api->getSectionPartItemOriginalCreator($section, $partN, $itemN, false);
		}

		$anthologizer_meta = $api->getDetailsByRef($anthologizer['atts']['ref']);
	}


	return $api->getPersonDetail($author_meta, $detail);
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

function anth_tag_meta() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $tags;
	global $tagIndex;
	global $tag_meta;


	if(is_array($tags) && isset($tags[$tagIndex] ) ) {
		$ref = $tags[$tagIndex]['atts']['ref'];
		$tag_meta = $api->getDetailsByRef($ref);
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
	global $tag_meta;

	switch($detail) {
		case 'count':
			$retValue = $tag_meta['nums'][0]['value'];
		break;

		case 'description':
			$retValue = $tag_meta['descs'][0]['divs'][0]['value'];
		break;

		case 'url':
			$retValue = $tag_meta['idents'][0]['value'];
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

function anth_category_meta() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;
	global $categories;
	global $catIndex;
	global $cat_meta;


	if(is_array($categories) && isset($categories[$catIndex] ) ) {
		$ref = $categories[$catIndex]['atts']['ref'];
		$cat_meta = $api->getDetailsByRef($ref);
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
	global $cat_meta;

	switch($detail) {
		case 'count':
			$retValue = $cat_meta['nums'][0]['value'];
		break;

		case 'description':
			$retValue = $cat_meta['descs'][0]['divs'][0]['value'];
		break;

		case 'url':
			$retValue = $cat_meta['idents'][0]['value'];
		break;
	}
	return $retValue;
}

function anth_the_category_detail($detail) {
	echo anth_get_the_category_detail($detail);
}






