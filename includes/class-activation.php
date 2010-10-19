<?php
/*
Activation class. This is fired when the Anthologize plugin is activated in WordPress. Use this for updating necessary information, initializing settings, and so forth. Also, do no evil.
*/

if ( !class_exists( 'Anthologize_Activation' ) ) :

class Anthologize_Activation {

	var $settings;

	function anthologize_activation () {
		if ( !$this->settings = get_option( 'anthologize_settings' ) )
			$this->settings = array();

		$version = ( isset( $this->settings['version'] ) ) ? $this->settings['version'] : '0.3';			

		// Fixes for those coming from v0.3
		if ( $version < '0.4' ) {
			$this->namespace_post_types();
			$this->unpublish_content();
		}
		
		// Fixes for those coming from v0.4
		if ( $version < '0.5' ) {
			$this->unpublish_imported_content();
			$this->flush_cookies();
		}
		
		$this->default_settings(); // Settings should be updated last, so that we can take advantage of old version info
	}

	// Unpublishes Anthologize content which was published by default
	// in original release. Required for people updating from version
	// 0.3. In 0.3, there was no version settings, so we have to
	// check for the existence of a version number.
	function unpublish_content() {

		require_once( dirname(__FILE__) . '/class-new-project.php' );
		$new_project = new Anthologize_New_Project();

		$projects = get_posts( array( 'post_type' => 'anth_project', 'nopaging' => true ) );

		foreach( $projects as $project ) {
			$update_project = array(
				'ID' => $project_id->ID,
				'post_status' => 'draft',
			);
			wp_update_post( $update_project );

			$new_project->change_project_status( $project_id->ID, 'draft' );
		}
	}

	// In version 0.3, Anthologize post types were not namespaced.
	// This function sweeps through content created with 0.3 (with
	// a reasonable degree of certainty that post types like
	// 'projects' and 'parts' are created by Anthologize) and
	// changes them to the new, namespaced versions
	function namespace_post_types() {

		$post_type_array = array(
			'projects' => 'anth_project',
			'parts' => 'anth_part',
			'library_items' => 'anth_library_item',
			'imported_items' => 'anth_imported_item'
		);

		foreach( $post_type_array as $old => $new ) {

			$args = array(
				'post_type' => $old,
				'post_status' => array( 'publish', 'draft' ),
				'nopaging' => true
			);
			$posts = get_posts( $args );

			foreach( $posts as $post ) {
				$update_post = array(
					'ID' => $post->ID,
					'post_type' => $new,
				);

				wp_update_post( $update_post );
			}

			unset( $posts );
		}
	}
	
	// Unpublishes imported content which was published by default
	// in original release and v0.4.
	function unpublish_imported_content() {

		$i_posts = get_posts( array( 'post_type' => 'anth_imported_item', 'nopaging' => true ) );

		foreach( $i_posts as $i_post ) {
			$update_post = array(
				'ID' => $i_post->ID,
				'post_status' => 'draft',
			);
			wp_update_post( $update_post );
		}
	}
	
	// Flushes post filter cookies
	function flush_cookies() {
		setcookie('anth-term', '', time() - 60, '/', '', 0);
		setcookie('anth-filter', '', time() - 60, '/', '', 0);
	}
	

	function default_settings() {
		$this->settings['version'] = ANTHOLOGIZE_VERSION;
		
		update_option( 'anthologize_settings', $this->settings );
	}
}

endif; // class exists

?>
