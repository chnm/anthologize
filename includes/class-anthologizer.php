<?php

/*
 * @abstract Anthologizer is an abstract class to guide quick creation of outputs
 * @uses TeiApi
 * @package Anthologize
 *
 */


abstract class Anthologizer {

	/*
	 * An instance of TeiApi, which carries the TeiDom document
	 */

	public $api;

	/*
	 * The final output document
	 */

	public $output;

	function __construct($api) {
		$this->api = $api;
		$this->init();
		$this->appendFront();

		$this->appendBody();

		$this->appendBack();

		$this->finish();

	}

	/*
	 * @abstract init
	 * Does pre-processing before rolling through adding content
	 */

	abstract function init();

	/*
	 * @abstract appendFront
	 *
	 * Append front matter content to the output document
	 */

	abstract function appendFront();

	/*
	 * @abstract appendBody
	 *
	 * Append body content to the output document
	 *
	 */

	abstract function appendBody();

	/*
	 * @abstract appendBack
	 *
	 * Append back matter to the output document
	 *
	 */

	abstract function appendBack();

	/*
	 * @abstract appendPart
	 * @param string $section 'front', 'body', or 'back'
	 * @param int $partNumber the part number
	 *
	 */

	abstract function appendPart($section, $partNumber);

	abstract function appendItem($section, $partNumber, $itemNumber);

	abstract function finish();

	abstract function output();

	protected function writeItemContent($section, $partNo, $itemNo) {
		//override this method to do additional processing
		$html = $this->api->getSectionPartItemContent($section, $partNo, $itemNo);
		return $html;
	}

}
