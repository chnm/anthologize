<?php

if ( !class_exists( 'Anthologize_Ajax_Handlers' ) ) :

class Anthologize_Ajax_Handlers {

	function anthologize_ajax_handlers() {
		add_action( 'wp_ajax_get_tags', array( $this, 'get_tags' ) );
		add_action( 'wp_ajax_get_cats', array( $this, 'get_cats' ) );
	}

	function get_tags() {
		$tags = get_tags();

		$the_tags = '';
		foreach( $tags as $tag ) {
			$the_tags .= $tag->term_id . ':' . $tag->name . ',';
		}

		print_r($the_tags);
		die();
		// Get the post and do some shit
	}

	function get_cats() {
		$cats = get_categories();

		$the_cats = '';
		foreach( $cats as $cat ) {
			$the_cats .= $cat->term_id . ':' . $cat->name . ',';
		}

		print_r($the_cats);
		die();
		// Get the post and do some shit
	}
}

endif;


?>