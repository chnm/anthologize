<?php

class Anthologize_Settings {
	var $settings;
	
	/**
	 * Constructor method
	 *
	 * @package Anthologize
	 * @since 0.6
	 */	
	function __construct() {
		$this->settings = $this->get_settings();
		$this->display();
	}
	
	/**
	 * Loads the settings for the blog
	 *
	 * @package Anthologize
	 * @since 0.6
	 */	
	function get_settings() {
		return get_option( 'anth_settings' );
	}
	
	/**
	 * Catches a saved settings page and saves settings
	 *
	 * @package Anthologize
	 * @since 0.6
	 */	
	function save() {
		check_admin_referer( 'anth_settings' );
	
		$anth_settings = !empty( $_POST['anth_settings'] ) ? $_POST['anth_settings'] : array();
	
		// This needs to be reset so that we don't have to refresh the page
		$this->settings = $anth_settings;
	
		update_option( 'anth_settings', $anth_settings );
	}
	
	/**
	 * Markup for the settings panel
	 *
	 * @package Anthologize
	 * @since 0.6
	 */	
	function display() {
		if ( !empty( $_POST['anth_settings_submit'] ) )
			$this->save();
	
		$minimum_cap = !empty( $this->settings['minimum_cap'] ) ? $this->settings['minimum_cap'] : 'manage_options';
		?>
		
		<div class="wrap anthologize">

			<div id="blockUISpinner">
				<img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
				<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
			</div>
	
			<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
				<h2><?php _e( 'Settings', 'anthologize' ) ?></h2>

			<form action="" method="post" id="bp-admin-form">

			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Minimum role for creating and editing Anthologize projects', 'anthologize' ) ?>:</th>
					<td>
						<select name="anth_settings[minimum_cap]">
						<?php if ( is_multisite() ) : ?>
							<option<?php selected( $minimum_cap, 'manage_network' ) ?> value="manage_network"><?php _e( 'Network Admin', 'anthologize' ) ?></option>
						<?php endif ?>
					
							<option<?php selected( $minimum_cap, 'manage_options' ) ?> value="manage_options"><?php _e( 'Administrator', 'anthologize' ) ?></option>
						
							<option<?php selected( $minimum_cap, 'delete_others_posts' ) ?> value="delete_others_posts"><?php _e( 'Editor', 'anthologize' ) ?></option>
						
							<option<?php selected( $minimum_cap, 'publish_posts' ) ?> value="publish_posts"><?php _e( 'Author', 'anthologize' ) ?></option>
						
							<option<?php selected( $minimum_cap, 'edit_posts' ) ?> value="edit_posts"><?php _e( 'Contributor', 'anthologize' ) ?></option>
						
							<option<?php selected( $minimum_cap, 'read' ) ?> value="read"><?php _e( 'Subscriber', 'anthologize' ) ?></option>
						
						</select>
					</td>
				</tr>
			</tbody>
			</table>
			
			<p class="submit">
				<input class="button-primary" type="submit" name="anth_settings_submit" value="<?php _e( 'Save Settings', 'anthologize' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'anth_settings' ) ?>

			
			</form>
		
		</div>
		
		<?php
	}
}

$anthologize_settings = new Anthologize_Settings;
?>