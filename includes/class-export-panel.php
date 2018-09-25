<?php

if ( !class_exists( 'Anthologize_Export_Panel' ) ) :

class Anthologize_Export_Panel {

	var $project_id;

	/**
	 * Singleton bootstrap
	 *
	 * @since 0.7
	 * @return obj Anthologize instance
	 */
	public static function init() {
		static $instance;
		if ( empty( $instance ) ) {
			$instance = new Anthologize_Export_Panel();
		}
		return $instance;
	}

	/**
	 * The export panel. We are the champions, my friends
	 */
	function __construct() {

		$this->projects = $this->get_projects();

		if ( !isset( $project_id ) ) {
			if ( isset( $_GET['project_id'] ) ) {
				$project_id = $_GET['project_id'];
			} else {
				$keys = array_keys( $this->projects, current( $this->projects ) );
				$project_id = $keys[0];
			}
		}

		$this->project_id = $project_id;

		$export_step = ( isset( $_POST['export-step'] ) ) ? $_POST['export-step'] : '1';

		if ( '1' === $export_step ) {
			anthologize_delete_session();
		}

		if ( $export_step != '3' )
			$this->display();
	}

	function display() {
		wp_enqueue_style( 'anthologize-admin' );

		$project_id = $this->project_id;

		if ( isset( $_POST['export-step'] ) )
			$this->save_session();

		$options = get_post_meta( $project_id, 'anthologize_meta', true );

		$cdate = !empty( $options['cdate'] ) ? $options['cdate'] : date('Y');

		if ( isset( $options['cname'] ) )
			$cname = $options['cname'];
		else if ( isset( $options['author_name'] ) )
			$cname = $options['author_name'];
		else
			$cname = '';

		// Default is Creative Commons
		$ctype = !empty( $options['ctype'] ) ? $options['ctype'] : 'cc';

		$cctype = !empty( $options['cctype'] ) ? $options['cctype'] : 'by';

		// No default for edition number
		$edition = isset( $options['edition'] ) ? isset( $options['edition'] ) : false;

		if ( isset( $options['authors'] ) )
			$authors = $options['authors'];
		else if ( isset( $options['author_name'] ) )
			$authors = $options['authors'];
		else
			$authors = '';

		$dedication = !empty( $options['dedication'] ) ? $options['dedication'] : '';

		$acknowledgements = !empty( $options['acknowledgements'] ) ? $options['acknowledgements'] : '';

		?>
		<div class="wrap anthologize">

		<div id="blockUISpinner">
			<img src="<?php echo plugins_url() ?>/anthologize/images/wait28.gif" alt="<?php esc_html_e( 'Please wait...', 'anthologize' ); ?>" aria-hidden="true" />
			<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
		</div>

		<div id="anthologize-logo"><img src="<?php echo esc_url( plugins_url() . '/anthologize/images/anthologize-logo.gif' ) ?>" alt="<?php esc_attr_e( 'Anthologize logo', 'anthologize' ); ?>" /></div>
			<h2><?php _e( 'Export Project', 'anthologize' ) ?></h2>

			<br />

			<div id="export-form">

			<?php if ( !isset( $_POST['export-step'] ) ) : ?>

			<form action="" method="post">

			<div class="export-project-selector">
				<label for="project-id-dropdown"><?php esc_html_e( 'Select a project:', 'anthologize' ) ?></label>
				<select name="project_id" id="project-id-dropdown">
				<?php foreach ( $this->projects as $proj_id => $project_name ) : ?>
					<option value="<?php echo esc_attr( $proj_id ) ?>"

					<?php if ( $proj_id == $project_id ) : ?>selected="selected"<?php endif; ?>

					><?php echo esc_html( $project_name ); ?></option>
				<?php endforeach; ?>
				</select>
			</div>

			<h3 id="copyright-information-header"><?php _e( 'Copyright Information', 'anthologize' ) ?></h3>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="cyear"><?php _e( 'Year', 'anthologize' ) ?></label></th>
					<td><input type="text" id="cyear" name="cyear" value="<?php echo esc_attr( $cdate ); ?>"/></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="cname"><?php _e( 'Copyright Holder', 'anthologize' ) ?></label></th>
					<td><input type="text" id="cname" name="cname" value="<?php echo esc_attr( $cname ); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row" id="license-type"><?php _e( 'Type', 'anthologize' ) ?></th>
					<td>
						<input role="group" aria-labelledby="license-type" type="radio" id="ctype-copyright" name="ctype" value="c" <?php if ( $ctype == 'c' ) echo 'checked="checked"' ?>/> <label for="ctype-copyright"><?php _e( 'Copyright', 'anthologize' ) ?></label><br />
						<input role="group" aria-labelledby="license-type" type="radio" id="ctype-cc" name="ctype" value="cc" checked="checked" <?php if ( $ctype != 'c' ) echo 'checked="checked"' ?>/> <label for="ctype-cc"><?php _e( 'Creative Commons', 'anthologize' ) ?></label>

						<label for="cctype" class="screen-reader-text"><?php esc_html_e( 'Select Creative Commons license type', 'anthologize' ); ?></label>
						<select id="cctype" name="cctype">
							<option value=""><?php _e( 'Select One...', 'anthologize' ) ?></option>
							<option value="by" <?php if ( $cctype == 'by' ) echo 'selected="selected"' ?>><?php _e( 'Attribution', 'anthologize' ) ?></option>
							<option value="by-sa" <?php if ( $cctype == 'by-sa' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Share-Alike', 'anthologize' ) ?></option>
							<option value="by-nd" <?php if ( $cctype == 'by-nd' ) echo 'selected="selected"' ?>><?php _e( 'Attribution No Derivatives', 'anthologize' ) ?></option>
							<option value="by-nc" <?php if ( $cctype == 'by-nc' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Non-Commercial', 'anthologize' ) ?></option>
							<option value="by-nc-sa" <?php if ( $cctype == 'by-nc-sa' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Non-Commercial Share Alike', 'anthologize' ) ?></option>
							<option value="by-nc-nd" <?php if ( $cctype == 'by-nc-nd' ) echo 'selected="selected"' ?>><?php _e( 'Attribution Non-Commercial No Derivatives', 'anthologize' ) ?></option>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="edition"><?php _e( 'Edition', 'anthologize' ) ?></label></th>
					<td><input type="text" id="edition" name="edition" value="<?php echo esc_attr( $edition ); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="authors"><?php _e( 'Add Author(s)', 'anthologize' ) ?></label></th>
					<td><textarea id="authors" name="authors"><?php echo esc_textarea( $authors ); ?></textarea></td>
				</tr>
			</table>

			<input type="hidden" id="export-step" name="export-step" value="1" />
			<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Next', 'anthologize' ) ?>" /></div>

			</form>

			<?php elseif ( $_POST['export-step'] == 1 ) : ?>

			<?php anthologize_save_project_meta() ?>

			<?php $project_id = $_POST['project_id']; ?>
			<?php $project = get_post( $project_id ); ?>

			<form action="" method="post">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="post-title"><?php esc_html_e( 'Title', 'anthologize' ) ?></label>
						</th>

						<td>
							<input type="text" name="post-title" id="post-title" value="<?php echo esc_attr( $project->post_title ); ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="dedication"><?php esc_html_e( 'Dedication', 'anthologize' ) ?></label>
						</th>

						<td>
							<textarea id="dedication" name="dedication" rows="5"><?php echo esc_textarea( $dedication ); ?></textarea>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="acknowledgements"><?php esc_html_e( 'Acknowledgements', 'anthologize' ) ?></label>
						</th>

						<td>
							<textarea id="acknowledgements" name="acknowledgements" rows="5"><?php echo esc_textarea( $acknowledgements ); ?></textarea>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Export Format', 'anthologize' ); ?>
						</th>

						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Export Format', 'anthologize' ) ?></legend>

								<?php $this->export_format_list() ?>
							</fieldset>
						</td>
					</tr>
				</table>

				<input type="hidden" name="export-step" value="2" />

				<div style="clear: both;"> </div>

				<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Next', 'anthologize' ) ?>" /></div>

			</form>

			<?php elseif ( $_POST['export-step'] == 2 ) : ?>

				<form action="admin.php?page=anthologize_export_project&project_id=<?php echo intval( $project_id ); ?>&noheader=true" method="post">

				<h3><?php $this->export_format_options_title() ?></h3>

				<table class="form-table">

					<?php $this->render_format_options() ?>

					<tr>
						<th scope="row">
							<label for="do-shortcodes"><?php esc_html_e( 'Shortcodes', 'anthologize' ) ?></label>
						</th>

						<td>
							<select name="do-shortcodes" id="do-shortcodes">
								<option value="1" checked="checked"><?php esc_html_e( 'Enable', 'anthologize' ) ?></option>
								<option value="0"><?php esc_html_e( 'Disable', 'anthologize' ) ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'WordPress shortcodes (such as [caption]) can sometimes cause problems with output formats. If shortcode content shows up incorrectly in your output, choose "Disable" to keep Anthologize from processing them.', 'anthologize' ) ?></p>
						</td>
					</tr>

				</table>

				<input type="hidden" name="export-step" value="3" />

				<div style="clear: both;"> </div>

				<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Export', 'anthologize' ) ?>" /></div>

				</form>



			<?php elseif ( $_POST['export-step'] == 3 ) : ?>
				<!-- Where the magic happens -->
				<?php /* You should never actually get to this point. Method load_template() in anthologize.php should grab all requests with $_POST['filetype'], send a file to the user, and die. If someone ends up here, it means that something has gone awry. */ ?>
				<p>

				<?php /* $this->load_template() */ ?>
			<?php endif; ?>

			</div>
		</div>
		<?php

	}

	function export_format_options_title() {
		global $anthologize_formats;

		$session = anthologize_get_session();
		$format = $session['filetype'];

		$title = sprintf( __( '%s Publishing Options', 'anthologize' ), $anthologize_formats[$format]['label'] );

		echo esc_html( $title );
	}

	public static function save_session() {
		$keys = anthologize_get_session_data_keys();
		$data = array();

		foreach ( $keys as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}

			$data[ $key ] = wp_unslash( $_POST[ $key ] );
		}

		anthologize_save_session( $data );
	}

	function export_format_list() {
		global $anthologize_formats;

		// Check the first one.
		$checked = true;

		foreach ( $anthologize_formats as $name => $fdata ) {
			$option_id = 'option-format-' . $name;

			?>

			<input type="radio" id="<?php echo esc_attr( $option_id ) ?>" name="filetype" value="<?php echo esc_attr( $name ) ?>" <?php checked( $checked ); ?> /> <label for="<?php echo esc_attr( $option_id ) ?>"><?php echo esc_html( $fdata['label'] ); ?></label><br />

			<?php

			$checked = false;
		}

		do_action( 'anthologize_export_format_list' );
	}

	function render_format_options() {
		global $anthologize_formats;

		$session = anthologize_get_session();
		$format = $session['filetype'];

		if ( $fdata = $anthologize_formats[$format] ) {
			$return = '';
			foreach( $fdata as $oname => $odata ) {

				if ( $oname == 'label' || $oname == 'loader-path' )
					continue;

				if ( !$odata )
					continue;

				$default = ( isset( $odata['default'] ) ) ? $odata['default'] : false;

				$return .= '<tr>';

				$return .= '<th scope="row">';
				$return .= sprintf( '<label for="%s">', esc_attr( $oname ) );
				$return .= esc_html( $odata['label'] );
				$return .= '</label></th>';

				$return .= '<td>';
				switch( $odata['type'] ) {
					case 'checkbox':
						$return .= $this->build_checkbox( $oname, $odata['label'] );
						break;

					case 'dropdown':
						$return .= $this->build_dropdown( $oname, $odata['label'], $odata['values'], $default );
						break;

					// Default is a textbox
					default:
						$return .= $this->build_textbox( $oname, $odata['label'] );
						break;
				}
				$return .= '</td>';

				$return .= '</tr>';

			}
		} else {
			$return = esc_html__( 'This appears to be an invalid export format. Please try again.', 'anthologize' );
		}

		echo $return;
	}

	function build_checkbox( $name, $label ) {

		$html = '<input name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) .'" type="checkbox">';

		return apply_filters( 'anthologize_build_checkbox', $html, $name, $label );
	}

	function build_dropdown( $name, $label, $options, $default ) {
		// $name is the input name (no spaces, eg 'page-size')
		// $label is the input label (for display, eg 'Page Size'. Should be internationalizable, eg __('Page Size', 'anthologize')
		// $options is associative array where keys are option values and values are the text displayed in the option field.
		// $default is the default option

		$html = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';

		foreach( $options as $ovalue => $olabel ) {
			$html .= '<option value="' . esc_attr( $ovalue ) . '"';

			if ( $default == $ovalue )
				$html .= ' selected="selected"';

			$html .= '>' . esc_html( $olabel ) . '</option>';
		}

		$html .= '</select>';

		return apply_filters( 'anthologize_build_dropdown', $html, $name, $label, $options );
	}

	function build_textbox( $name, $label ) {

		$html = '<input name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" type="text">';

		return apply_filters( 'anthologize_build_textbox', $html, $name, $label );
	}

	function load_template() {
		// The goggles! Zey do nossing!
		// Check anthologize.php for the real handler method load_template, which happens before headers are sent.
	}

	function get_projects() {
		$projects = array();

		query_posts( 'post_type=anth_project&orderby=title&order=ASC&posts_per_page=-1' );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$projects[get_the_ID()] = get_the_title();
			}
		}

		return $projects;
	}
}

endif;
