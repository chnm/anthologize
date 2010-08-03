<?php

define('L_BRACKET', '<');
define('R_BRACKET', '>');
define('R_BRACKET', '>');
define('B_SLASH', '/');
define('SPACE', ' ');
define('EQUALS', '=');
define('D_QUOTE', '"');

$legal_tags = array("a", "b", "blockquote", "br", "dd", "del", "div", "dl", "dt", "em", "font", "h1", "h2", "h3", "h4", "h5", "h6", "hr", "i", "img", "li", "ol", "p", "pre", "small", "span", "strong", "sub", "sup", "table", "tcpdf", "td", "th", "thead", "tr", "tt", "u", "ul");

function startElemHandler($parser, $name, $attrs) {

	global $legal_tags;
	global $html;


	if (in_array($name, $legal_tags)) {
		if (count($attrs) == 0) {
			$html = $html . L_BRACKET . $name . R_BRACKET;
		} else {
			$html = $html . L_BRACKET . $name;
			foreach ($attrs as $key => $value) {
				$html = $html . SPACE . $key . EQUALS . D_QUOTE . $value . D_QUOTE;
			}
			$html = $html . R_BRACKET;
		}
	}

}

function endElemHandler($parser, $name) {

	global $legal_tags;
	global $html;

	if (in_array($name, $legal_tags)) {

		$html = $html . L_BRACKET . B_SLASH . $name . R_BRACKET;

	}

}

function characterData($parser, $data) {

	global $html;

	$html = $html . $data;

	return $html;

}

function filter_html($html) {

	global $html;

	$parser = xml_parser_create();

	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
	xml_set_element_handler($parser, startElemHandler, endElemHandler);
	xml_set_character_data_handler($parser, "characterData");

	xml_parse($parser, $html);

	return $html;

}

?>
