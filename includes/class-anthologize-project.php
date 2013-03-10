<?php

class Anthologize_Project {
	public function __construct( $project_id = false ) {
		if ( $project_id ) {
			$this->setup( $project_id );
		}
	}

	protected function setup( $project_id ) {
		$project_id = intval( $project_id );
		if ( ! $project_id ) {
			return;
		}

		$post = get_post( $project_id );
		if ( empty( $post ) || is_wp_error( $post ) || 'anth_project' != $post->post_type ) {
			return;
		}

		$this->id = $project_id;
		$this->post = $post;
	}
}
