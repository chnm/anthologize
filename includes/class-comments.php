<?php

if ( !class_exists( 'Anthologize_Comments' ) ) :

class Anthologize_Comments {
	var $item_id;
	var $original_item_id;
	
	var $item_meta;	
	var $included_comments;
	
	/**
    	 * Constructor
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 */
	function __construct( $item_id = false ) {
		if ( $item_id ) {
			$this->item_id = $item_id;
		
			// Get the metadata for this item
			if ( !$this->item_meta = get_post_meta( $this->item_id, 'anthologize_meta', true ) ) {
				$this->item_meta = array();
			}
			
			$this->original_item_id	= isset( $this->item_meta['original_post_id'] ) ? $this->item_meta['original_post_id'] : false;
		
			// Just in case the included_comments array doesn't exist
			if ( empty( $this->item_meta['included_comments'] ) || !is_array( $this->item_meta['included_comments'] ) ) {
				$this->item_meta['included_comments'] = array();
			}
			
			$this->included_comments = $this->item_meta['included_comments']; 
		}
	}
	
	/**
    	 * Import a single comment from the original post to the anth_library_item
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 *
    	 * @param int $comment_id The ID of the *original* comment that you want to copy over
    	 */
	public function import_comment( $comment_id ) {
		// Get the comment from the original post
		if ( !$comment = get_comment( $comment_id, ARRAY_A ) )
			return false;

		// We can pretty much reuse all the comment data, though we'll need to remove the ID
		// so that we create a new comment and set it to a different post
		unset( $comment['ID'] );
		$comment['comment_post_ID'] = $this->item_id;
		
		// Insert the new comment
		if ( !$new_comment_id = wp_insert_comment( $comment ) )
			return false;
		
		// Add the original comment id to the index of included comments. included_comments
		// is structured as [comment_copy_id] => original_comment_id 
		if ( !in_array( $comment_id, $this->included_comments ) ) {
			$this->included_comments[$new_comment_id] = $comment_id;
		}	
	}
	
	/**
    	 * Remove a single comment from the anth_library_item
    	 *
    	 * For technical reasons having to do with the markup created in the project organizer JS,
    	 * this function takes the *original comment ID* as a parameter, NOT the ID of the copied
    	 * comment on the anth_library_item. This function then deletes ALL instances of comments
    	 * on the anth_library_item that correspond to the original comment.
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 *
    	 * @uses apply_filters() Filter anthologize_remove_comment_status to change the default
    	 *   behavior from trashing to true deletion
    	 * @param int $comment_id The ID of the *original* comment corresponding to the comments
    	 *   you want to delete
    	 */
	public function remove_comment( $comment_id ) {
		// Just to be safe, we remove all instances of comments on the library item that
		// correspond to the original comment in question
		$comments_to_remove = array_keys( $this->included_comments, $comment_id );
	
		foreach( (array)$comments_to_remove as $ctr ) {
			// We'll trash the comment instead of deleting it
			wp_set_comment_status( $ctr, apply_filters( 'anthologize_remove_comment_status', 'trash' ) );
			unset( $this->included_comments[$ctr] );
		}
	}
	
	/**
    	 * Import all comments from the original post to the anth_library_item
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 */
	public function import_all_comments() {
		$original_comments = get_comments( array( 'post_id' => $this->original_item_id ) );
		
		foreach( $original_comments as $oc ) {
			// Don't overwrite existing comments
			if ( !in_array( $oc->comment_ID, $this->included_comments ) ) {
				$this->import_comment( $oc->comment_ID );
			}
		}
	}
	
	/**
    	 * Remove all comments from the anth_library_item
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 */
	public function remove_all_comments() {
		foreach( (array)$this->included_comments as $comment_id => $original_comment_id ) {
			$this->remove_comment( $original_comment_id );	
		}
	}
	
	/**
    	 * Utility function to reset the postmeta corresponding to the included comment data
    	 *
    	 * It's a good idea to run this function manually after running any of the other methods.
    	 * I don't do it automatically because it creates a lot of overhead for bulk add/deletes.
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 */
	public function update_included_comments() {
		$this->item_meta['included_comments'] = $this->included_comments;

		update_post_meta( $this->item_id, 'anthologize_meta', $this->item_meta );
	}
}

endif;

?>