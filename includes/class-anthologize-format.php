<?php

abstract class Anthologize_Format {
	protected $id;
	protected $label;
	protected $extension;
	protected $options = array();

	protected $tei_dom;
	protected $tei_api;
	protected $tei_save_path;

	protected function setup() {
		include( ANTHOLOGIZE_INCLUDES_PATH . 'class-anthologizer.php' );
	}

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

	abstract public function generate_export( TeiApi $tei_api );

}
