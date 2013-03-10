<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', 'http://www.anthologize.org/ns');

class AnthologizeTeiApiTest extends Anthologize_UnitTestCase {

    public function setUp() {
	parent::setUp();

	if ( ! class_exists( 'TeiApi' ) ) {
		require( ANTHOLOGIZE_TEIDOMAPI_PATH );
	}

        if ( ! class_exists( 'TeiDom' ) ) {
    	    require_once( ANTHOLOGIZE_TEIDOM_PATH );
        }

	$project_id = $this->factory->project->create();
	$tei = new TeiDom( array( 'project_id' => $project_id ) );
//        $tei = new DomDocument();
        $tei->load(ANTHOLOGIZE_TESTS_PATH . '/includes/test.xml');
        $teiapi = new TeiApi($tei);
        $teiapi->tei = $tei;
        $teiapi->xpath = new DOMXPath($tei);
        $teiapi->xpath->registerNamespace('tei', TEI);
        $teiapi->xpath->registerNamespace('html', HTML);
        $teiapi->xpath->registerNamespace('anth', ANTH);
        $this->api = $teiapi;
    }
}
