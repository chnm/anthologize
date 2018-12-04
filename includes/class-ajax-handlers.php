<?php

if ( !class_exists( 'Anthologize_Ajax_Handlers' ) ) :

    require_once('class-project-organizer.php');

class Anthologize_Ajax_Handlers {

    var $project_organizer = null;

    function __construct() {
        $project_id = ( isset( $_POST['project_id'] ) ) ? $_POST['project_id'] : 0;

        $this->project_organizer = new Anthologize_Project_Organizer($project_id);

        add_action( 'wp_ajax_get_filterby_terms', array( $this, 'get_filterby_terms' ) );
        add_action( 'wp_ajax_get_posts_by', array( $this, 'get_posts_by' ) );
        add_action( 'wp_ajax_place_item', array( $this, 'place_item' ) );
        add_action( 'wp_ajax_place_items', array( $this, 'place_items' ) );
        add_action( 'wp_ajax_merge_items', array( $this, 'merge_items' ) );
        add_action( 'wp_ajax_get_project_meta', array( $this, 'fetch_project_meta' ) );
        add_action( 'wp_ajax_get_item_comments', array( $this, 'get_item_comments' ) );
        add_action( 'wp_ajax_include_comments', array( $this, 'include_comments' ) );
        add_action( 'wp_ajax_include_all_comments', array( $this, 'include_all_comments' ) );
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
			'post_type' => array_keys($this->project_organizer->available_post_types()),
			'posts_per_page' => -1,
			'orderby' => 'post_date',
			'order' => 'DESC',
			'post_status' => $this->project_organizer->source_item_post_statuses(),
		);

		switch ( $filterby ) {
			case 'date' :
				$date_query = array();

				if ( isset( $_POST['startdate'] ) ) {
					$date_query[] = array(
						'after' => wp_unslash( $_POST['startdate'] ),
					);
				}

				if ( isset( $_POST['enddate'] ) ) {
					$date_query[] = array(
						'before' => wp_unslash( $_POST['enddate'] ),
					);
				}

				if ( $date_query ) {
					$args['date_query'] = $date_query;
				}

				break;

			case 'tag' :
				$args['tag'] = $_POST['term'];
				break;

			case 'category' :
				$args['cat'] = $_POST['term'];
				break;

			case 'post_type' :
				if ($_POST['term'] != ''){
					$args['post_type'] = $_POST['term'];
				}
				break;
		}
		// Allow plugins to modify the query_post arguments
		$posts = new WP_Query( apply_filters( 'anth_get_posts_by_query', $args, $filterby ) );

		$the_posts = Array();
		while ( $posts->have_posts() ) {
			$posts->the_post();

			$post_data = array(
				'title'    => get_the_title(),
				'metadata' => Anthologize_Project_Organizer::get_item_metadata( get_the_ID() ),
			);
			$the_posts[ get_the_ID() ] = $post_data;
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
      			$dest_seq_array[$insert_result_id] = $dest_seq_array['new_new_new'];
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
			$pidarray = explode( '-', $post_id );
			$post_id = array_pop( $pidarray );
			//$post_id = str_replace("added-", "", $post_id);
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
					'comment_count'	=> $comment_count,
					'original_id'	=> $post_id
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

		require_once( ANTHOLOGIZE_INCLUDES_PATH . 'class-comments.php' );
		$comments = new Anthologize_Comments( $post_id );

		// Our next action depends on $action
		switch ( $action ) {
			case 'add' :
				$comments->import_comment( $comment_id );
				break;

			case 'remove' :
			default :
				$comments->remove_comment( $comment_id );
				break;
		}

		// Resave the meta
		$comments->update_included_comments();

		// Return the comment array to show that we were successful
		echo json_encode( array_values( $comments->included_comments ) );
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
	function include_all_comments() {
		if ( !empty( $_POST['post_id'] ) )
			$post_id = $_POST['post_id'];

		$action = !empty( $_POST['check_action'] ) && 'remove' == $_POST['check_action'] ? 'remove' : 'add';

		if ( empty( $post_id ) || empty( $action ) )
			die(); // better error reporting?

		require_once( ANTHOLOGIZE_INCLUDES_PATH . 'class-comments.php' );
		$comments = new Anthologize_Comments( $post_id );

		// Our next action depends on $action
		switch ( $action ) {
			case 'add' :
				$comments->import_all_comments();
				break;

			case 'remove' :
			default :
				$comments->remove_all_comments();
				break;
		}

		// Resave the meta
		$comments->update_included_comments();

		// Return the comment array to show that we were successful
		echo json_encode( array_values( $comments->included_comments ) );
		die();
	}
}

endif;

?>
