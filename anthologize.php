<?php
/*
Plugin Name: Anthologize
Plugin URI: http://anthologize.org
Description: Use the power of WordPress to transform your content into a book.
Version: 0.7.8
Text Domain: anthologize
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

if ( ! defined( 'ANTHOLOGIZE_VERSION' ) )
	define( 'ANTHOLOGIZE_VERSION', '0.7.8' );

require dirname( __FILE__ ) . '/vendor/autoload.php';

if ( ! class_exists( 'Anthologize' ) ) :

class Anthologize {

	/**
	 * Bootstrap for the Anthologize singleton
	 *
	 * @since 0.7
	 * @return obj Anthologize instance
	 */
	public static function init() {
		static $instance;
		if ( empty( $instance ) ) {
			$instance = new Anthologize();
		}
		return $instance;
	}

	/**
	 * Constructor for the Anthologize class
	 *
	 * This constructor does the following:
	 * - Checks minimum PHP and WP version, and bails if they're not met
	 * - Includes Anthologize's main files
	 * - Sets up the basic hooks that initialize Anthologize's post types and UI
	 *
	 * @since 0.7
	 */
	public function __construct() {

		// Bail if PHP version is not at least 5.0
		if ( ! self::check_minimum_php() ) {
			add_action( 'admin_notices', array( 'Anthologize', 'phpversion_nag' ) );
			return;
		}

		// Bail if WP version is not at least 3.3
		if ( ! self::check_minimum_wp() ) {
			add_action( 'admin_notices', array( 'Anthologize', 'wpversion_nag' ) );
		}

		// If we've made it this far, start initializing Anthologize

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		// @todo WP's functions plugin_basename() etc don't work
		//   correctly on symlinked setups, so I'm implementing my own
		$bn = explode( DIRECTORY_SEPARATOR, dirname( __FILE__ ) );
		$this->basename     = array_pop( $bn );
		$this->plugin_dir   = plugin_dir_path( __FILE__ );
		$this->plugin_url   = plugin_dir_url( __FILE__ );
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );

		$upload_dir         = wp_upload_dir();
		$this->cache_dir    = trailingslashit( $upload_dir['basedir'] . '/anthologize-cache' );
		$this->cache_url    = trailingslashit( $upload_dir['baseurl'] . '/anthologize-cache' );

		$this->setup_constants();
		$this->includes();
		$this->setup_hooks();
		$this->register_assets();
	}

	/**
	 * Check to see whether the PHP version is at least 5.0
	 *
	 * @return bool
	 * @since 0.7
	 */
	public static function check_minimum_php() {
		return version_compare( phpversion(), '5', '>=' );
	}

	/**
	 * Check to see whether the PHP version is at least 5.0
	 *
	 * @return bool
	 * @since 0.7
	 */
	public static function check_minimum_wp() {
		return version_compare( get_bloginfo( 'version' ), '3.3', '>=' );
	}

	/**
	 * Echoes the admin notice shown when the PHP requirements are not met
	 *
	 * @since 0.7
	 */
	public static function phpversion_nag() {
		echo '<div id="message" class="error fade">';
		echo   '<p>';
		echo     sprintf( __( "<strong>Anthologize will not work with your version of PHP</strong>. You are currently running PHP v%s, and Anthologize requires version 5.0 or greater. Please contact your host if you would like to use Anthologize. ", 'anthologize' ), phpversion() );
		echo   '</p>';
		echo '</div>';
	}

	/**
	 * Echoes the admin notice shown when the minimum WP version is not met
	 *
	 * @since 0.7
	 */
	public static function wpversion_nag() {
		echo '<div id="message" class="error fade">';
		echo   '<p>';
		echo     sprintf( __( "<strong>Anthologize will not work with your version of WordPress</strong>. You are currently running WordPress v%s, and Anthologize requires version 3.3 or greater. Please upgrade WordPress if you would like to use Anthologize. ", 'anthologize' ), get_bloginfo( 'version' ) );
		echo   '</p>';
		echo '</div>';
	}

	/**
	 * Set up constants needed throughout the plugin
	 *
	 * @since 0.7
	 */
	public function setup_constants() {
		if ( ! defined( 'ANTHOLOGIZE_INSTALL_PATH' ) ) {
			define( 'ANTHOLOGIZE_INSTALL_PATH', $this->plugin_dir );
		}

		if ( ! defined( 'ANTHOLOGIZE_INCLUDES_PATH' ) ) {
			define( 'ANTHOLOGIZE_INCLUDES_PATH', $this->includes_dir );
		}

		if ( ! defined( 'ANTHOLOGIZE_TEIDOM_PATH' ) ) {
			define( 'ANTHOLOGIZE_TEIDOM_PATH', $this->includes_dir . 'class-tei-dom.php' );
		}

		if ( ! defined( 'ANTHOLOGIZE_TEIDOMAPI_PATH' ) ) {
			define( 'ANTHOLOGIZE_TEIDOMAPI_PATH', $this->includes_dir . 'class-tei-api.php' );
		}

		if ( ! defined( 'ANTHOLOGIZE_CREATORS_ALL' ) ) {
			define( 'ANTHOLOGIZE_CREATORS_ALL', 1 );
		}

		if ( ! defined( 'ANTHOLOGIZE_CREATORS_ASSERTED' ) ) {
			define( 'ANTHOLOGIZE_CREATORS_ASSERTED', 2 );
		}
	}

	/**
	 * Include required files
	 *
	 * @since 0.7
	 */
	public function includes() {

		require( $this->includes_dir . 'class-format-api.php' );
		require( $this->includes_dir . 'functions.php' );

		if ( is_admin() ) {
			require( $this->includes_dir . 'class-admin-main.php' );
			$this->admin = new Anthologize_Admin_Main();
		}
	}

	public function setup_hooks() {
		add_action( 'init',             array( $this, 'anthologize_init' ) );
		add_action( 'anthologize_init', array( $this, 'register_post_types' ) );
		add_action( 'plugins_loaded',   array( $this, 'textdomain' ) );
	}

	public static function anthologize_init() {
		do_action( 'anthologize_init' );
	}

	function activation() {
		require_once( dirname( __FILE__ ) . '/includes/class-activation.php' );
		$activation = new Anthologize_Activation();
	}

	function deactivation() {}

	/**
	 * Register our custom post types
	 *
	 * Oh, Oh, Oh, It's Magic
	 *
	 * We register four types:
	 * - anth_project is the top-level CPT (Projects)
	 * - anth_part corresponds to book chapters (Parts)
	 * - anth_library_item corresponds to individual project posts (Items)
	 * - anth_imported_item is an item pulled from an RSS feed, but not yet
	 *   incorporated into a Project/Port
	 */
	public function register_post_types() {
		register_post_type( 'anth_project', array(
			'label'               => __( 'Projects', 'anthologize' ),
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'capability_type'     => 'page',
			'hierarchical'        => false,
			'supports'            => array('title', 'editor', 'revisions'),
		) );

		register_post_type( 'anth_part', array(
			'label'               => __( 'Parts', 'anthologize' ),
			'labels'              => array(
				'add_new_item' => __( 'Add New Part', 'anthologize' ),
			),
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true, // todo: hide
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'capability_type'     => 'page',
			'hierarchical'        => true,
			'supports'            => array('title'),
		) );

		register_post_type( 'anth_library_item', array(
			'label'               => __('Library Items', 'anthologize' ),
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'capability_type'     => 'page',
			'hierarchical'        => true,
			'supports'            => array( 'title', 'editor', 'revisions', 'comments' ),
		) );

		register_post_type( 'anth_imported_item', array(
			'label'               => __('Imported Items', 'anthologize' ),
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'capability_type'     => 'page',
			'hierarchical'        => true,
			'supports'            => array( 'title', 'editor', 'revisions' ),
		) );
	}

	// Allow this plugin to be translated by specifying text domain
	// Todo: Make the logic a bit more complex to allow for custom text within a given language
	function textdomain() {
		$locale = get_locale();

		// First look in wp-content/anthologize-files/languages, where custom language files will not be overwritten by Anthologize upgrades. Then check the packaged language file directory.
		$mofile_custom = WP_CONTENT_DIR . "/anthologize-files/languages/anthologize-$locale.mo";

		if ( file_exists( $mofile_custom ) ) {
			load_textdomain( 'anthologize', $mofile_custom );
		} else {
			load_plugin_textdomain( 'anthologize', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}
	}

	/**
	 * Registers static assets with WordPress.
	 *
	 * @since 0.8.0
	 */
	public function register_assets() {
		wp_register_style( 'anthologize-admin', plugins_url() . '/anthologize/css/admin.css' );

		$foo = wp_register_script( 'blockUI-js', plugins_url() . '/anthologize/js/jquery.blockUI.js' );
		wp_register_script( 'jquery-cookie', plugins_url() . '/anthologize/js/jquery-cookie.js' );

		wp_register_script(
			'anthologize-project-organizer',
			plugins_url() . '/anthologize/js/project-organizer.js',
			array(
				'jquery-ui-sortable',
				'jquery-ui-draggable',
				'jquery-ui-datepicker',
				'blockUI-js',
				'jquery-cookie',
			)
		);

		wp_register_script( 'anthologize-sortlist-js', plugins_url() . '/anthologize/js/anthologize-sortlist.js', array( 'anthologize-project-organizer' ) );

		wp_localize_script( 'anthologize-sortlist-js', 'anth_strings', array(
			'append'           => __( 'Append', 'anthologize' ),
			'cancel'           => __( 'Cancel', 'anthologize' ),
			'commenter'        => __( 'Commenter', 'anthologize' ),
			'comment_content'  => __( 'Comment Content', 'anthologize' ),
			'comments'         => __( 'Comments', 'anthologize' ),
			'comments_explain' => __( 'Check the comments from the original post that you would like to include in your project.', 'anthologize' ),
			'done'             => __( 'Done', 'anthologize' ),
			'edit'             => __( 'Edit', 'anthologize' ),
			'less'             => __( 'less', 'anthologize' ),
			'more'             => __( 'more', 'anthologize' ),
			'no_comments'      => __( 'This post has no comments associated with it.', 'anthologize' ),
			'preview'          => __( 'Preview', 'anthologize' ),
			'posted'           => __( 'Posted', 'anthologize' ),
			'remove'           => __( 'Remove', 'anthologize' ),
			'save'             => __( 'Save', 'anthologize' ),
			'select_all'       => __( 'Select all', 'anthologize' ),
			'select_none'      => __( 'Select none', 'anthologize' ),
		) );
	}
}

endif;

/**
 * A wrapper function that allows access to the Anthologize singleton
 *
 * We also use this function to bootstrap the plugin.
 *
 * @since 0.7
 */
function anthologize() {
	return Anthologize::init();
}

$_GLOBALS['anthologize'] = anthologize();
