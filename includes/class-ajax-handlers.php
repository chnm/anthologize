<?php

if ( !class_exists( 'Anthologize_Ajax_Handlers' ) ) :

    require_once('class-project-organizer.php');

class Anthologize_Ajax_Handlers {

    var $project_organizer = null;

    function anthologize_ajax_handlers() {
        add_action( 'wp_ajax_get_filterby_terms', array( $this, 'get_filterby_terms' ) );
        add_action( 'wp_ajax_get_posts_by', array( $this, 'get_posts_by' ) );
        add_action( 'wp_ajax_place_item', array( $this, 'place_item' ) );
        add_action( 'wp_ajax_place_items', array( $this, 'place_items' ) );
        add_action( 'wp_ajax_merge_items', array( $this, 'merge_items' ) );
        add_action( 'wp_ajax_get_project_meta', array( $this, 'fetch_project_meta' ) );
        add_action( 'wp_ajax_get_item_comments', array( $this, 'get_item_comments' ) );
        add_action( 'wp_ajax_include_comments', array( $this, 'include_comments' ) );
    }

    function __construct() {
        $this->anthologize_ajax_handlers();
        $project_id = ( isset( $_POST['project_id'] ) ) ? $_POST['project_id'] : 0;
                
        $this->project_organizer = new Anthologize_Project_Organizer($project_id);
       
    }

    function fetch_tags() {
        $tags = get_tags();

        $the_tags = Array();
        foreach( $tags as $tag ) {
            $the_tags[$tag->slug] = $tag->name;
        }

        print(json_encode($the_tags));
        die();
    }

    function fetch_cats() {
        $cats = get_categories();

        $the_cats = Array();
        foreach( $cats as $cat ) {
            $the_cats[$cat->term_id] = $cat->name;
        }

        print(json_encode($the_cats));
        die();
    }

	function get_filterby_terms() {
		$filtertype = $_POST['filtertype'];
		
		$terms = array();
		
		switch ( $filtertype ) {
			case 'category' :
				$cats = get_categories();
				foreach( $cats as $cat ) {
					$terms[$cat->term_id] = $cat->name;
				}
				break;
			
			case 'tag' :
				$tags = get_tags();
				foreach( $tags as $tag ) {
					$terms[$tag->slug] = $tag->name;
				}
				break;
			
			case 'post_type' :
				$terms = $this->project_organizer->available_post_types();
				break;
		}
				
		$terms = apply_filters( 'anth_get_posts_by', $terms, $filtertype );
		
		print( json_encode( $terms ) );
		die();
	}

    function get_posts_by() {
		$filterby = $_POST['filterby'];

		$args = array(
			'post_type' => array('post', 'page', 'anth_imported_item' ),
			'posts_per_page' => -1,
			'orderby' => 'post_date',
			'order' => 'DESC'
		);

		switch ( $filterby ) {
			case 'date' :
				$startdate = mysql_real_escape_string($_POST['startdate']);
				$enddate = mysql_real_escape_string($_POST['enddate']);				
								
				$date_range_where = '';
				if (strlen($startdate) > 0){
				$date_range_where = " AND post_date >= '".$startdate."'";
				}
				if (strlen($enddate) > 0){
				$date_range_where .= " AND post_date <= '".$enddate."'";
				}

				$where_func = '$where .= "'.$date_range_where.'"; return $where;'; 
				$filter_where = create_function('$where', $where_func);
				add_filter('posts_where', $filter_where);

				break;
			
			case 'tag' :
				$args['tag'] = $_POST['term'];
				break;
			
			case 'category' :
				$args['cat'] = $_POST['term'];
				break;
			
			case 'post_type' :
				$args['post_type'] = $_POST['term'];
				break;
		}

		// Allow plugins to modify the query_post arguments
		$posts = new WP_Query( apply_filters( 'anth_get_posts_by_query', $args, $filterby ) );
		
		$the_posts = Array();
		while ( $posts->have_posts() ) {
			$posts->the_post();
			$the_posts[get_the_ID()] = get_the_title();
		}
		if ($filterby == 'date'){
			remove_filter('posts_where', $filter_where);
		}
		
		$the_posts = apply_filters( 'anth_get_posts_by', $the_posts, $filterby );
		
		print(json_encode($the_posts));

		die();
	}

