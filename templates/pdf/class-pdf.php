<?php
/**
* TeiPdf - Generates PDF from internal, hybridized TEI.
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

$eng = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'eng.php';
$tcpdf = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php';
$class_pdf = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'class-tei.php';
$pdf_html_filter = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR .  'pdf-html-filter.php';

require_once($eng);
require_once($tcpdf);
//require_once($class_pdf);
require_once($pdf_html_filter);

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', 'http://www.anthologize.org/ns');

class TeiPdf {

	public $tei;
	public $pdf;
	public $xpath;

	function __construct($tei_master) {

		$this->tei = $tei_master;

		$paper_size = $this->tei->getProjectOutputParams('paper-size');

		$this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $paper_size, true, 'UTF-8', false);

$lg = Array();
$lg['a_meta_charset'] = 'UTF-8';


//set some language-dependent strings
$this->pdf->setLanguageArray($lg);

		//set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$this->set_docinfo();
		$this->set_font();
		$this->set_margins();

	}

	public function write_pdf() {
		$toc_page = 3;
		$book_title = $this->tei->getProjectTitle();
		$book_subtitle = $this->tei->getProjectSubTitle();
		$book_author = $this->tei->getProjectCreator();

		// Title Page
		$this->pdf->AddPage();
		$this->set_title("h1", $book_title);
		if ($book_subtitle != '') { $this->set_sub_title("h2", $book_subtitle); }
		$this->set_title("h3", $book_author);

		// Copyright page
		$this->pdf->AddPage();
		$rights_html = "<div style=\"text-align: center;\"><p><em>".$book_title;
		if ($book_subtitle != ''){
			$rights_html .= ": ".$book_subtitle;
		}
		$rights_html .= "</em><br />";

		$book_availability = $this->tei->getProjectCopyright();
		$rights_html .= $book_availability."</p>";


		$this->pdf->WriteHTML('<div>' . $rights_html . '</div>', true, 0, true, 0);

		$dedication = $this->get_dedication();
		$acknowledgements = $this->get_acknowledgements();
		if ($dedication || $acknowledgements){
			$toc_page = 4;
    	$this->pdf->AddPage();
			$this->pdf->WriteHTML($dedication.$acknowledgements);
		}


		// Main content
		$this->pdf->AddPage();

		$partCount = $this->tei->getSectionPartCount('body');

		for ($part = 0; $part < $partCount; $part++) {
			// Grab the main title for each part and render it as
			// a "chapter" title.

			//getSectionPartTitle returns the title wrapped in a span, which pdf->Bookmark seems not to like.
			//c'mon, what's not to like?
			$titleSpan = $this->tei->getSectionPartTitle('body', $part, true);


			$title = $titleSpan->nodeValue;

			$html = "<h1>" . $title . "</h1>";

			// Create a nodeList containing all libraryItems

			$libraryItemCount = $this->tei->getSectionPartItemCount('body', $part);
			for ($libraryItem = 0; $libraryItem < $libraryItemCount; $libraryItem++) {

				// Grab the main title for each libraryItem and render it
				// as a "sub section" title.
				$sub_title = $this->tei->getSectionPartItemTitle('body', $part, $libraryItem);

				$html = $html . "<h3>" . $sub_title . "</h3>";

				// All content below <html:body>
				$post_content = $this->tei->getSectionPartItemContent('body', $part, $libraryItem);

				$html .= $post_content;

			} // foreach item
			$this->pdf->Bookmark($title);
			$this->pdf->WriteHTML($html, true, 0, true, 0);
			$this->pdf->AddPage();
		} // foreach part

		// add a new page for TOC
		$this->pdf->addTOCPage();

		// write the TOC title
		$this->pdf->WriteHTML("<h3>Table of Contents</h3>", true, 0, true, 0);

		// add TOC at page 3
		$this->pdf->addTOC($toc_page);

		// // end of TOC page
		$this->pdf->endTOCPage();

		if ( $this->tei->getProjectOutputParams('colophon') == 'on' ) {
			$colophon = $this->get_colophon();
			$this->pdf->WriteHTML($colophon);
		}

		//echo get_class($html); // DEBUG
		$filename = $this->tei->getFileName() . ".pdf";
		$this->pdf->Output($filename, 'I');

	} // writePDF

	private function set_title($h, $book_title) {

		$title_html = '<' . $h . ' style="text-align: center">' . $book_title . '</h1>';
		$this->pdf->WriteHTML($title_html, true, 0, true, 0);

	}

	private function set_sub_title($h, $book_subtitle) {
		$subtitle_html = '<h2 style="text-align: center">' . $book_subtitle . '</h2>';
		$this->pdf->WriteHTML($subtitle_html, true, 0, true, 0);

	}

	private function set_title_author($book_author) {

		$subtitle_html = '<' . $h . ' style="text-align: center">' . $book_author . '</h2>';
		$this->pdf->WriteHTML($subtitle_html, true, 0, true, 0);

	}

	private function set_header() {

		// set default header data
		$this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

	}

	private function set_footer() {

		// set header and footer fonts
		$this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	}

	private function set_docinfo() {

		$book_author = $this->tei->getProjectCreator();
		$book_title = $this->tei->getProjectTitle(true);

		$this->pdf->SetCreator("Anthologize: A One Week | One Tool Production");
		$this->pdf->SetAuthor($book_author);
		$this->pdf->SetTitle($book_title);

	}

	private function set_font() {

		// set default monospaced font
		$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set default font subsetting mode
		$this->pdf->setFontSubsetting(true);

		$font_family = $this->tei->getProjectOutputParams('font-face');
		$font_size   = $this->tei->getProjectOutputParams('font-size');
		if(strpos($font_family, 'arialunicid0') !== false) {
			$font_family = 'arialunicid0';
		}

		$this->pdf->SetFont($font_family, '', $font_size, '', true);
//		$this->pdf->SetFont('arialunicid0', '', $font_size, '', true);
	}

	private function set_margins() {

		$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	}

	private function get_colophon() {

		$day   = date(jS);
		$month = date(F);
	 	$year  = date(Y);
		$date  = "the " . $day . " of " . $month . ", " . $year;

		$logo  = WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif';

		$horace_quote = "Omne tulit punctum qui miscuit utile dulci -- Horace";

		$colophon = "<div style=\"text-align: center;\"><em>This Document was Generated on<br/>" . $date . "<br/>using<br/><br/><a href=\"http://www.anthologize.org/\"><img src=\"" . $logo . "\"\></a></em><br/><br/>" . $horace_quote . "</div>";

		return $colophon;

	}
	
	private function get_dedication(){
		$dedication_html = '';
		$dedication = $this->tei->getSectionPartItemContent('front', 0, 0);
		if ($this->tei->getSectionPartItemContent('front', 0, 0, true)->textContent){
			$dedication_html = '<h3>'.$this->tei->getSectionPartItemTitle('front', 0, 0).'</h3>';
			$dedication_html .= '<div><i>'.$dedication.'</i></div>';
		}
		
		return $dedication_html;
	}
	
	private function get_acknowledgements(){
		$acknowledgements_html = '';
		$acknowledgements = $this->tei->getSectionPartItemContent('front', 0, 1);
		if ($this->tei->getSectionPartItemContent('front', 0, 1, true)->textContent){
			$acknowledgements_html = '<h3>'.$this->tei->getSectionPartItemTitle('front', 0, 1).'</h3>';
			$acknowledgements_html .= '<div><i>'.$acknowledgements.'</i></div>';
		}
		
		return $acknowledgements_html;		
	}

} // TeiPdf


