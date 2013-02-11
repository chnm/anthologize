<?php

class Anthologize_PDF extends Anthologize_Format {
	public function __construct() {
		$this->set_id( 'pdf' );
		$this->set_name( __( 'PDF', 'anthologize' ) );
	}
}
