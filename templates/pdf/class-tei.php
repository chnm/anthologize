<?php
/**
* TeiAPI - Wrapper API for the Antholize TEI DOM.
*
* This file is part of Anthologize {@link http://anthologize.org}.
*
* @author One Week | One Tool {@link http://oneweekonetool.org/people/}
*
* Last Modified: Thu Aug 05 15:06:19 CDT 2010
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

$class_tei_dom = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php';

include_once($class_tei_dom);

class TeiAPI {

	private $tei;
	private $xpath;

	public function __construct() {

		// Creates an object of type DOMDocument
		// and exposes it as the attribute $tei
		$this->tei = new DOMDocument();


		//setup options for TEIDOM
		$ops = array('includeStructuredSubjects' => true, //Include structured data about tags and categories
				'includeItemSubjects' => true, // Include basic data about tags and categories
				'includeCreatorData' => true, // Include basic data about creators
				'includeStructuredCreatorData' => true, //include structured data about creators
				'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories)
				'checkImgSrcs' => true, //whether to check availability of image sources
				'linkToEmbeddedObjects' => false,
				'indexSubjects' => false,
				'indexCategories' => false,
				'indexTags' => false,
				'indexAuthors' => false,
				'indexImages' => false,
				);


		$ops['outputParams'] = $_SESSION['outputParams'];

		$tei_dom = new TeiDom($_SESSION, $ops);
		$this->tei->loadXML($tei_dom->getTeiString());

		$this->xpath = new DOMXpath($this->tei);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->xpath->registerNamespace('anth', ANTH);

	}

	/**
	 * Return document title/subtitle.
	 *
	 * /tei:TEI/tei:text/tei:front/tei:titlePage/tei:docTitle/tei:titlePart[@type='".$type."']/text()
	 *
	 */

	public function get_book_title($type = 'main') {

		return $this->xpath->query("/tei:TEI/tei:text/tei:front/tei:titlePage/tei:docTitle/tei:titlePart[@type='".$type."']")->item(0)->textContent;

	}

	/**
	*
	*
	* /tei:TEI/tei:text/tei:front/tei:titlePage/tei:docAuthor/text()
	*
	*/

	public function get_book_author(){

		return $this->xpath->query("/tei:TEI/tei:text/tei:front/tei:titlePage/tei:docAuthor")->item(0)->textContent;

	}

	public function get_availability(){

		return $this->xpath->query("/tei:TEI/tei:teiHeader/tei:fileDesc/tei:publicationStmt/tei:availability")->item(0)->textContent;

	}

	/**
	*
	* tei:head/tei:title/text()
	*
	*/

	public function get_title($context) {

		return $this->xpath->query("tei:head/tei:title", $context)->item(0)->textContent;

	}

	/**
	*
	* tei:div[@type='libraryItem']
	*
	*/

	public function get_parts() {

		return $this->xpath->query("//tei:div[@type='part']");

	}

	public function get_div($type, $context) {

		return $this->xpath->query("tei:div[@type='$type']", $context);

	}

	public function get_html($context) {

		$html_nodes   = $this->xpath->query("html:body/*", $context);

		$html = "";

		foreach ($html_nodes as $node) {

			$html_string = $this->node_to_string($node);
		  $html_string = $this->strip_whitespace($html_string);

			$html .= $html_string;

		}

		return $html;


	}

	public function get_paper_size() {

		return $this->xpath->query("/tei:TEI/tei:teiHeader/anth:outputParams/anth:param[@name='paper-type']")->item(0)->textContent;

	}

	public function get_font_family() {

		return $this->xpath->query("/tei:TEI/tei:teiHeader/anth:outputParams/anth:param[@name='font-family']")->item(0)->textContent;

	}

	public function get_font_size() {

		return (int)$this->xpath->query("/tei:TEI/tei:teiHeader/anth:outputParams/anth:param[@name='font-size']")->item(0)->textContent;

	}

	private function node_to_string($node) {

		return $this->tei->saveXML($node);

	}

	private function strip_whitespace($target) {

		return preg_replace('/\s+/', ' ', $target);

	}

}
