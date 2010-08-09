<?php
/*
Activation class. This is fired when the Anthologize plugin is activated in WordPress. Use this for updating necessary information, initializing settings, and so forth. Also, do no evil.
*/

if ( !class_exists( 'Anthologize_Activation' ) ) :

class Anthologize_Activation {

	var $settings;

	function anthologize_activation () {
		$this->unpublish_content();
		$this->namespace_post_types();

		$this->default_settings(); // Settings should be updated last, so that we can take advantage of old version info
	}

	// Unpublishes Anthologize content which was published by default in original release. Required for people updating from version 0.3. In 0.3, there was no version settings, so we have to check for the existence of a version number.
	function unpublish_content() {
		if ( isset( $this->settings['version'] ) )
			return;

		require_once( dirname(__FILE__) . '/class-new-project.php' );
		$new_project = new Anthologize_New_Project();

		$projects = get_posts( array( 'post_type' => 'anth_project' ) );

		foreach( $projects as $project ) {
			$update_project = array(
				'ID' => $project_id->ID,
				'post_status' => 'draft',
			);
			wp_update_post( $update_project );

			$new_project::change_project_status( $project_id->ID, 'draft' );
		}
	}

	function default_settings() {
		if ( !$this->settings = get_option( 'anthologize_settings' ) )
			$this->settings = array();

		$this->settings['version'] = '0.4';

		update_option( 'anthologize_settings', $this->settings );
	}
}

endif; // class exists

?>
