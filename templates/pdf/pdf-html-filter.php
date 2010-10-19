<?php
/**
* pdf-html-filter - Preprocessing filter for Wordpress HTML content.
*
* This file is part of Anthologize {@link http://anthologize.org}.
*
* @author One Week | One Tool {@link http://oneweekonetool.org/people/}
*
* Last Modified: Fri Aug 06 15:44:05 CDT 2010
*
* @copyright Copyright (c) 2010 Center for History and New Media, George Mason
* University.
*
* Anthologize is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3, or (at your option) any
* later version.
*
* Anthologize is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
* for more details.
*
* You should have received a copy of the GNU General Public License
* along with Anthologize; see the file license.txt.  If not see
* {@link http://www.gnu.org/licenses/}.
*
* @package anthologize
*/

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
			$html .= L_BRACKET . $name . R_BRACKET;
		} else {
			$html .= L_BRACKET . $name;
			foreach ($attrs as $key => $value) {
				$html .= SPACE . $key . EQUALS . D_QUOTE . $value . D_QUOTE;
			}
			$html .= R_BRACKET;
		}
		if ($name == "img") {
			$html = "<p>" . $html;
		}
	}
}

function endElemHandler($parser, $name) {

	global $legal_tags;
	global $html;

	if (in_array($name, $legal_tags)) {

		$html .= L_BRACKET . B_SLASH . $name . R_BRACKET;

		if ($name == "img") {
			$html .= "</p>";
		}
	}
}

function characterData($parser, $data) {

	global $html;

	$html .= $data;

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
