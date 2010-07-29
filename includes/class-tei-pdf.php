<?php

require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

define('TEI', 'http://www.tei-c.org/ns/1.0');


class TeiPdf {

	public $tei;

	function __construct($wpContent = null) {

		# Reading from text file for now
		$tei = new DOMDocument(); 
	  $tei->load("../templates/tei/teiBase.xml");


	}	

	public function writePDF() {

		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Stephen Ramsay');
		$pdf->SetTitle('The Book of Boone');
		$pdf->SetSubject('Barbecue');
		$pdf->SetKeywords('Boone, barbecue, oneweek');

		// set default header data
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

		// set header and footer fonts
		//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$pdf->setLanguageArray($l);

		// ---------------------------------------------------------

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('times', '', 12, '', true);

		// Add a page
		// This method has several options, check the source code documentation
		// for more information.
		$pdf->AddPage();

		// Set some content to print
		$xpath = new DOMXpath($this->tei);
		$xpath->registerNamespace('tei', TEI);

		$titles = $xpath->query("//tei:title");
		foreach ($titles as $title) {
			$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $title->nodeValue, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
		}

// Print text using writeHTMLCell()

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');

	}

	}

$pdf_output = new TeiPdf();

$pdf_output->writePDF();


?>
