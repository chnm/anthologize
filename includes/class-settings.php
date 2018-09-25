<?php

class Anthologize_Settings {
	var $settings;
	var $site_settings;

	var $minimum_cap;
	var $forbid_local_caps;

	/**
	 * Singleton bootstrap
	 *
	 * @since 0.7
	 * @return obj Anthologize instance
	 */
	public static function init() {
		static $instance;
		if ( empty( $instance ) ) {
			$instance = new Anthologize_Settings();
		}
		return $instance;
	}

	/**
	 * Constructor method
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	function __construct() {
		$this->settings = $this->get_settings();
		$this->site_settings = $this->get_site_settings();
		$this->forbid_local_caps = $this->forbid_local_caps();
		$this->minimum_cap = $this->minimum_cap();

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
	 * Loads the settings for the blog
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	function get_site_settings() {
		$site_settings = array();

		if ( is_multisite() )
			$site_settings = get_site_option( 'anth_site_settings' );

		return apply_filters( 'anth_site_settings', $site_settings );
	}

	/**
	 * Determine whether the network admin has forbidden the setting of local caps
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	function forbid_local_caps() {
		$forbid_local_caps = false;

		if ( !empty( $this->site_settings['forbid_per_blog_caps'] ) )
			$forbid_local_caps = true;

		return apply_filters( 'anth_forbid_local_caps', $forbid_local_caps );
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

		// These need to be reset so that we don't have to refresh the page
		$this->settings = $anth_settings;
		$this->minimum_cap = $anth_settings['minimum_cap'];

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
		?>

		<div class="wrap anthologize">

			<div id="blockUISpinner">
				<img src="<?php echo plugins_url() ?>/anthologize/images/wait28.gif" alt="<?php esc_html_e( 'Please wait...', 'anthologize' ); ?>" aria-hidden="true" />
				<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
			</div>

			<div id="anthologize-logo"><img src="<?php echo esc_url( plugins_url() . '/anthologize/images/anthologize-logo.gif' ) ?>" alt="<?php esc_attr_e( 'Anthologize logo', 'anthologize' ); ?>" /></div>
				<h2><?php _e( 'Settings', 'anthologize' ) ?></h2>

			<form action="" method="post" id="bp-admin-form">

			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="minimum-cap"><?php _e( 'Minimum role for creating and editing Anthologize projects', 'anthologize' ) ?>:</label></th>
					<td>
						<select name="anth_settings[minimum_cap]" id="minimum-cap" <?php if ( $this->forbid_local_caps ) : ?>disabled="disabled"<?php endif ?>>
						<?php if ( is_multisite() ) : ?>
							<option<?php selected( $this->minimum_cap, 'manage_network' ) ?> value="manage_network"><?php _e( 'Network Admin', 'anthologize' ) ?></option>
						<?php endif ?>

							<option<?php selected( $this->minimum_cap, 'manage_options' ) ?> value="manage_options"><?php _e( 'Administrator', 'anthologize' ) ?></option>

							<option<?php selected( $this->minimum_cap, 'delete_others_posts' ) ?> value="delete_others_posts"><?php _e( 'Editor', 'anthologize' ) ?></option>

							<option<?php selected( $this->minimum_cap, 'publish_posts' ) ?> value="publish_posts"><?php _e( 'Author', 'anthologize' ) ?></option>

							<?php /* I think it doesn't make sense for these to be available */ ?>
							<?php /*
							<option<?php selected( $this->minimum_cap, 'edit_posts' ) ?> value="edit_posts"><?php _e( 'Contributor', 'anthologize' ) ?></option>

							<option<?php selected( $this->minimum_cap, 'read' ) ?> value="read"><?php _e( 'Subscriber', 'anthologize' ) ?></option>
							*/ ?>
						</select>
						<?php if ( $this->forbid_local_caps ) : ?>
							<p class="description"><?php _e( 'Your network administrator has disabled this setting.', 'anthologize' ) ?></p>
						<?php endif ?>
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

	function minimum_cap() {
		$default_cap = 'manage_options';

		if ( is_multisite() ) {
			// On multisite, the network admin is able to override the local admin's
			// settings
			$forbid_local_caps = !empty( $this->site_settings['forbid_per_blog_caps'] ) ? true : false;

			if ( $this->forbid_local_caps ) {
				$minimum_cap = !empty( $this->site_settings['minimum_cap'] ) ? $this->site_settings['minimum_cap'] : 'manage_options';
			} else {
				// If the network admin has not forbidden local caps, we still must
				// check whether there's a network default
				$default_cap = !empty( $this->site_settings['minimum_cap'] ) ? $this->site_settings['minimum_cap'] : 'manage_options';
				$minimum_cap = !empty( $this->settings['minimum_cap'] ) ? $this->settings['minimum_cap'] : $default_cap;
			}
		} else {
			// On non-MS, we can check the local settings directly
			$minimum_cap = !empty( $this->settings['minimum_cap'] ) ? $this->settings['minimum_cap'] : $default_cap;
		}

		return apply_filters( 'anth_settings_minimum_cap', $minimum_cap );
	}
}
