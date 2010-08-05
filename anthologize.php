<?php
/*
Plugin Name: Anthologize
Plugin URI: http://anthologize.org
Description: Use the power of WordPress to transform your content into a book.
Version: 0.3-alpha
Author: One Week | One Tool
Author URI: http://oneweekonetool.org
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

		add_filter( 'custom_menu_order', array( $this, 'custom_menu_order_function' ) );

		add_filter( 'menu_order', array( $this, 'menu_order_my_function' ) );

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
	// Todo: Make the logic a bit more complex to allow for custom text within a given language
	function textdomain() {
		$locale = get_locale();

		// First look in wp-content/anthologize-files/languages, where custom language files will not be overwritten by Anthologize upgrades. Then check the packaged language file directory.
		$mofile_custom = WP_CONTENT_DIR . "/anthologize-files/languages/anthologize-$locale.mo";
		$mofile_packaged = WP_PLUGIN_DIR . "/anthologize/languages/anthologize-$locale.mo";

    	if ( file_exists( $mofile_custom ) ) {
      		load_textdomain( 'anthologize', $mofile_custom );
      		return;
      	} else if ( file_exists( $mofile_packaged ) ) {
      		load_textdomain( 'anthologize', $mofile_packaged );
      		return;
      	}
	}

	// The next two functions are a hack to make WordPress hide the menu items for Parts and Library Items
	function custom_menu_order_function(){
		return true;
	}

	function menu_order_my_function($menu_order){
		global $menu;

		foreach ( $menu as $mkey => $m ) {

			$key = array_search( 'edit.php?post_type=parts', $m );
			$keyb = array_search( 'edit.php?post_type=library_items', $m );

			if ( $key || $keyb )
				unset( $menu[$mkey] );
		}

		return $menu_order;
	}


	// Custom post types - Oh, Oh, Oh, It's Magic
	function register_post_types() {
		register_post_type( 'projects', array(
			'label' => __( 'Projects', 'anthologize' ),
			'public' => true,
			'_builtin' => false,
			'show_ui' => false,
			'capability_type' => 'page',
			'hierarchical' => false,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "project"), // Permalinks format
		));

		 $parts_labels = array(
			'name' => _x('Parts', 'post type general name'),
			'singular_name' => _x('Part', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Part'),
			'edit_item' => __('Edit Part'),
			'new_item' => __('New Part'),
			'view_item' => __('View Part'),
			'search_items' => __('Search Parts'),
			'not_found' =>  __('No parts found'),
			'not_found_in_trash' => __('No parts found in Trash'),
			'parent_item_colon' => ''
		  );

		register_post_type( 'parts', array(
			'label' => __( 'Parts', 'anthologize' ),
			'labels' => $parts_labels,
			'public' => true,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title'),
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

		 $imported_items_labels = array(
			'name' => _x('Imported Items', 'post type general name'),
			'singular_name' => _x('Imported Items', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Imported Item'),
			'edit_item' => __('Edit Imported Item'),
			'new_item' => __('New Imported Item'),
			'view_item' => __('View Imported Item'),
			'search_items' => __('Search Imported Items'),
			'not_found' =>  __('No imported items found'),
			'not_found_in_trash' => __('No imported items found in Trash'),
			'parent_item_colon' => ''
		  );

		register_post_type( 'imported_items', array(
			'label' => __('Imported Items', 'anthologize' ),
			'labels' => $imported_items_labels,
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

		require_once( dirname( __FILE__ ) . '/includes/functions.php' );

		require( dirname( __FILE__ ) . '/includes/class-ajax-handlers.php' );
		$ajax_handlers = new Anthologize_Ajax_Handlers();

	}

	// Let plugins know that we're done loading
	function loaded() {
		do_action( 'anthologize_loaded' );
	}


	function load_template() {
		if ( $_POST['export-step'] != 2 )
			return;

		$project_id = $_POST['project_id'];

		anthologize_save_project_meta();

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
			case 'rtf' :
				load_template( WP_PLUGIN_DIR . '/anthologize/templates/rtf/base.php' );
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
