<?php



if ( !class_exists( 'Anthologize_Format_API' ) ) :

class Anthologize_Format_API {

	function __construct() {

	}

	/**
	 * register_format()
	 *
	 * Use this function to register an export format translator in
	 * Anthologize
	 *
	 * @author Boone Gorges
	 * @param $name string The name used internally by Anthologize for this format (eg 'pdf')
	 * @param $label string The format name as displayed to the user. Can be localizable.
	 * @param $loader_path string Path to the translator loader file, which will be included with WordPress's load_template()
	 * @return type bool Returns true on successful registration
	 */
	public static function register_format( $name, $label, $loader_path, $options = false ) {
		global $anthologize_formats;

		if ( !is_array( $anthologize_formats ) )
			$anthologize_formats = array();

		$counter = 1;
		$new_name = $name;
		while ( isset( $anthologize_formats[$new_name] ) ) {
			$new_name = $name . '-' . $counter;
		}
		$name = $new_name;

		$new_format = array(
			'label' => $label,
			'loader-path' => $loader_path
		);

		// Register the format
		if ( $anthologize_formats[$name] = $new_format )
			return true;

		return false;
	}

	public static function deregister_format( $name ) {
		global $anthologize_formats;

		unset( $anthologize_formats[$name] );
	}

	public static function register_format_option( $format_name, $option_name, $label, $type, $values, $default ) {
		global $anthologize_formats;

		$option = array(
			'label' => $label,
			'type' => $type,
			'values' => $values,
			'default' => $default
		);

		if ( !empty( $anthologize_formats[$format_name][$option_name] ) && $already_option = $anthologize_formats[$format_name][$option_name] ) {
			// Parse the registered options with the existing ones
			$option = wp_parse_args( $option, $already_option );
			extract( $options, EXTR_SKIP );
		}

		if ( $anthologize_formats[$format_name][$option_name] = $option )
			return true;

		return false;
	}

	public static function deregister_format_option( $format_name, $option_name ) {
		global $anthologize_formats;

		unset( $anthologize_formats[$format_name][$option_name] );
	}

}

endif;


function anthologize_register_format( $name, $label, $loader_path, $options = false ) {
	if ( !isset( $name ) || !isset( $label ) || !isset( $loader_path ) )
		return false;

	if ( !file_exists( $loader_path ) )
		return false;

	Anthologize_Format_API::register_format( $name, $label, $loader_path );
}

function anthologize_deregister_format( $name ) {
	global $anthologize_formats;

	if ( !isset( $name ) )
		return false;

	if ( !isset( $anthologize_formats[$name] ) )
		return false;

	Anthologize_Format_API::deregister_format( $name );
}

function anthologize_register_format_option( $format_name, $option_name, $label, $type = false, $values = false, $default = false ) {
	global $anthologize_formats;

	if ( !isset( $format_name ) || !isset( $option_name ) || !isset( $label ) )
		return false;

	if ( !is_array( $anthologize_formats ) )
		return false; // Todo: add something to WP_Error to account for these cases

	if ( !isset( $anthologize_formats[$format_name] ) )
		return false; // Todo: Ditto

	// If a type is not provided, it'll be a plain textbox
	if ( !isset( $type ) )
		$type = 'textbox';

	// $values will be an empty array if there are no preselected values to choose from
	if ( !isset( $values ) || $type == 'textbox' )
		$values = array();

	// When an option is registered as a dropdown, it must have some options to choose from. Otherwise default to textbox
	if ( empty( $values ) && $type == 'dropdown' )
		$type = 'textbox';

	if ( !isset( $default ) )
		$default = false;

	Anthologize_Format_API::register_format_option( $format_name, $option_name, $label, $type, $values, $default );
}

function anthologize_deregister_format_option( $format_name, $option_name ) {
	global $anthologize_formats;

	if ( !isset( $format_name ) || !isset( $option_name ) )
		return false;

	if ( !isset( $anthologize_formats[$format_name] ) )
		return false;

	if ( !isset( $anthologize_formats[$format_name][$option_name] ) )
		return false;

	Anthologize_Format_API::deregister_format_option( $format_name, $option_name );
}

/**
 * Registers Anthologize's packaged export formats
 *
 * @since 0.7
 */