    /**
     * @todo Merge this with place_items. No reason for two functions
     */
    function place_item() {	
    	global $wpdb;
    	
        $project_id = $_POST['project_id'];
        $post_id = $_POST['post_id'];
        $dest_part_id = $_POST['dest_id'];
        $dest_seq = stripslashes($_POST['dest_seq']);
        $dest_seq_array = json_decode($dest_seq, $assoc=true);
        if ( NULL === $dest_seq_array ) {
            header('HTTP/1.1 500 Internal Server Error');
            die();
        }
        
        if ('true' === $_POST['new_post']) {
            $new_item = true;
            $src_part_id = false;
            $src_seq_array = false;
        } else {
            $new_item = false;
            $src_part_id = $_POST['src_id'];
            $src_seq = stripslashes($_POST['src_seq']);
            $src_seq_array = json_decode($src_seq, $assoc=true);
            if ( NULL === $src_seq_array ) {
                header('HTTP/1.1 500 Internal Server Error');
                die();
            }
        }

        $insert_result_id = $this->project_organizer->insert_item($project_id, $post_id, $new_item, $dest_part_id, $src_part_id, $dest_seq_array, $src_seq_array);

        if (false === $insert_result_id) {
		header('HTTP/1.1 500 Internal Server Error');
		die();
        } else {
		if (true == $new_item){
      			$dest_seq_array[$insert_result] = $dest_seq_array['new_new_new'];
      			unset($dest_seq_array['new_new_new']);
		}
		
		$this->project_organizer->rearrange_items($dest_seq_array);
				
		// Get the comment count for the original item
		$comment_count = $wpdb->get_var( $wpdb->prepare( "SELECT comment_count FROM $wpdb->posts WHERE ID = %s", $post_id ) );
		
		// Assemble the array to return
		$insert_result = array(
			array(
				'post_id'	=> $insert_result_id,
				'comment_count'	=> $comment_count
			),
		);
		
		echo json_encode( $insert_result );
        }

        die();
    }

	function place_items() {
		global $wpdb;
		
		$project_id = $_POST['project_id'];

		$post_ids = $_POST['post_ids'];
		$post_ids = stripslashes($_POST['post_ids']);
		$post_ids_array = json_decode($post_ids, $assoc=true);

		$dest_part_id = $_POST['dest_id'];
		$dest_seq = stripslashes($_POST['dest_seq']);
		$dest_seq_array = json_decode($dest_seq, $assoc=true);
		if ( NULL === $dest_seq_array ) {
			header('HTTP/1.1 500 Internal Server Error');
			die();
		}

		$new_item = true;
		$src_part_id = false;
		$src_seq_array = false;

		$ret_ids = array();
		foreach ($post_ids_array as $position => $post_id){
			$post_id = str_replace("added-", "", $post_id);
			$insert_result = $this->project_organizer->insert_item( $project_id, $post_id, $new_item, $dest_part_id, $src_part_id, $dest_seq_array, $src_seq_array );
			if (false === $insert_result) {
				header('HTTP/1.1 500 Internal Server Error');
				die();
			}else{
				$dest_seq_array[$insert_result] = $dest_seq_array[$post_id];
				unset($dest_seq_array[$post_id]);
				
				// Get the comment count for the original item
				$comment_count = $wpdb->get_var( $wpdb->prepare( "SELECT comment_count FROM $wpdb->posts WHERE ID = %s", $post_id ) );
				
				// Assemble the array to return
				$ret_ids[] = array(
					'post_id'	=> $insert_result,
					'comment_count'	=> $comment_count			
				);
			}
		}
		$this->project_organizer->rearrange_items($dest_seq_array);
		
		print json_encode( $ret_ids );
		die();
	}

    function merge_items() {
        $project_id = $_POST['project_id'];
        $post_id = $_POST['post_id'];

        if (is_array($_POST['child_post_ids'])) {
            $child_post_ids = $_POST['child_post_ids'];
        } else {
            $child_post_ids = Array($_POST['child_post_ids']);
        }

        $new_seq = stripslashes($_POST['new_seq']);

        $new_seq_array = json_decode($new_seq, $assoc=true);
        if ( NULL === $new_seq_array ) {
            header('HTTP/1.1 500 Internal Server Error');
            die();
        }

        $append_result = $this->project_organizer->append_children($post_id, $child_post_ids);

        if (false === $append_result) {
            header('HTTP/1.1 500 Internal Server Error');
            die();
        }

        $reseq_result = $this->project_organizer->rearrange_items($new_seq_array);

        // TODO: What to do? If the merge succeeded but the resort failed, ugh...
        /*if (false === $reseq_result) {
        }*/

        die();
    }

