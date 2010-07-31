<?php
/*
Plugin Name: Anthologize
Plugin URI: http://oneweekonetool.org
Description: Use the power of WordPress to transform your content into a book.
Version: 0.3-alpha
Author: One Week | One Tool
Author URI: http://anthologize.org
*/

/*
Copyright (C) 2010 Center for History and New Media, George Mason University

This program is free software: you can redistribute it and/or modify it under 
the terms of the GNU General Public License as published by the Free Software 
Foundation, either version 3 of the License, or (at your option) any later 
version.

This program is distributed in the hope that it will be useful, but 
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for 
more details.

You should have received a copy of the GNU General Public License along with 
this program.  If not, see <http://www.gnu.org/licenses/>.

Anthologize includes TCPDF, which is released under the LGPL Use and 
modifications of TDPDF must comply with its license.
*/

if ( !class_exists( 'Anthologize_Loader' ) ) :

class Anthologize_Loader {

/**
* The main Anthologize loader. Hooks our stuff into WP
*/
function anthologize_loader () {

	// Give me something to believe in
	add_action( 'plugins_loaded', array ( $this, 'loaded' ) );

	add_action( 'init', array ( $this, 'init' ) );

	// Load the post types
	add_action( 'anthologize_init', array ( $this, 'register_post_types' ) );


	// Load constants
	//add_action( 'anthologize_init',  array ( $this, 'load_constants' ) );

	// Load the custom feed
	add_action( 'do_feed_customfeed', array ( $this, 'register_custom_feed' ) );


	// Include the necessary files
	add_action( 'anthologize_loaded', array ( $this, 'includes' ) );

	// Attach textdomain for localization
	add_action( 'anthologize_init', array ( $this, 'textdomain' ) );


	add_action( 'anthologize_init', array ( $this, 'load_template' ) );

	// activation sequence
	register_activation_hook( __FILE__, array( $this, 'activation' ) );

	// deactivation sequence
	register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	// Let plugins know that we're initializing
	function init() {
	do_action( 'anthologize_init' );
	}

	// Allow this plugin to be translated by specifying text domain
	function textdomain() {
	// todo: load the text domain
	}

	// Custom post types - Oh, Oh, Oh, It's Magic
	function register_post_types() {
		register_post_type( 'projects', array(
			'label' => __( 'Projects', 'anthologize' ),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => false,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "project"), // Permalinks format
		));

		register_post_type( 'parts', array(
			'label' => __( 'Parts', 'anthologize' ),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "part"), // Permalinks format
		));

		register_post_type( 'library_items', array(
			'label' => __('Library Items', 'anthologize' ),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "library_item"), // Permalinks format
		));

		register_post_type( 'imported_items', array(
			'label' => __('Imported Items', 'anthologize' ),
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "imported_item"), // Permalinks format
		));
	}

	function register_custom_feed() {
	load_template( dirname( __FILE__ ) . '/templates/customfeed.php');
	}


	function includes() {

		if ( is_admin() )
			require( dirname( __FILE__ ) . '/includes/class-admin-main.php' );

		require( dirname( __FILE__ ) . '/includes/class-ajax-handlers.php' );
		$ajax_handlers = new Anthologize_Ajax_Handlers();

	}

	// Let plugins know that we're done loading
	function loaded() {
		do_action( 'anthologize_loaded' );
	}


	function load_template() {
//		print_r($_POST); die();
		switch( $_POST['filetype'] ) {
			case 'tei' :
				load_template( WP_PLUGIN_DIR . '/anthologize/templates/tei/base.php' );
				return false;
			case 'epub' :
				load_template( WP_PLUGIN_DIR . '/anthologize/templates/epub/index.php' );
				return false;
			case 'pdf' :
				load_template( WP_PLUGIN_DIR . '/anthologize/templates/pdf/base.php' );
				return false;
		}
	}

	function grab() { // todo: make this make sense

		if ( isset( $_POST['save_project']) || ($_GET['action'] == 'delete'))
				wp_redirect( 'admin.php?page=anthologize');


		if ( $_GET['output'] == 'customfeed' ) {

		load_template( dirname( __FILE__ ) . '/templates/customfeed.php' );
		return false;
		} else if ($_GET['output'] == 'tei') {
		load_template( dirname(__FILE__) . '/templates/tei/base.php' );
		return false;
		} else if ($_GET['output'] == 'epub') {
		load_template( dirname(__FILE__) . '/templates/epub/index.php' );
		return false;
		} else if ($_GET['output'] == 'pdf') {
			load_template( dirname(__FILE__) . '/templates/pdf/base.php' );
		  return false;
		}
	}


	function activation() {}

	function deactivation() {}
	}

endif; // class exists

$anthologize_loader = new Anthologize_Loader();



?>