function anthologize_register_default_formats() {
	// Defining the default options for export formats
	$d_page_size = array(
		'letter' => __( 'Letter', 'anthologize' ),
		'a4'     => __( 'A4', 'anthologize' )
	);

	$d_font_size = array(
		'9'  => __( '9 pt', 'anthologize' ),
		'10' => __( '10 pt', 'anthologize' ),
		'11' => __( '11 pt', 'anthologize' ),
		'12' => __( '12 pt', 'anthologize' ),
		'13' => __( '13 pt', 'anthologize' ),
		'14' => __( '14 pt', 'anthologize' )
	);

	$d_font_face = array(
		'times'     => __( 'Times New Roman', 'anthologize' ),
		'helvetica' => __( 'Helvetica', 'anthologize' ),
		'courier'   => __( 'Courier', 'anthologize' )
	);

	$d_font_face_pdf = array(
		'times'           => __( 'Times New Roman', 'anthologize' ),
		'helvetica'       => __( 'Helvetica', 'anthologize' ),
		'courier'         => __( 'Courier', 'anthologize' ),
		'dejavusans'      => __( 'Deja Vu Sans', 'anthologize' ),
		'arialunicid0-cj' => __( 'Chinese and Japanese', 'anthologize' ),
		'arialunicid0-ko' => __( 'Korean', 'anthologize' )
	);

	$d_font_face_epub = array(
		'Times New Roman' => __( 'Times New Roman', 'anthologize' ),
		'Helvetica'       => __( 'Helvetica', 'anthologize' ),
		'Courier'         => __( 'Courier', 'anthologize' )
	);

	// Register PDF + options
	anthologize_register_format( 'pdf', __( 'PDF', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/pdf/base.php' );

	anthologize_register_format_option( 'pdf', 'page-size', __( 'Page Size', 'anthologize' ), 'dropdown', $d_page_size, 'letter' );

	anthologize_register_format_option( 'pdf', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );

	anthologize_register_format_option( 'pdf', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $d_font_face_pdf, 'Times New Roman' );

	anthologize_register_format_option( 'pdf', 'break-parts', __( 'Page break before parts?', 'anthologize' ), 'checkbox' );

	anthologize_register_format_option( 'pdf', 'break-items', __( 'Page break before items?', 'anthologize' ), 'checkbox' );

	anthologize_register_format_option( 'pdf', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

	// Register RTF + options
	anthologize_register_format( 'rtf', __( 'RTF', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/rtf/base.php' );
	anthologize_register_format_option( 'rtf', 'page-size', __( 'Page Size', 'anthologize' ), 'dropdown', $d_page_size, 'letter' );
	anthologize_register_format_option( 'rtf', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );
	anthologize_register_format_option( 'rtf', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $d_font_face_pdf, 'Times New Roman' );
	anthologize_register_format_option( 'rtf', 'break-parts', __( 'Page break before parts?', 'anthologize' ), 'checkbox' );
	anthologize_register_format_option( 'rtf', 'break-items', __( 'Page break before items?', 'anthologize' ), 'checkbox' );
	anthologize_register_format_option( 'rtf', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

	// Register ePub.
	anthologize_register_format( 'epub', __( 'ePub', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/epub/index.php' );

	anthologize_register_format_option( 'epub', 'font-size', __( 'Base Font Fize', 'anthologize' ), 'dropdown', $d_font_size, '12' );

	anthologize_register_format_option( 'epub', 'font-family', __( 'Font Family', 'anthologize' ), 'dropdown', $d_font_face_epub, 'Times New Roman' );

	anthologize_register_format_option( 'epub', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

	//build the covers list for selection
	$coversArray = array();
	$coversArray['none'] = 'None';
	//scan the covers directory and return the array
	$filesArray = scandir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' .
		 DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'epub' . DIRECTORY_SEPARATOR . 'covers');
	foreach($filesArray as $file) {
		if(! is_dir($file)) {
			$coversArray[$file] = $file;
		}
	}

	anthologize_register_format_option( 'epub', 'cover', __( 'Cover Image', 'anthologize' ), 'dropdown', $coversArray);

	//epub colophon commented out until we get the XSLTs working for it
	//anthologize_register_format_option( 'epub', 'colophon', __( 'Include Anthologize colophon page?', 'anthologize' ), 'checkbox' );

	// Register HTML

	anthologize_register_format( 'html', __( 'HTML', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/html/output.php' );

	$htmlFontSizes = array('48pt'=>'48 pt', '36pt'=>'36 pt', '18pt'=>'18 pt', '14'=>'14 pt', '12'=>'12 pt');

	anthologize_register_format_option( 'html', 'font-size', __( 'Font Size', 'anthologize' ), 'dropdown', $htmlFontSizes, '14pt' );

	anthologize_register_format_option( 'html', 'download', __('Download HTML?', 'anthologize'), 'checkbox', array('Download'=>'download'), 'download');

	// Register TEI. No options for this one
	anthologize_register_format( 'tei', __( 'Anthologize TEI', 'anthologize' ), WP_PLUGIN_DIR . '/anthologize/templates/tei/base.php' );
}
add_action( 'anthologize_init', 'anthologize_register_default_formats' );
