<?php

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');

class TeiMaster {

	private $tei;
	private $xpath;

	public function __construct() {

		// Creates an object of type DOMDocument
		// and exposes it as the attribute $tei
		$this->tei = new DOMDocument();

		$tei_dom = new TeiDom($_POST);

		$this->tei->loadXML($tei_dom->getTeiString());

		$this->xpath = new DOMXpath($this->tei);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->xpath->registerNamespace('anth', ANTH);

	}

	/**
	*
	* /tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title/text()
	* 
	*/

	public function get_book_title($type = 'main') {

		//return $this->xpath->query("/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title")->item(0)->textContent;
		return $this->xpath->query("/tei:TEI/tei:text/tei:front/tei:titlePage/tei:docTitle/tei:titlePart[@type='".$type."']")->item(0)->textContent;
	}
	
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

			$html = $html . $html_string;

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
