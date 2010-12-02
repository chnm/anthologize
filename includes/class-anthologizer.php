<?php

abstract class Anthologizer {

	public $api;
	public $output;

	public function __construct($api) {
		$this->api = $api;
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
		//override this method to do additional processing
		$html = $this->api->getSectionPartItemContent($section, $partNo, $itemNo);
		return $html;
	}

}