    function fetch_project_meta() {
		$result = '';
		$project_id = $_POST['proj_id'];

		if ( $options = get_post_meta( $project_id, 'anthologize_meta', true ) )
			$result = json_encode( $options );
		else
			$result = json_encode( 'none' );

    	print(json_encode( $result ));

    	die();

    }
    	
    	/**
    	 * The handler for the get_item_comments ajax action
    	 *
    	 * Returns the comments associated with the provided post_id. Called when the 'Comments'
    	 * link near an item on the project organizer screen is clicked.
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 */
	function get_item_comments() {
		$item_id = !empty( $_POST['post_id'] ) ? $_POST['post_id'] : false;
		
		// The item_id tends to be a CSS selector. We have to break it up.
		if ( !is_int( $item_id ) ) {
			$i 	 = explode( '-', $item_id );
			$item_id = $i[1];
		}
		
		if ( !$item_id )
			return false;
		
		// Get the original post id
		$anth_meta 		= get_post_meta( $item_id, 'anthologize_meta', true );
		$original_post_id	= isset( $anth_meta['original_post_id'] ) ? $anth_meta['original_post_id'] : false;
		
		if ( !$original_post_id )
			return false;
		
		$comments = get_comments( array( 'post_id' => $original_post_id ) );
		
		// Mark certain comments as already included, so their checkboxes get checked
		foreach( $comments as $comment ) {
			if ( !empty( $anth_meta['included_comments'] ) && in_array( $comment->comment_ID, $anth_meta['included_comments'] ) ) {
				$comment->is_included = 1;
			} else {
				$comment->is_included = 0;
			}
		}
		
		if ( empty( $comments ) ) {
			$comment = array(
				'empty' => '1',
				'text'	=> __( 'This post has no comments.', 'anthologize' )
			);
		}
		
		echo( json_encode( $comments ) );
		die();
	}
	
	/**
    	 * The handler for the include_comments ajax action
    	 *
    	 * Called when the Save button is clicked on the Comments slider of the project organizer
    	 * screen. Saves the submitted comments to the anthologize_meta postmeta.
    	 *
    	 * @package Anthologize
    	 * @since 0.6
    	 */
	function include_comments() {
		if ( !empty ( $_POST['comment_id'] ) )
			$comment_id = $_POST['comment_id'];
		
		if ( !empty( $_POST['post_id'] ) )
			$post_id = $_POST['post_id'];
		
		$action = !empty( $_POST['check_action'] ) && 'add' == $_POST['check_action'] ? 'add' : 'remove';
		
		if ( empty( $post_id ) || empty( $comment_id ) )
			die(); // better error reporting?
		
		// Get the included comments for this item
		if ( !$item_meta = get_post_meta( $post_id, 'anthologize_meta', true ) ) {
			$item_meta = array();
		}
		
		// Just in case the included_comments array doesn't exist
		if ( empty( $item_meta['included_comments'] ) || !is_array( $item_meta['included_comments'] ) ) {
			$item_meta['included_comments'] = array();
		}
		
		// Our next action depends on $action
		switch ( $action ) {
			case 'add' :
				// Get the comment from the original post
				if ( !$comment = get_comment( $comment_id, ARRAY_A ) )
					return false;

				// We can pretty much reuse all the comment data, though we'll
				// need to remove the ID so that we create a new comment and
				// set it to a different post
				unset( $comment['ID'] );
				$comment['comment_post_ID'] = $post_id;
				
				// Insert the new comment
				if ( !$new_comment_id = wp_insert_comment( $comment ) )
					return false;
				
				// Add the original comment id to the index of included comments
				// included_comments is structured as 
				// [comment_copy_id] => original_comment_id 
				if ( !in_array( $comment_id, $item_meta['included_comments'] ) ) {
					$item_meta['included_comments'][$new_comment_id] = $comment_id;
				}	
				break;
			
			case 'remove' :
			default :
				// Just to be safe, we remove all instances of comments on the
				// library item that correspond to the original comment in
				// question
				$comments_to_remove = array_keys( $item_meta['included_comments'], $comment_id );
				
				foreach( (array)$comments_to_remove as $ctr ) {
					// We'll trash the comment instead of deleting it
					wp_set_comment_status( $ctr, 'trash' );
					unset( $item_meta['included_comments'][$ctr] );
				}
				
				break;
		}
		
		// Resave
		update_post_meta( $post_id, 'anthologize_meta', $item_meta );
		
		// Return the comment array to show that we were successful
		echo json_encode( array_values( $item_meta['included_comments'] ) );
		die();
	}
}

endif;

?>
