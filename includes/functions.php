<?php

// This plugin file contains miscellaneous Anthologize functions that are needed in the global scope. Todo: Clean up.

function anthologize_save_project_meta() {
	if ( !empty( $_POST['project_id'] ) )
		$project_id = $_POST['project_id'];
	else if ( !empty( $_GET['project_id'] ) )
		$project_id = $_GET['project_id'];
	else
		return;

	$project_meta = get_post_meta( $project_id, 'anthologize_meta', true );
	if ( ! is_array( $project_meta ) ) {
		$project_meta = array();
	}

	foreach( $_POST as $key => $value ) {

		if ( $key == 'project_id' || $key == 'submit' )
			continue;

		$project_meta[$key] = $value;
	}

	update_post_meta( $project_id, 'anthologize_meta', $project_meta );
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
        echo '<h2>' . esc_html( $part->post_title ) . '</h2>'."\n";
        echo '<div class="anthologize-part-content">'."\n";
        echo $item->post_content . "\n";
        echo '</div>' . "\n";

        $items = anthologize_get_part_items($part->ID);
        foreach ( $items as $item ) {
            echo '<h3>' . esc_html( $item->post_title ) . '</h3>'."\n";
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

//add_filter('the_content', 'anthologize_filter_post_content');

/**
 * Get data about an export "session".
 *
 * @since 0.7.8
 */
function anthologize_get_session() {
	$session = get_user_meta( get_current_user_id(), 'anthologize_export_session', true );
	if ( ! $session ) {
		$session = array();
	}

	return $session;
}

/**
 * Delete current user's active export session.
 *
 * @since 0.7.8
 */
function anthologize_delete_session() {
	delete_user_meta( get_current_user_id(), 'anthologize_export_session' );
}

/**
 * Save data to the current export "session".
 *
 * @since 0.7.8
 *
 * @param array $data
 */
function anthologize_save_session( $data ) {
	$keys = anthologize_get_session_data_keys();

	$session = anthologize_get_session();

	foreach ( $keys as $key ) {
		if ( isset( $data[ $key ] ) ) {
			$session[ $key ] = $data[ $key ];
		}
	}

	update_user_meta( get_current_user_id(), 'anthologize_export_session', $session );
}

/**
 * Get a list of keys that are whitelisted for sessions.
 *
 * @return array
 */
function anthologize_get_session_data_keys() {
	$keys = array(
		// Step 1
		'project_id',
		'cyear',
		'cname',
		'ctype',
		'cctype',
		'edition',
		'authors',

		// Step 2
		'post-title',
		'dedication',
		'acknowledgements',
		'filetype',

		// Step 3
		'page-size',
		'font-size',
		'font-face',
		'break-parts',
		'break-items',
		'colophon',
		'do-shortcodes',
		'metadata',

		'creatorOutputSettings',
		'outputParams',
	);

	/**
	 * Filters the keys that can be saved as part of an export session.
	 *
	 * @since 0.7.8
	 */
	return apply_filters( 'anthologize_get_session_data_keys', $keys );
}

/**
 * Get session "outputParams" needed by export formats.
 *
 * @return array
 */
function anthologize_get_session_output_params() {
	$session = anthologize_get_session();

	$keys = array(
		'page-size',
		'font-size',
		'font-face',
		'break-parts',
		'break-items',
		'colophon',
		'do-shortcodes',
		'creatorOutputSettings',
		'download',
		'gravatar-default',
		'metadata',
	);

	$params = array();
	foreach ( $keys as $key ) {
		$value = isset( $session[ $key ] ) ? $session[ $key ] : '';
		$params[ $key ] = $value;
	}

	return $params;
}
