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


function anth_the_section($section_name) {
	global $api;
	global $section;
	global $partCount;
	$section = $section_name;
	$partCount = $api->getSectionPartCount($section);
}

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

function anth_the_part() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	$partN++;
	$itemCount = $api->getSectionPartItemCount($section, $partN);
}

function anth_part_has_items() {
	global $itemCount;
	return false !== $itemCount;
}

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

function anth_the_item() {
	global $itemN;
	$itemN++;
}

function anth_the_title() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if($itemN != -1) {
		echo $api->getSectionPartItemTitle($section, $partN, $itemN);
		return;
	}

	if($partN != -1) {
		echo $api->getSectionPartTitle($section, $partN);
		return;
	}
	return false;
}

function anth_the_author() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if(false !== $itemCount) {
		echo $api->getSectionPartItemCreator($section, $partN, $itemN);
		return;
	}

	if(false !== $partCount) {
		echo $api->getSectionPartCreator($section, $partN);
		return;
	}

	return false;
}

function anth_item_content() {
	global $api;
	global $section;
	global $partN;
	global $partCount;
	global $itemCount;
	global $itemN;

	if(false !== $itemN) {
		echo $api->getSectionPartItemContent($section, $partN, $itemN);
	}

	return false;

}

