<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');

class TeiPdf {

	public $tei;

	function __construct($wpContent = null) {

		# Reading from text file for now
		$tei = new DOMDocument(); 
	  $tei ->load("../templates/tei/teiBase.xml");


	}	

	public function writePDF() {

		try {

			$pdf = new PDFlib();

			if ($pdf->begin_document("", "") == 0) {
				die("Error: " . $p->get_errmsg());
			}

			$pdf->set_info("Creator", "class-tei-pdf.php");
			$pdf->set_info("Author", "Boone Gorges");
			$pdf->set_info("Title", "The Book of Boone");

			$pdf->begin_page_ext(595, 842, "");
			$font = $pdf->load_font("Helvetica-Bold", "winansi", "");

			$pdf->setfont($font, 24.0);
			$pdf->set_text_pos(50, 700);

			$xpath = new DOMXpath($this->tei);
			$xpath->registerNamespace('tei', TEI);

			$titles = $xpath->query("//tei:title");

			foreach ($titles as $title) {
				$pdf->continue_text($title->nodeValue);
			}

			#$pdf->continue_text("(says PHP)");
			$pdf->end_page_ext("");

			$pdf->end_document("");

			$buf = $pdf->get_buffer();
			$len = strlen($buf);

		header("Content-type: application/pdf");
		header("Content-Length: $len");
		header("Content-Disposition: inline; filename=hello.pdf");

		print $buf;

		}
		catch (PDFlibException $e) {
			die("PDFlib exception occurred in hello sample:\n" .  "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .  $e->get_errmsg() . "\n");
		}
		catch (Exception $e) {
			die($e);
		}

		$pdf = 0;
		
		}
}

$pdf = new TeiPdf();

$pdf->writePDF();


?>
