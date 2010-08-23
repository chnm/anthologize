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
	public function register_format( $name, $label, $loader_path, $options = false ) {
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
	
	public function deregister_format( $name ) {
		global $anthologize_formats;
		
		unset( $anthologize_formats[$name] );
	}
	
	public function register_format_option( $format_name, $option_name, $label, $type, $values, $default ) {
		global $anthologize_formats;
	
		$option = array(
			'label' => $label,
			'type' => $type,
			'values' => $values,
			'default' => $default
		);
		
		if ( $already_option = $anthologize_formats[$format_name][$option_name] ) {
			// Parse the registered options with the existing ones
			$option = wp_parse_args( $option, $already_option );
			extract( $options, EXTR_SKIP );
		}
		
		if ( $anthologize_formats[$format_name][$option_name] = $option )
			return true;
		
		return false;
	}

	public function deregister_format_option( $format_name, $option_name ) {
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

?>