<?php

abstract class Anthologize_Format {
	protected $id;
	protected $label;
	protected $extension;
	protected $options = array();

	protected $tei_dom;
	protected $tei_api;
	protected $tei_save_path;

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
	public function get_tei_dom( $session_array = array() ) {
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
		$path = $this->get_tei_save_path();
		file_put_contents( $path, $this->get_tei_dom()->getTeiString() );
	}

	/**
	 * Get the save path for the TEI API
	 *
	 * Format: [upload-dir]/anthologize-cache/[yyyy-mm-dd-His]/tei.json
	 */
	public function get_tei_save_path() {
		if ( empty( $this->tei_save_path ) ) {
			$export_directory = $this->get_export_directory_path();
			$this->tei_save_path = $export_directory . DIRECTORY_SEPARATOR . 'tei.xml';
		}

		return $this->tei_save_path;
	}

	// @todo - This should be moved out of this class. It's not format-specific.
	public function get_export_directory_path() {
		if ( empty( $this->export_dir) ) {
			$project_dir = $this->get_project_directory_path();

			// We use a human-readable timestamp for export identification
			$this->export_timestamp = date( 'Y-m-d-His' );
			$this->export_dir = $project_dir . DIRECTORY_SEPARATOR . $this->export_timestamp;

			if ( ! wp_mkdir_p( $this->export_dir ) ) {
				return false;
			}
		}

		return $this->export_dir;
	}

	public function get_project_directory_path() {
		if ( empty( $this->project_dir) ) {
			$project_id = (int) $this->tei_dom->projectData['project_id'];
			$this->project_dir = anthologize()->cache_dir . $project_id;

			if ( ! wp_mkdir_p( $this->project_dir ) ) {
				return false;
			}
		}

		return $this->project_dir;
	}

}
