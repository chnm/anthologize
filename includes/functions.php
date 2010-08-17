<?php

// This plugin file contains miscellaneous Anthologize functions that are needed in the global scope. Todo: Clean up.

function anthologize_save_project_meta() {
	$project_id = $_POST['project_id'];
	$project_meta = get_post_meta( $project_id, 'anthologize_meta', true );

	foreach( $_POST as $key => $value ) {
	
		if ( $key == 'project_id' || $key == 'submit' )
			continue;

		$project_meta[$key] = $value;
	}

	update_post_meta( $_POST['project_id'], 'anthologize_meta', $project_meta );
}

function anthologize_get_project_parts($projectId) {

    $projectParts =  new WP_Query(array('post_parent'=>$projectId, 'post_type'=>'anth_part'));

    return $projectParts->posts;

}

function anthologize_get_part_items($partId) {
    $partItems = new WP_Query(array('post_parent'=>$partId, 'post_type'=>'anth_library_item'));

    return $partItems->posts;

}

function anthologize_display_project_content($projectId) {
    $parts = anthologize_get_project_parts($projectId);

    foreach ( $parts as $part ) {
        echo '<h2>' . $part->post_title . '</h2>'."\n";
        echo '<div class="anthologize-part-content">'."\n";
        echo $item->post_content . "\n";
        echo '</div>' . "\n";

        $items = anthologize_get_part_items($part->ID);
        foreach ( $items as $item ) {
            echo '<h3>'.$item->post_title . '</h3>'."\n";
            echo '<div class="anthologize-item-content">';
            echo $item->post_content;
            echo '</div>';
        }
    }
}

function anthologize_filter_post_content($content) {
    global $post;
    if ($post->post_type == 'anth_project') {
        $content .=  anthologize_display_project_content(get_the_ID());
    }
    return $content;
}

add_filter('the_content', 'anthologize_filter_post_content');


function anthologize_register_format( $name, $label, $loader_path, $options = false ) {
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
		)
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
		)
	);
	
	$d_font_face = array(
		'label' => __( 'Font Face', 'anthologize' ),
		'values' => array(
			'times' => __( 'Times New Roman', 'anthologize' ),
			'helvetica' => __( 'Helvetica', 'anthologize' ),
			'courier' => __( 'Courier', 'anthologize' )
		)
	);
	
	$default_options = array(
		'page_size' => $d_page_size,
		'font_size' => $d_font_size,
		'font_face' => $d_font_face
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
	
	// Register the format
	$anthologize_formats[$name] = $new_format;
	//print_r($new_format);
}

function test_formats() {
	global $anthologize_formats;
	print_r($anthologize_formats);
}
//add_action( 'anthologize_init', 'test_formats', 999 );



?>