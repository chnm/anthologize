<?php

/**
 * Anthologize TCPDF configuration
 *
 * For better performance in WordPress environments, Anthologize loads a custom
 * configuration for TCPDF. The important changed config items are flagged with
 * inline documentation. All other documentation has been removed. For complete
 * docs, see tcpdf/config/tcpdf-config.php
 *
 * Put the following in your wp-config.php file if you want to provide your own
 * configuration for Anthologize's TCPDF (advanced users only!)
 *
 *     define( 'ANTHOLOGIZE_TCPDF_EXTERNAL_CONFIG', true );
 *
 * or use add_filter() to return the value you want.
 *
 * @package Anthologize
 * @since 0.6.3
 */

/**
 * Tell TCPDF that we won't be needing their config, thankyouverymuch
 */
define( 'K_TCPDF_EXTERNAL_CONFIG', true );

if ( !defined( 'ANTHOLOGIZE_TCPDF_EXTERNAL_CONFIG' ) ) {
        define( 'ANTHOLOGIZE_TCPDF_EXTERNAL_CONFIG', false );
}

if ( !apply_filters( 'anthologize_tcpdf_external_config', ANTHOLOGIZE_TCPDF_EXTERNAL_CONFIG ) ) {

        /**
         * Out of the box, TCPDF does a bunch of logic to auto-determine the
         * install path and URL. Since we're always running from inside
         * Anthologize, we can provide these values manually
         */
        define( 'K_PATH_MAIN', ANTHOLOGIZE_INSTALL_PATH . 'templates/pdf/tcpdf/' );
        define( 'K_PATH_URL', WP_PLUGIN_URL . '/anthologize/templates/pdf/tcpdf' );

	/**
	 * cache directory for temporary files (full path)
         *
         * Many WordPress setups are set up so that the Apache use does not
         * have permission to write to the Anthologize plugin directory and its
         * subdirectories. However, all WP installations should be able to
         * write to the WP upload directory. So we'll put our TCPDF cache there
	 */
        $upload_dir = wp_upload_dir();

        // File system path
	define ('K_PATH_CACHE', $upload_dir['basedir'] . '/anthologize-cache');

        // URL path
	define ('K_PATH_URL_CACHE', $upload_dir['baseurl'] . '/anthologize-cache');

        // The rest of these values are the same as TCPDF's
	define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');
	define ('K_PATH_IMAGES', K_PATH_MAIN.'images/');
	define ('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');
	define ('PDF_PAGE_FORMAT', 'A4');
	define ('PDF_PAGE_ORIENTATION', 'P');
	define ('PDF_CREATOR', 'TCPDF');
	define ('PDF_AUTHOR', 'TCPDF');
	define ('PDF_HEADER_TITLE', 'TCPDF Example');
	define ('PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");
	define ('PDF_HEADER_LOGO', 'tcpdf_logo.jpg');
	define ('PDF_HEADER_LOGO_WIDTH', 30);
	define ('PDF_UNIT', 'mm');
	define ('PDF_MARGIN_HEADER', 5);
	define ('PDF_MARGIN_FOOTER', 10);
	define ('PDF_MARGIN_TOP', 27);
	define ('PDF_MARGIN_BOTTOM', 25);
	define ('PDF_MARGIN_LEFT', 15);
	define ('PDF_MARGIN_RIGHT', 15);
	define ('PDF_FONT_NAME_MAIN', 'helvetica');
	define ('PDF_FONT_SIZE_MAIN', 10);
	define ('PDF_FONT_NAME_DATA', 'helvetica');
	define ('PDF_FONT_SIZE_DATA', 8);
	define ('PDF_FONT_MONOSPACED', 'courier');
	define ('PDF_IMAGE_SCALE_RATIO', 1.25);
	define('HEAD_MAGNIFICATION', 1.1);
	define('K_CELL_HEIGHT_RATIO', 1.25);
	define('K_TITLE_MAGNIFICATION', 1.3);
	define('K_SMALL_RATIO', 2/3);
	define('K_THAI_TOPCHARS', true);
	define('K_TCPDF_CALLS_IN_HTML', true);
}
?>
