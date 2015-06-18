<?php

$GLOBALS['wp_tests_options'] = array(
    'active_plugins' => array( basename( dirname( dirname( __FILE__ ) ) ) . '/anthologize.php' ),
);

define('ANTHOLOGIZE_TESTS_PATH', dirname(__FILE__) );

require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/includes/factory.php';
require dirname( __FILE__ ) . '/includes/anthologize-testcase.php';
require dirname( __FILE__ ) . '/includes/AnthologizeTeiApiTest.php';
