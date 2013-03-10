<?php

class Anthologize_UnitTestCase extends WP_UnitTestCase {
	function setUp() {
		parent::setUp();

		$this->factory->project = new Anthologize_UnitTest_Factory_For_Project();
	}

	function tearDown() {
		parent::tearDown();
	}

}
