<?php

class Anthologize_Format {
	protected $tei_dom;

	/**
	 * Generate the TEI DOM object
	 *
	 * @since 0.8
	 */
	protected function generate_tei_dom( $session_array, $options ) {
		if ( empty( $this->tei_dom ) ) {
			if ( ! class_exists( 'TeiDom' ) ) {
				require( ANTHOLOGIZE_TEIDOM_PATH );
			}

			$this->tei_dom = new TeiDom( $session_array, $options );
		}
	}

	/**
	 * Fetch the TEI DOM object
	 *
	 * @since 0.8
	 */
	public function get_tei_dom( $session_array, $options ) {
		if ( empty( $this->tei_dom ) ) {
			$this->generate_tei_dom( $session_array, $options );
		}

		return $this->tei_dom;
	}
}
