<?php

require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');


class TeiPdf {

	public $tei;
	public $pdf;

	function __construct($wpContent = null) {

		// Creates an object of type DOMDocument
		// and exposes it as the attribute $tei
		$this->tei = new DOMDocument(); 
	  $this->tei->load("../templates/tei/teiBase.xml");

		$this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	}	

	public function writePDF() {

		//set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$this->pdf->setLanguageArray($l);

		// ---------------------------------------------------------

		// set default font subsetting mode
		$this->pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$this->pdf->SetFont('times', '', 12, '', true);

		// Add a page
		// This method has several options, check the source code documentation
		// for more information.
		$this->pdf->AddPage();

		// Set some content to print
		

		$xpath = new DOMXpath($this->tei);
		$xpath->registerNamespace('tei', TEI);
		$xpath->registerNamespace('html', HTML);
		$parts = $xpath->query("//tei:div[@type='part']");
		foreach ($parts as $part) {
			$title = $xpath->query("tei:head/tei:title", $part);
			$paras = $xpath->query("//html:p", $part);
			$html = $html . $this->node_to_string($title->item(0));
			foreach ($paras as $para) {
				$html = $html . $this->node_to_string($para);
			}
			$this->pdf->WriteHTML($html, false, true, true, false, "L");
		}

		// Close and output PDF document
		// This method has several options, check the source code
		// documentation for more information.

		// echo $html; // DEBUG
		$this->pdf->Output('example_001.pdf', 'I');

	} // writePDF 

	public function set_header() {

		// set default header data
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

	}

	public function set_footer() {

		// set header and footer fonts
		//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	}

	public function set_docinfo() {

		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('Boone Gorges');
		$this->pdf->SetTitle('The Book of Boone');
		$this->pdf->SetSubject('Barbecue');
		$this->pdf->SetKeywords('Boone, barbecue, oneweek');

	}

	public function set_font() {

		// set default monospaced font
		$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	}

	public function set_margins() {

		//set margins
		$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	}

	private function node_to_string($node) {
		return $this->tei->saveXML($node);
	}

	//private function strip_whitespace($string) {
	//	return preg_replace('/\w+/', ' ');

	//}


} // TeiPdf

$pdf_output = new TeiPdf();

$pdf_output->set_header();
$pdf_output->set_footer();
$pdf_output->set_docinfo();
$pdf_output->set_font();
$pdf_output->set_margins();

$pdf_output->writePDF();

?>
