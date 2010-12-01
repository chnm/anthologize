<?php

abstract class Anthologizer {

	public $api;
	public $output;

	public function __construct($api, $ops) {
		$this->api = $api;
		$this->ops = $ops;
		$this->init();
		$this->appendFront();
		$this->appendBody();
		$this->appendBack();
		$this->finish();
	}


	abstract function init();

	abstract function appendFront();

	abstract function appendBody();

	abstract function appendBack();

	abstract function appendPart($section, $partNumber);

	abstract function appendItem($section, $partNumber, $itemNumber);

	abstract function finish();

	abstract function output();

	public function writeItemContent($section, $partNo, $itemNo) {
		$html = $this->api->getSectionPartItemContent($section, $partNo, $itemNo);
		//TODO: do some more processing? maybe push through HTMLTidy if it is present?

		$html = $this->tidyContent($html); // TODO: should happen in TeiDom!!

		return $html;
	}

	public function tidyContent($content) {
		// if tidy is here, run it on the content
		//$content = tidified content
		//add tidy to TeiDom?
		if($this->tidy) {
			$this->tidy->parseString($content, array(), 'utf8');
			$this->tidy->cleanRepair();
			return $this->tidy;
		}
		return $content;
	}

}

class PdfAnthologizer extends Anthologizer {

	public $partH = '16';
	public $itemH = '12';
	public $tidy = false;

	public function init() {
		$page_size = $this->api->getProjectOutputParams('page-size');

		$this->frontPages = 0;
		$this->output = new AnthologizeTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $page_size, true, 'UTF-8', false);
		$lg = array();
		$lg['a_meta_charset'] = 'UTF-8';

		//set some language-dependent strings
		$this->output->setLanguageArray($lg);

		$this->output->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		$this->output->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$this->output->setPrintHeader(false);
		$this->output->setPrintFooter(false);


		$this->set_font();
		$this->set_margins();
		$this->partH = $this->baseH + 4;
		$this->itemH = $this->baseH + 2;

