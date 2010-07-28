<?php
/*
Plugin Name: Kitty
Plugin URI: http://oneweekonetool.org
Description: Rocks your world
Version: 0.1-alpha
Author: One Week | One Tool
Author URI: http://oneweekonetool.org
*/


if ( !class_exists( 'Booyakasha_Loader' ) ) :

class Booyakasha_Loader {

	/**
	 * The main Booyakasha loader. Hooks our stuff into WP
	 */
	function booyakasha_loader () {

		// Give me something to believe in
		add_action( 'plugins_loaded', array ( $this, 'loaded' ) );

		add_action( 'init', array ( $this, 'init' ) );

		// Load the post types
		add_action( 'booyakasha_init',  array ( $this, 'register_post_types' ) );

		// Load the custom feed
		add_action( 'do_feed_customfeed', array ( $this, 'register_custom_feed' ) );

		// Include the necessary files
		add_action( 'booyakasha_loaded', array ( $this, 'includes' ) );

		// Attach textdomain for localization
		add_action( 'booyakasha_init', array ( $this, 'textdomain' ) );


		add_action( 'booyakasha_init', array ( $this, 'grab' ) );

		// activation sequence
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// deactivation sequence
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	// Let plugins know that we're initializing
	function init() {
		do_action( 'booyakasha_init' );
	}

	// Allow this plugin to be translated by specifying text domain
	function textdomain() {
		// todo: load the text domain
	}

	// Custom post types - Oh, Oh, Oh, It's Magic
	function register_post_types() {
		register_post_type( 'books', array(
			'label' => __('Books'),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => false,
			'supports' => array('title', 'editor', 'revisions', 'page-attributes'),
			'rewrite' => array("slug" => "book"), // Permalinks format
		));

		register_post_type( 'parts', array(
			'label' => __('Parts'),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions', 'page-attributes'),
			'rewrite' => array("slug" => "part"), // Permalinks format
		));

		register_post_type( 'library_items', array(
			'label' => __('Library Items'),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions', 'page-attributes'),
			'rewrite' => array("slug" => "library_item"), // Permalinks format
		));

		register_post_type( 'imported_items', array(
			'label' => __('Imported Items'),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions', 'page-attributes'),
			'rewrite' => array("slug" => "imported_item"), // Permalinks format
		));
	}

	function register_custom_feed() {
		load_template( dirname( __FILE__ ) . '/templates/customfeed.php');
	}

	function includes() {

		if ( is_admin() )
			require( dirname( __FILE__ ) . '/includes/class-admin-main.php' );

	}

	// Let plugins know that we're done loading
	function loaded() {
		do_action( 'booyakasha_loaded' );
	}

	function grab() { // todo: make this make sense
		if ( $_GET['output'] ) {

			load_template( dirname( __FILE__ ) . '/templates/customfeed.php' );
			return false;
		}
	}


	function activation() {}

	function deactivation() {}
}

endif; // class exists

$booyakasha_loader = new Booyakasha_Loader();



?>