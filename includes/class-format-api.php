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
   * @param $options array Array of options (page size, font, etc) supported by the export format. Omit to accept the defaults.
   * @return type bool Returns true on successful registration
   */
	function register_format( $name, $label, $loader_path, $options = false ) {
		global $anthologize_formats;
		
		if ( !is_array( $anthologize_formats ) )
			$anthologize_formats = array();
		
		if ( !isset( $name ) || !isset( $label ) || !isset( $loader_path ) )
			return false;
		
		if ( !file_exists( $loader_path ) )
			return false;
		
		$counter = 1;
		$new_name = $name;
		while ( isset( $anthologize_formats[$new_name] ) ) {
			$new_name = $name . '-' . $counter;
		}
		$name = $new_name;
		
		// Defining the default options for export formats
		$d_page_size = array(
			'label' => __( 'Page Size', 'anthologize' ),
			'values' => array(
				'letter' => __( 'Letter', 'anthologize' ),
				'a4' => __( 'A4', 'anthologize' )
			),
			'default' => 'letter'
		);
		
		$d_font_size = array(
			'label' => __( 'Base Font Size', 'anthologize' ),
			'values' => array(
				'9' => __( '9 pt', 'anthologize' ),
				'10' => __( '10 pt', 'anthologize' ),
				'11' => __( '11 pt', 'anthologize' ),
				'12' => __( '12 pt', 'anthologize' ),
				'13' => __( '13 pt', 'anthologize' ),
				'14' => __( '14 pt', 'anthologize' ),
			),
			'default' => '12'
		);
		
		$d_font_face = array(
			'label' => __( 'Font Face', 'anthologize' ),
			'values' => array(
				'times' => __( 'Times New Roman', 'anthologize' ),
				'helvetica' => __( 'Helvetica', 'anthologize' ),
				'courier' => __( 'Courier', 'anthologize' )
			),
			'default' => 'times'
		);
		
		$default_options = array(
			'page-size' => $d_page_size,
			'font-size' => $d_font_size,
			'font-face' => $d_font_face
		);
		
		// Parse the registered options with the defaults
		$options = wp_parse_args( $options, $default_options );
		extract( $options, EXTR_SKIP );
		
		$new_format = array(
			'label' => $label,
			'page-size' => $page_size,
			'font-size' => $font_size,
			'font-face' => $font_face,
			'loader-path' => $loader_path
		);
		
		// Add miscellaneous options
		foreach( $options as $key => $value ) {
			if ( !isset( $new_format[$key] ) )
				$new_format[$key] = $value;
		}
			
		// Register the format
		if ( $anthologize_formats[$name] = $new_format )
			return true;
		
		return false;
	}
	
	

}

endif;


function anthologize_register_format( $name, $label, $loader_path, $options = false ) {
	$anthologize_format_api = new Anthologize_Format_API();
	$anthologize_format_api->register_format( $name, $label, $loader_path, $options );
}

?>