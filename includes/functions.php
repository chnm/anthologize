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






?>