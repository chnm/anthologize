<?php

require_once('../../includes/class-tei-api.php');

define('ANTHOLOGIZE_TESTS_PATH', dirname(__FILE__) );
define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', 'http://www.anthologize.org/ns');


class AnthologizeTeiApiTest extends PHPUnit_Framework_TestCase {
    
    public function setUp() {
        
        $tei = new DomDocument();
        $tei->load(ANTHOLOGIZE_TESTS_PATH . '/test.xml');
        $teiapi = new TeiApi($tei);
        $teiapi->tei = $tei;
        $teiapi->xpath = new DOMXPath($tei);
        $teiapi->xpath->registerNamespace('tei', TEI);
        $teiapi->xpath->registerNamespace('html', HTML);
        $teiapi->xpath->registerNamespace('anth', ANTH);
        $this->api = $teiapi;
    }
}