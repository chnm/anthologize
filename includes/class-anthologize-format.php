<?php

abstract class Anthologize_Format {
	protected $id;
	protected $label;
	protected $extension;
	protected $options = array();

	protected $tei_dom;

	/**
	 * Set the unique id of this format
	 *
	 * @since 0.8
	 * @todo Ensure uniqueness?
	 *
	 * @param string $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Set the label of this format
	 *
	 * @since 0.8
	 *
	 * @param string $label
	 */
	public function set_label( $label ) {
		$this->label = $label;
	}

	/**
	 * Set the file extension of this format
	 *
	 * @since 0.8
	 * @todo Sanitize? Check against WP mime types?
	 *
	 * @param string $extension
	 */
	public function set_extension( $extension ) {
		$this->extension = $extension;
	}

	public function set_options( $options ) {
		$this->options = (array) $options;
	}

	public function __get( $property ) {
		if ( isset( $this->{$property} ) ) {
			return $this->{$property};
		} else {
			return false;
		}
	}

	protected function includes() {

require_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-anthologizer.php');
	}

	public function start() {
		$this->tei_dom = $this->get_tei_dom( $_SESSION );
		$this->tei_api = $this->get_tei_api();
	}

	abstract protected function setup();

	/**
	 * Generate the TEI DOM object
	 *
	 * @since 0.8
	 */
	protected function generate_tei_dom( $session_array ) {
		if ( empty( $this->tei_dom ) ) {
			if ( ! class_exists( 'TeiDom' ) ) {
				require( ANTHOLOGIZE_TEIDOM_PATH );
			}

			$this->tei_dom = new TeiDom( $session_array, $this->options );
		}
	}

	/**
	 * Fetch the TEI DOM object
	 *
	 * @since 0.8
	 */
	public function get_tei_dom( $session_array ) {
		if ( empty( $this->tei_dom ) ) {
			$this->generate_tei_dom( $session_array );
		}

		return $this->tei_dom;
	}

	/**
	 * Generate the TEI API object
	 *
	 * @since 0.8
	 */
	protected function generate_tei_api() {
		if ( empty( $this->tei_api ) ) {
			if ( ! class_exists( 'TeiApi' ) ) {
				require( ANTHOLOGIZE_TEIDOMAPI_PATH );
			}

			$this->tei_api = new TeiApi( $this->get_tei_dom() );
		}
	}

	/**
	 * Fetch the TEI API object
	 *
	 * @since 0.8
	 */
	public function get_tei_api() {
		if ( empty( $this->tei_api ) ) {
			$this->generate_tei_api();
		}

		return $this->tei_api;
	}

	public function save_tei_to_disk() {

	}
}
