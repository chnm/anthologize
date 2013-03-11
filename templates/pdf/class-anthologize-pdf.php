<?php

class Anthologize_PDF extends Anthologize_Format {
	public function __construct() {
		$this->set_id( 'pdf' );
		$this->set_label( __( 'PDF', 'anthologize' ) );
		$this->set_extension( 'pdf' );
		$this->set_options( array(
			'includeStructuredSubjects' => false, //Include structured data about tags and categories
			'includeItemSubjects' => false, // Include basic data about tags and categories
			'includeCreatorData' => false, // Include basic data about creators
			'includeStructuredCreatorData' => false, //include structured data about creators
			'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories)
			'checkImgSrcs' => true, //whether to check availability of image sources
			'linkToEmbeddedObjects' => true,
			'indexSubjects' => false,
			'indexCategories' => false,
			'indexTags' => false,
			'indexAuthors' => false,
			'indexImages' => false,
		) );
	}

	public function generate_export( TeiApi $tei_api ) {
		parent::setup();
		$_SESSION['outputParams']['creatorOutputSettings'] = ANTHOLOGIZE_CREATORS_ALL; //@TODO: hacked in--no interface yet!

		$pdfPath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR ;
		require_once( $pdfPath . 'tcpdf-config.php' );
		require_once($pdfPath . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');
		require_once($pdfPath .  'class-anthologize-tcpdf.php'); //overrides some methods in TCPDF
		require_once($pdfPath . 'class-pdf-anthologizer.php' );

		$pdfer = new PdfAnthologizer( $tei_api );
		$pdfer->output();
	}
}
