<?php

if ( !class_exists( 'Anthologize_Ajax_Handlers' ) ) :

class Anthologize_Ajax_Handlers {

	function anthologize_ajax_handlers() {
		add_action( 'wp_ajax_get_tags', array( $this, 'get_tags' ) );
		add_action( 'wp_ajax_get_cats', array( $this, 'get_cats' ) );
		add_action( 'wp_ajax_get_posts_by', array( $this, 'get_posts_by' ) );
		add_action( 'wp_ajax_place_item', array( $this, 'place_item' ) );
		add_action( 'wp_ajax_merge_items', array( $this, 'merge_items' ) );
		add_action( 'wp_ajax_update_post_metadata', array( $this, 'update_post_metadata' ) );
		add_action( 'wp_ajax_remove_item_part', array( $this, 'remove_item_part' ) );
		add_action( 'wp_ajax_insert_new_item', array( $this, 'insert_new_item' ) );
		add_action( 'wp_ajax_insert_new_part', array( $this, 'insert_new_part' ) );
	}

	function get_tags() {
		$tags = get_tags();

		$the_tags = '';
		foreach( $tags as $tag ) {
			$the_tags .= $tag->term_id . ':' . $tag->name . ',';
		}

		print_r($the_tags);
		die();
	}

	function get_cats() {
		$cats = get_categories();

		$the_cats = '';
		foreach( $cats as $cat ) {
			$the_cats .= $cat->term_id . ':' . $cat->name . ',';
		}

		print_r($the_cats);
		die();
	}

	function get_posts_by() {
		$term = $_POST['term'];
		$tagorcat = $_POST['tagorcat'];

		// Blech
		$t_or_c = ( $tagorcat == 'tag' ) ? 'tag_id' : 'cat';

		$args = array(
			'post_type' => array('post', 'page', 'imported_items' ),
			$t_or_c => $term,
			'posts_per_page' => -1
		);


		query_posts( $args );

		$response = '';

		while ( have_posts() ) {
			the_post();
			$response .= get_the_ID() . ':' . get_the_title() . ',';
		}

		print_r($response);

		die();
	}

    function place_item() {

        die();
    }

    function merge_items() {

        die();
    }

    function update_post_metadata() {

        die();
    }

    function remove_item_part() {

        die();
    }

    function insert_new_item() {

        die();
    }
    
    function insert_new_part() {

        die();
    }
}

endif;


?>
