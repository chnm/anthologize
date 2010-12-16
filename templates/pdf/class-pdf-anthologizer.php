<?php

class PdfAnthologizer extends Anthologizer {

	public $partH = '16';
	public $itemH = '12';
	public $headerLogo = 'med-logo.png'; //TCPDF looks for this in /tcpdf/images
	public $headerLogoWidth = '10';
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

		$this->output->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->output->setFooterFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));


		// set default monospaced font
		$this->output->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set default font subsetting mode
		$this->output->setFontSubsetting(false);

		$font_family = $this->api->getProjectOutputParams('font-face');
		$this->baseH = $this->api->getProjectOutputParams('font-size');

		if(strpos($font_family, 'arialunicid0') !== false) {
			$font_family = 'arialunicid0';
		}

		$this->output->SetFont($font_family, '', $this->baseH, '', true);

		$this->output->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->output->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->output->SetFooterMargin(PDF_MARGIN_FOOTER);

		$this->set_header(array('logo'=>$this->headerLogo, 'logo_width'=>$this->headerLogoWidth));

		$this->partH = $this->baseH + 4;
		$this->itemH = $this->baseH + 2;

	}

	public function appendFront() {

		//add the front matter

		//title and author
		$creator = $this->api->getProjectCreator(false, false);

		$book_title = $this->api->getProjectTitle(true);
		$this->output->SetCreator("Anthologize: A One Week | One Tool Production");
		$this->output->SetAuthor($creator);
		$this->output->SetTitle($book_title);

		//subjects


		//append cover
		$this->output->AddPage();
		$this->frontPages++;
		$this->output->setFont('', 'B', $this->partH + 6);
		$this->output->SetY(80);
		$this->output->Write('', $book_title, '', false, 'C', true );
		$this->output->setFont('', '', $this->baseH);
		$this->output->Write('', $creator, '', false, 'C', true );
		$this->output->SetY(120);
		$year = substr( $this->api->getProjectPublicationDate(), 0, 4 );
		$this->output->Write('', $this->api->getProjectCopyright(false, false) . ' -- ' . $year , '', false, 'C', true );



		//dedication
		$dedication = $this->api->getSectionPartItemContent('front', 0, 0);
		if ($dedication){
			$this->output->AddPage();
			$this->output->setFont('', 'B', $this->partH);
			$titleNode = $this->api->getSectionPartItemTitle('front', 0, 0, true);
			$title = $titleNode->nodeValue;
			$this->output->write('', $title, '', false, 'C', true);
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
			$this->output->writeHTML($acknowledgements);
			$this->output->setFont('', '', $this->baseH);
			$this->frontPages++;
		}
	}


	public function appendBody() {

		$this->output->startPageGroup();
		$this->output->setPrintHeader(true);

		//actually letting appendPart and append Item do the appending
		//this just fires up the loop through the body parts

		$partsCount = $this->api->getSectionPartCount('body');
		for($partNo = 0; $partNo <$partsCount; $partNo++) {
			$this->appendPart('body', $partNo);
		}

	}

	public function appendBack() {
		$this->output->startPageGroup();
		$this->output->setPrintHeader(true);
		$partsCount = $this->api->getSectionPartItemCount('back');
		//echo $partsCount;
		//die();
		for($partNo = 0; $partNo < $partsCount; $partNo++) {
			$this->appendPart('back', $partNo);
		}
	}

	public function appendPart($section, $partNo) {
		$titleNode = $this->api->getSectionPartTitle($section, $partNo, true);
		$title = $titleNode->textContent;

		$this->set_header(array('title'=>$title, 'string'=>''));

		if($partNo == 0) {
			$this->output->AddPage();
		} elseif($this->api->getProjectOutputParams('break-parts') == 'on' )  {
			$this->output->AddPage();
		}

		//TCPDF seems to add the footer to prev. page if AddPage hasn't been fired
		$this->output->setPrintFooter(true);
		if($section == 'body') {
			$this->output->Bookmark($title);
		}

		//add the header info
		$this->appendPartHead($section, $partNo);
		//loop the items and append
		$itemsCount = $this->api->getSectionPartItemCount($section, $partNo);
		for($itemNo = 0; $itemNo < $itemsCount; $itemNo++) {
			$this->appendItem($section, $partNo, $itemNo);
		}
		$this->output->endPage();
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

		$titleNode = $this->api->getSectionPartItemTitle($section, $partNo, $itemNo, true);
		$title = $titleNode->textContent;
		$this->set_header(array('string'=>$title));

		if( ($this->api->getProjectOutputParams('break-items') == 'on') && $itemNo != 0   ) {
			$this->output->AddPage();
		}

		if($section == 'body') {
			$this->output->Bookmark($title, 1);
		}

		$this->appendItemHead($section, $partNo, $itemNo);

		//append the item content
		$content = $this->writeItemContent($section, $partNo, $itemNo);
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

	public function finish() {
		//add TOC
		$this->output->setPrintHeader(false);
		$this->output->setPrintFooter(false);
		$this->output->addTOCPage();

		$this->output->Write(0, 'Table of Contents', '', false, 'C', true);
		$this->output->addTOC($this->frontPages + 1 , '', '', 'Table of Contents');
		$this->output->endTOCPage();
	}


	public function output() {
		$filename = $this->api->getFileName() . ".pdf";
		$this->output->Output($filename, 'D');
	}


	private function set_header($array) {

		$newArray = $this->output->getHeaderData();
		foreach($array as $prop=>$value) {
			$newArray[$prop] = $value;
		}

		$this->output->setHeaderData($newArray['logo'], $newArray['logo_width'], $newArray['title'], $newArray['string']);
	}


}
