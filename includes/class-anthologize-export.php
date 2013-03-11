<?php

class Anthologize_Export {
	protected $format;
	protected $project;

	public function __construct( $options = array() ) {
		if ( isset( $options['project_id'] ) ) {
			$this->set_project( $options['project_id'] );
		}

		if ( isset( $options['timestamp'] ) ) {

			// This is an existing export
			$this->timestamp = $options['timestamp'];
			// @todo Pull it up if stored in the database?

		} else if ( isset( $options['format_id'] ) ) {

			// Creating a new export based on a project & format
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
	public function set_format( $format_id ) {
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

	public function get_export_directory_path() {
		if ( empty( $this->export_dir) ) {
			$project_dir = $this->project->get_export_directory_path();

			if ( empty( $this->timestamp ) ) {
				// We use a human-readable timestamp for export identification
				$this->timestamp = date( 'Y-m-d-His' );
			}

			$this->export_dir = $project_dir . DIRECTORY_SEPARATOR . $this->timestamp;

			if ( ! wp_mkdir_p( $this->export_dir ) ) {
				return false;
			}
		}

		return $this->export_dir;
	}

	public function generate_export() {

		// load teiapi object and pass to export generator
		$xml_path = $this->get_tei_save_path();

		if ( ! class_exists( 'TeiApi' ) ) {
			require_once( ANTHOLOGIZE_INCLUDES_PATH . 'class-tei-api.php' );
		}

		// todo this can be moved out
		define('TEI', 'http://www.tei-c.org/ns/1.0');
		define('HTML', 'http://www.w3.org/1999/xhtml');
		define('ANTH', 'http://www.anthologize.org/ns');

		$tei_api = new TeiApi();
		$tei_api->set_tei( $xml_path );
		$tei_api->xpath = new DOMXPath($tei_api->tei);
		$tei_api->xpath->registerNamespace('tei', TEI);
		$tei_api->xpath->registerNamespace('html', HTML);
		$tei_api->xpath->registerNamespace('anth', ANTH);

		$export = $this->format->generate_export( $tei_api );
	}

}
