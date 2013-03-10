<?php

class Anthologize_Export {
	protected $format;
	protected $project;

	public function __construct( $options = array() ) {
		if ( isset( $options['id'] ) ) {
			// Fetching an existing export
		} else if ( isset( $options['project_id'] ) && isset( $options['format_id'] ) ) {
			// Creating a new export based on a project & format
			$this->set_project( $options['project_id'] );
			$this->set_format( $options['format_id'] );
		}


	}

	protected function includes() {
		require_once( anthologize()->includes_dir . DIRECTORY_SEPARATOR . 'class-anthologizer.php' );
	}

	public function start() {
		$this->tei_dom = $this->get_tei_dom( $_SESSION );
		$this->tei_api = $this->get_tei_api();
	}

	/**
	 * Set up the current project info
	 *
	 * @since 0.8
	 * @todo error checking
	 */
	protected function set_project( $project_id ) {
		$this->project = new Anthologize_Project( $project_id );
		return $this->project;
	}

	/**
	 * Set up this export's format
	 *
	 * @since 0.8
	 */
	protected function set_format( $format_id ) {
		if ( isset( anthologize()->formats[ $format_id ] ) ) {
			$this->format = anthologize()->formats[ $format_id ];
		}

		return $this->format;
	}

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

			$this->tei_dom = new TeiDom( $session_array, $this->format->options );
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
			$project_dir = $this->project->get_export_directory_path();

			// We use a human-readable timestamp for export identification
			$this->export_timestamp = date( 'Y-m-d-His' );
			$this->export_dir = $project_dir . DIRECTORY_SEPARATOR . $this->export_timestamp;

			if ( ! wp_mkdir_p( $this->export_dir ) ) {
				return false;
			}
		}

		return $this->export_dir;
	}



}
