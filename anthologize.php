<?php
/*
Plugin Name: Anthologize
Plugin URI: http://anthologize.org
Description: Use the power of WordPress to transform your content into a book.
Version: 0.4-alpha
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

		session_start();

		// Give me something to believe in
		add_action( 'plugins_loaded', array ( $this, 'loaded' ) );

		add_action( 'init', array ( $this, 'init' ) );

		// Load the post types
		add_action( 'anthologize_init', array ( $this, 'register_post_types' ) );

		// Load constants
		add_action( 'anthologize_init',  array ( $this, 'load_constants' ) );

		// Load the custom feed
		add_action( 'do_feed_customfeed', array ( $this, 'register_custom_feed' ) );

		// Include the necessary files
		add_action( 'anthologize_loaded', array ( $this, 'includes' ) );

		// Attach textdomain for localization
		add_action( 'anthologize_init', array ( $this, 'textdomain' ) );

		add_action( 'anthologize_init', array ( $this, 'load_template' ), 999 );

		// Register the built-in export formats
		add_action( 'anthologize_init', array( $this, 'default_export_formats' ) );

		add_filter( 'custom_menu_order', array( $this, 'custom_menu_order_function' ) );

		add_filter( 'menu_order', array( $this, 'menu_order_my_function' ) );

		// activation sequence
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// deactivation sequence
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	// Load constants
	function load_constants() {
		if ( !defined( 'ANTHOLOGIZE_VERSION' ) )
			define( 'ANTHOLOGIZE_VERSION', '0.4' );

		if ( !defined( 'ANTHOLOGIZE_TEIDOM_PATH' ) )
			define( 'ANTHOLOGIZE_TEIDOM_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php' );

		if ( !defined( 'ANTHOLOGIZE_TEIDOMAPI_PATH' ) )
			define( 'ANTHOLOGIZE_TEIDOMAPI_PATH', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-api.php' );

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

			$key = array_search( 'edit.php?post_type=anth_part', $m );
			$keyb = array_search( 'edit.php?post_type=anth_library_item', $m );

			if ( $key || $keyb )
				unset( $menu[$mkey] );
		}

		return $menu_order;
	}


	// Custom post types - Oh, Oh, Oh, It's Magic
	function register_post_types() {
		register_post_type( 'anth_project', array(
			'label' => __( 'Projects', 'anthologize' ),
			'exclude_from_search' => true,
			'publicly_queryable' => false,
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

		register_post_type( 'anth_part', array(
			'label' => __( 'Parts', 'anthologize' ),
			'labels' => $parts_labels,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title'),
			'rewrite' => array("slug" => "part"), // Permalinks format
		));

		 $library_items_labels = array(
			'name' => _x('Library Items', 'post type general name'),
			'singular_name' => _x('Library Item', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Library Item'),
			'edit_item' => __('Edit Anthologize Library Item'),
			'new_item' => __('New Anthologize Library Item'),
			'view_item' => __('View Anthologize Library Item'),
			'search_items' => __('Search Library Items'),
			'not_found' =>  __('No library items found'),
			'not_found_in_trash' => __('No library items found in Trash'),
			'parent_item_colon' => ''
		  );

		register_post_type( 'anth_library_item', array(
			'label' => __('Library Items', 'anthologize' ),
			'labels' => $library_items_labels,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'_builtin' => false,
			'show_ui' => true,
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "library_item"), // Permalinks format
		));

		 $imported_items_labels = array(
			'name' => _x('Imported Items', 'post type general name'),
			'singular_name' => _x('Imported Item', 'post type singular name'),
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

		register_post_type( 'anth_imported_item', array(
			'label' => __('Imported Items', 'anthologize' ),
			'labels' => $imported_items_labels,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'_builtin' => false,
			'show_ui' => true, // todo: hide
			'capability_type' => 'page',
			'hierarchical' => true,
			'supports' => array('title', 'editor', 'revisions'),
			'rewrite' => array("slug" => "imported_item"), // Permalinks format
		));
	}

	function default_export_formats() {

		// Defining the default options for export formats
		$d_page_size = array(
				'letter' => __( 'Letter', 'anthologize' ),
				'a4' => __( 'A4', 'anthologize' )
		);

		$d_font_size = array(
			'9' => __( '9 pt', 'anthologize' ),
			'10' => __( '10 pt', 'anthologize' ),
			'11' => __( '11 pt', 'anthologize' ),
			'12' => __( '12 pt', 'anthologize' ),
			'13' => __( '13 pt', 'anthologize' ),
			'14' => __( '14 pt', 'anthologize' )
		);

		$d_font_face = array(
			'times' => __( 'Times New Roman', 'anthologize' ),
			'helvetica' => __( 'Helvetica', 'anthologize' ),
			'courier' => __( 'Courier', 'anthologize' )
		);

		$d_font_face_pdf = array(
			'times' => __( 'Times New Roman', 'anthologize' ),
			'helvetica' => __( 'Helvetica', 'anthologize' ),
			'courier' => __( 'Courier', 'anthologize' ),
			'dejavusans' => __( 'Deja Vu Sans', 'anthologize' ),
			'arialunicid0-cj' => __( 'Chinese and Japanese', 'anthologize' ),
			'arialunicid0-ko' => __( 'Korean', 'anthologize' )
		);

		$d_font_face_epub = array(
			'Times New Roman' => __( 'Times New Roman', 'anthologize' ),
			'Helvetica' => __( 'Helvetica', 'anthologize' ),
			'Courier' => __( 'Courier', 'anthologize' )
		);
		// Register PDF + options
		anthologize_register_format( 'pdf', __( 'PDF', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/pdf/base.php' );

		anthologize_register_format_option( 'pdf', 'page-size', __( 'Page Size', 'anthologize' ), 'dropdown', $d_page_size, 'letter' );

		anthologize_register_format_option( 'pdf', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );

		anthologize_register_format_option( 'pdf', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $d_font_face_pdf, 'Times New Roman' );

		anthologize_register_format_option( 'pdf', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );


		// Register RTF + options
		anthologize_register_format( 'rtf', __( 'RTF', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/rtf/base.php' );

		anthologize_register_format_option( 'rtf', 'page-size', __( 'Page Size', 'anthologize' ), 'dropdown', $d_page_size, 'letter' );

		anthologize_register_format_option( 'rtf', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );

		anthologize_register_format_option( 'rtf', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $d_font_face, 'Times New Roman' );

		anthologize_register_format_option( 'rtf', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );


		// Register ePub.
		anthologize_register_format( 'epub', __( 'ePub', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/epub/index.php' );

		anthologize_register_format_option( 'epub', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );

		anthologize_register_format_option( 'epub', 'font-family', __( 'Font Family', 'anthologize' ), 'dropdown', $d_font_face_epub, 'Times New Roman' );

		anthologize_register_format_option( 'epub', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );



		// Register TEI. No options for this one
		anthologize_register_format( 'tei', __( 'TEI (plus HTML)', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/tei/base.php' );
	}


	function includes() {

		if ( is_admin() ) {
			require( dirname( __FILE__ ) . '/includes/class-admin-main.php' );
			require( dirname( __FILE__ ) . '/includes/class-ajax-handlers.php' );
			$ajax_handlers = new Anthologize_Ajax_Handlers();
		}

		require_once( dirname( __FILE__ ) . '/includes/class-format-api.php' );
		require_once( dirname( __FILE__ ) . '/includes/functions.php' );

	}

	// Let plugins know that we're done loading
	function loaded() {
		do_action( 'anthologize_loaded' );
	}


	function load_template() {
		global $anthologize_formats;

		$return = true;

		if ( isset( $_POST['export-step'] ) ) {
			if ( $_POST['export-step'] == 3 )
				$return = false;
		}

		if ( $return )
			return;

		anthologize_save_project_meta();

		require_once( dirname(__FILE__) . '/includes/class-export-panel.php' );
		Anthologize_Export_Panel::save_session();

		$type = $_SESSION['filetype'];

		if ( !is_array( $anthologize_formats[$type] ) )
			return;

		$project_id = $_SESSION['project_id'];

		load_template( $anthologize_formats[$type]['loader-path'] );

		return false;
	}


	function activation() {
		require_once( dirname( __FILE__ ) . '/includes/class-activation.php' );
		$activation = new Anthologize_Activation();
	}

	function deactivation() {}
}

endif; // class exists

$anthologize_loader = new Anthologize_Loader();

?>
