<?php

class Anthologize_UnitTest_Factory_For_Project extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'post_status' => 'publish',
			'post_title' => new WP_UnitTest_Generator_Sequence( 'Project title %s' ),
			'post_content' => new WP_UnitTest_Generator_Sequence( 'Project description %s' ),
			'post_type' => 'anth_project'
		);
	}

	function create_object( $args ) {
		$post_id = wp_insert_post( $args );
		return $post_id;
	}

	/**
	 * @todo
	 */
	function update_object( $object, $fields ) {}

	function get_object_by_id( $post_id ) {
		return get_post( $post_id );
	}
}