		if(class_exists('Tidy')) {
			$this->tidy = new Tidy();
		}

	}

	public function appendFront() {

		//add the front matter

		//title and author
		$book_author = $this->api->getProjectCreator();
		$book_title = $this->api->getProjectTitle(true);
		$this->output->SetCreator("Anthologize: A One Week | One Tool Production");
		$this->output->SetAuthor($book_author);
		$this->output->SetTitle($book_title);

		//subjects


		//append cover
		$this->appendCoverPage();

		//dedication
		$dedication = $this->api->getSectionPartItemContent('front', 0, 0);

		if ($dedication){
			$this->output->AddPage();
			$this->output->setFont('', 'B', $this->partH);
			$titleNode = $this->api->getSectionPartItemTitle('front', 0, 0, true);
			$title = $titleNode->nodeValue;
			$this->output->write('', $title, '', false, 'C', true);
			$dedication = $this->tidyContent($dedication);
			$this->output->writeHTML($dedication);
			$this->output->setFont('', '', $this->baseH);
			$this->frontPages++;
		}

		//acknowledgements
		$acknowledgements = $this->api->getSectionPartItemContent('front', 0, 1);
		if ($acknowledgements){
			$this->output->AddPage();
			$this->output->setFont('', 'B', $this->partH);
			$titleNode = $this->api->getSectionPartItemTitle('front', 0, 1, true);
			$title = $titleNode->nodeValue;
			$this->output->write('', $title, '', false, 'C', true);
			$acknowledgements = $this->tidyContent($acknowledgements);
			$this->output->writeHTML($acknowledgements);
			$this->output->setFont('', '', $this->baseH);
			$this->frontPages++;
		}

	}

	public function appendCoverPage() {
		//TODO
	}

	public function appendBody() {

		$this->output->startPageGroup();

		//actually letting appendPart and append Item do the appending
		//this just fires up the loop through the body parts

		$partsCount = $this->api->getSectionPartCount('body');
		for($partNo = 0; $partNo <$partsCount; $partNo++) {
			$this->appendPart('body', $partNo);
		}
		$this->output->endPage();
	}

	public function appendBack() {
		if(isset($this->ops['use-colophon']) && $this->ops['use-colophon']) {
			$this->output->writeHTML('colophon');
		}
	}

	public function appendPart($section, $partNo) {


		$this->set_header();

		if(isset($this->ops['break-parts']) && $this->ops['break-parts']) {
			$this->output->AddPage();
		}

		//TCPDF seems to add the footer to prev. page if AddPage hasn't been fired
		$this->set_footer();


		$titleNode = $this->api->getSectionPartTitle($section, $partNo, true);
		$title = $titleNode->textContent;
		$this->output->Bookmark($title);
		//add the header info
		$this->appendPartHead($section, $partNo);
		//loop the items and append
		$itemsCount = $this->api->getSectionPartItemCount('body', $partNo);
		for($itemNo = 0; $itemNo < $itemsCount; $itemNo++) {
			$this->appendItem($section, $partNo, $itemNo);
		}
	}


	public function appendPartHead($section, $partNo) {
		//append the header stuff, avoiding HTML methods for optimization

		$titleNode = $this->api->getSectionPartTitle($section, $partNo, true);
		$title = $titleNode->textContent;
		$this->output->setFont('', 'B', $this->partH);
		$this->output->Write('', $title, '', false, 'C', true );
		$this->output->setFont('', '', $this->baseH);

	}

	public function appendItem($section, $partNo, $itemNo) {
		//append the header stuff
		if(isset($this->ops['break-items']) && $this->ops['break-items'] && $itemNo !== 0 ) {
			$this->output->AddPage();
		}
		$titleNode = $this->api->getSectionPartItemTitle($section, $partNo, $itemNo, true);
		$title = $titleNode->textContent;

		//to get the correct page number (w/o counting front matter)

		$this->output->Bookmark($title, 1);
		$this->appendItemHead($section, $partNo, $itemNo);

		//append the item content
		$content = $this->writeItemContent('body', $partNo, $itemNo);
		$this->output->writeHTML($content, true, false, true);

	}

	public function appendItemHead($section, $partNo, $itemNo) {
		//write the head, avoiding HTML for optimization
		$titleNode = $this->api->getSectionPartItemTitle($section, $partNo, $itemNo, true);
		$title = $titleNode->textContent;

		$this->output->setFont('', 'B', $this->itemH);
		$this->output->Write('', $title, '', false, 'C', true );
		$this->output->setFont('', '', $this->baseH);

	}

	public function output() {
		header('Content-type: application/pdf');
		$filename = $this->api->getFileName() . ".pdf";
		$this->output->Output($filename, 'I');
	}

	public function finish() {
		//add TOC
		$this->output->setPrintHeader(false);
		$this->output->setPrintFooter(false);
		$this->output->addTOCPage();
		$this->output->addTOC($this->frontPages + 1, '', '', 'Table of Contents');
		$this->output->endTOCPage();
	}

	private function set_header() {
		// set default header data

		$this->output->setPrintHeader(true);
		$this->output->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->output->SetHeaderData('', '', 'Title', 'woot');
	}

	private function set_footer() {
		// set header and footer fonts

		$this->output->setPrintFooter(true);
		$this->output->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	}

	private function set_font() {

		// set default monospaced font
		//$this->output->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set default font subsetting mode
		$this->output->setFontSubsetting(false);

		$font_family = $this->api->getProjectOutputParams('font-face');
		$this->baseH = $this->api->getProjectOutputParams('font-size');

		//TODO: why would this be a substring?
		if(strpos($font_family, 'arialunicid0') !== false) {
			$font_family = 'arialunicid0';
		}

		$this->output->SetFont($font_family, '', $this->baseH, '', true);
//		$this->output->SetFont('arialunicid0', '', $this->baseH, '', true);
	}

	private function set_margins() {

		$this->output->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->output->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->output->SetFooterMargin(PDF_MARGIN_FOOTER);
	}

}
