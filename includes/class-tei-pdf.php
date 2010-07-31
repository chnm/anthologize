// TeiPdf - Generates PDF from internal, hybridized TEI.
//
// This file is part of Anthologize
//
// Written and maintained by Stephen Ramsay <sramsay.unl@gmail.com>
//
// Last Modified: Sat Jul 31 08:14:13 EDT 2010
//
// Copyright (c) 2010 Center for History and New Media, George Mason
// University.
//
// TeiPdf is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3, or (at your option) any
// later version.
//
// TeiPdf is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License
// along with TeiPdf; see the file COPYING.  If not see
// <http://www.gnu.org/licenses/>.
<?php

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'eng.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', 'http://www.anthologize.org/ns');

class TeiPdf {

	public $tei;
	public $pdf;
	public $xpath;

	function __construct($tei_dom) {

		// Creates an object of type DOMDocument
		// and exposes it as the attribute $tei
		$this->tei = new DOMDocument();

    $this->tei->loadXML($tei_dom->getTeiString());

		$this->xpath = new DOMXpath($this->tei);
    $this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->xpath->registerNamespace('anth', ANTH);

		$paper_size = $this->get_paper_size();

		$this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $paper_size, true, 'UTF-8', false);

// -------------------------------------------------------- //

		//set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$this->pdf->setLanguageArray($l);

		$this->set_docinfo();
		$this->set_font();
		$this->set_margins();

	}

	public function write_pdf() {

		$this->pdf->AddPage();

		//$book_title = $this->xpath->query("/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title")->item(0)->textContent;
		
		// Create a nodeList containing all parts.
		$parts = $this->xpath->query("//tei:div[@type='part']");

		foreach ($parts as $part) {
			// Grab the main title for each part and render it as
			// a "chapter" title.
			$title = $this->xpath->query("tei:head/tei:title", $part)->item(0);
			$html = $html . "<h1>" . $title->textContent . "</h1>";

			// Create a nodeList containing all libraryItems
			$library_items = $this->xpath->query("tei:div[@type='libraryItem']", $part);

			foreach ($library_items as $item) {
				// Grab the main title for each libraryItem and render it
				// as a "sub section" title.
				$sub_title = $this->xpath->query("tei:head/tei:title", $item)->item(0);
				$html = $html . "<h3>" . $sub_title->textContent . "</h3>";

				// Grab all paragraphs
				$paras = $this->xpath->query("html:body/*", $item);

				foreach ($paras as $para) {

					$strip1 = $this->strip_whitespace($this->node_to_string($para));
					$strip2 = strip_shortcodes($strip1);

					$html = $html . $strip1;

				} // foreach para

			} // foreach item

		} // foreach part

		$this->pdf->WriteHTML($html, true, 0, true, 0);

		// Close and output PDF document
		// This method has several options, check the source code
		// documentation for more information.

		//echo $html; // DEBUG
		$this->pdf->Output('example.pdf', 'I');

	} // writePDF 

	public function set_header() {

		// set default header data
		$this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

	}

	public function set_footer() {

		// set header and footer fonts
		$this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	}

	public function set_docinfo() {

		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('One Week | One Tool');
		$this->pdf->SetTitle('An Amazing Example of PDF Generation');
		$this->pdf->SetSubject('Barbecue');
		$this->pdf->SetKeywords('Boone, barbecue, oneweek, pants');

	}

	public function set_font() {

		// set default monospaced font
		$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set default font subsetting mode
		$this->pdf->setFontSubsetting(true);

		$font_family = $this->xpath->query("/tei:TEI/tei:teiHeader/anth:outputParams/anth:param[@name='font-family']")->item(0);
		$font_size = $this->xpath->query("/tei:TEI/tei:teiHeader/anth:outputParams/anth:param[@name='font-size']")->item(0);

		$font_family = $font_size->textContent;
		$font_size= $font_size->textContent;

		$this->pdf->SetFont('times', '', 12, '', true);

	}

	public function set_margins() {

		$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	}

	private function node_to_string($node) {
		return $this->tei->saveXML($node);
	}

	private function strip_whitespace($target) {
		return preg_replace('/\s+/', ' ', $target);
	}

	private function strip_shortcodes($target) {

		$shortcode = get_shortcode_regex();
		return preg_replace($shortcode, '', $target);

	}

	private function get_paper_size() {
		
		$paper_size = $this->xpath->query("/tei:TEI/tei:teiHeader/anth:outputParams/anth:param[@name='paper-type']")->item(0);

		return $paper_size->textContent;

	}

} // TeiPdf

// -------------------------------------------------------- //

?>
