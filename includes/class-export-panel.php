<?php

if ( !class_exists( 'Anthologize_Export_Panel' ) ) :

class Anthologize_Export_Panel {

	var $project_id;

	/**
	 * The export panel. We are the champions, my friends
	 */
	function anthologize_export_panel () {

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
		
		if ( $export_step != '3' )
			$this->display();
	}

	function display() {
		$project_id = $this->project_id;
		
		if ( isset( $_POST['export-step'] ) )
			$this->save_session();

		$options = get_post_meta( $project_id, 'anthologize_meta', true );

		if ( !$cdate = $options['cdate'] )
			$cdate = date('Y');

		if ( !$cname = $options['cname'] )
			$cname = $options['author_name'];

		// Default is Creative Commons
		if ( !$ctype = $options['ctype'] )
			$ctype = 'cc';

		if ( !$cctype = $options['cctype'] )
			$cctype = 'by';

		// No default for edition number
		$edition = $options['edition'];

		if ( !$authors = $options['authors'] )
			$authors = $options['author_name'];

		$dedication = $options['dedication'];

		$acknowledgements = $options['acknowledgements'];

		?>
		<div class="wrap anthologize">

		<div id="blockUISpinner">
			<img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
			<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
		</div>

		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
			<h2><?php _e( 'Export Project', 'anthologize' ) ?></h2>

			<br />

			<div id="export-form">

			<?php if ( !isset( $_POST['export-step'] ) ) : ?>

			<form action="" method="post">

			<label for="project_id"><?php _e( 'Select a project...', 'anthologize' ) ?></label>
			<select name="project_id" id="project-id-dropdown">
			<?php foreach ( $this->projects as $proj_id => $project_name ) : ?>
				<option value="<?php echo $proj_id ?>"

				<?php if ( $proj_id == $project_id ) : ?>selected="selected"<?php endif; ?>

				><?php echo $project_name ?></option>
			<?php endforeach; ?>
			</select>

			<h3 id="copyright-information-header"><?php _e( 'Copyright Information', 'anthologize' ) ?></h3>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Year', 'anthologize' ) ?></th>
					<td><input type="text" id="cyear" name="cyear" value="<?php echo $cdate ?>"/></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Copyright Holder', 'anthologize' ) ?></th>
					<td><input type="text" id="cname" name="cname" value="<?php echo $cname ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Type', 'anthologize' ) ?></th>
					<td>
						<input type="radio" id="ctype" name="ctype" value="c" <?php if ( $ctype == 'c' ) echo 'checked="checked"' ?>/> <?php _e( 'Copyright', 'anthologize' ) ?><br />
						<input type="radio" id="ctype" name="ctype" value="cc" checked="checked" <?php if ( $ctype != 'c' ) echo 'checked="checked"' ?>/> <?php _e( 'Creative Commons', 'anthologize' ) ?>
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
					<th scope="row"><?php _e( 'Edition', 'anthologize' ) ?></th>
					<td><input type="text" id="edition" name="edition" value="<?php echo $edition ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Add Author(s)', 'anthologize' ) ?></th>
					<td><textarea id="authors" name="authors"><?php echo $authors ?></textarea></td>
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

				<?php _e( 'Title', 'anthologize' ) ?> <input type="text" name="post-title" id="post-title" value="<?php echo $project->post_title ?>" size="100"/>

				<div style="clear: both;"> </div><br />

				<div style="width: 400px; float: left;">
					<p><strong><?php _e( 'Dedication', 'anthologize' ) ?></strong></p>
					<textarea id="dedication" name="dedication" cols=35 rows=15><?php echo $dedication ?></textarea>
				</div>

				<div style="width: 400px; float: left;">
					<p><strong><?php _e( 'Acknowledgements', 'anthologize' ) ?></strong></p>
					<textarea id="acknowledgements" name="acknowledgements" cols=35 rows=15><?php echo $acknowledgements ?></textarea>
				</div>
				
				<div style="clear: both;"></div>
				
				<div id="export-format">
					<h4><?php _e( 'Export Format', 'anthologize' ) ?></h4>
					
					<?php $this->export_format_list() ?>
				</div>
				
				<input type="hidden" name="export-step" value="2" />

				<div style="clear: both;"> </div>

				<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Next', 'anthologize' ) ?>" /></div>

			</form>
			
			<?php elseif ( $_POST['export-step'] == 2 ) : ?>
								
				<form action="admin.php?page=anthologize/includes/class-export-panel.php&project_id=<?php echo $project_id ?>&noheader=true" method="post">

				<h3><?php $this->export_format_options_title() ?></h3>
				<div id="publishing-options">

					<?php $this->render_format_options() ?>


					<div class="export-options-box">
						<div class="pub-options-title"><?php _e( 'Shortcodes', 'anthologize' ) ?></div>
						<p><small><?php _e( 'WordPress shortcodes (such as [caption]) can sometimes cause problems with output formats. If shortcode content shows up incorrectly in your output, choose "Disable" to keep Anthologize from processing them.', 'anthologize' ) ?></small></p>
						<select name="do-shortcodes">
							<option value="1" checked="checked"><?php _e( 'Enable', 'anthologize' ) ?></option>
							<option value="0"><?php _e( 'Disable', 'anthologize' ) ?></option>
						</select>
					</div>

				</div>
				
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
		
		$format = $_SESSION['filetype'];
	
		$title = sprintf( __( '%s Publishing Options', 'anthologize' ), $anthologize_formats[$format]['label'] );
		
		echo $title;
	}

	function save_session() {
		
		if ( $_POST['export-step'] == '2' )
			$_SESSION['outputParams'] = array( 'format' => $_POST['filetype'] );
		
		// outputParams need to be reset at step 3 so that
		// on a refresh null values will overwrite
		if ( $_POST['export-step'] == '3' )
			$_SESSION['outputParams'] = array( 'format' => $_SESSION['outputParams']['filetype'] );
				
		
		foreach ( $_POST as $key => $value ) {
			if ( $key == 'submit' || $key == 'export-step' )
				continue;
		
			if ( $key == '' )
				echo "OK";
			
			if ( $_POST['export-step'] == '3' )
				$_SESSION['outputParams'][$key] = stripslashes( $value );
			else
				$_SESSION[$key] = stripslashes( $value );
		
		}
	
	}
	
	function export_format_list() { 
		global $anthologize_formats;
	?>
		<?php foreach( $anthologize_formats as $name => $fdata ) : ?>
		
			<input type="radio" name="filetype" value="<?php echo $name ?>" /> <?php echo $fdata['label'] ?><br />
					
		<?php endforeach; ?>
	
		<?php do_action( 'anthologize_export_format_list' ) ?>

	<?php
	}
	
	function render_format_options() {
		global $anthologize_formats;
		
		$format = $_SESSION['filetype'];
		
		if ( $fdata = $anthologize_formats[$format] ) {
			$return = '';
			foreach( $fdata as $oname => $odata ) {
			
				if ( $oname == 'label' || $oname == 'loader-path' )
					continue;
				
				if ( !$odata )
					continue;
				
				$default = ( isset( $odata['default'] ) ) ? $odata['default'] : false;
				
				$return .= '<div class="export-options-box">'; 
		
				$return .= '<div class="pub-options-title">' . $odata['label'] . '</div>';
				
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
				
				$return .= '</div>';
				
			}
		} else {
			$return = __( 'This appears to be an invalid export format. Please try again.', 'anthologize' );
		}
					
		echo $return;
	}

	function build_checkbox( $name, $label ) {
		
		$html = '<input name="' . $name . '" id="' . $name .'" type="checkbox">';
		
		return apply_filters( 'anthologize_build_checkbox', $html, $name, $label );
	}

	function build_dropdown( $name, $label, $options, $default ) {
		// $name is the input name (no spaces, eg 'page-size')
		// $label is the input label (for display, eg 'Page Size'. Should be internationalizable, eg __('Page Size', 'anthologize')
		// $options is associative array where keys are option values and values are the text displayed in the option field.
		// $default is the default option
						
		$html = '<select name="' . $name . '">';
		
		foreach( $options as $ovalue => $olabel ) {
			$html .= '<option value="' . $ovalue . '"';
			
			if ( $default == $ovalue )
				$html .= ' selected="selected"';
						
			$html .= '>' . $olabel . '</option>';
		}	
		
		$html .= '</select>';
		
		return apply_filters( 'anthologize_build_dropdown', $html, $name, $label, $options );
	}
	
	function build_textbox( $name, $label ) {
					
		$html = '<input name="' . $name . '" id="' . $name . '" type="text">';
		
		return apply_filters( 'anthologize_build_textbox', $html, $name, $label );
	}

	function load_template() {
		// The goggles! Zey do nossing!
		// Check anthologize.php for the real handler method load_template, which happens before headers are sent.
	}

	function get_projects() {
		$projects = array();

		query_posts( 'post_type=anth_project&orderby=title&order=ASC' );

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

$export_panel = new Anthologize_Export_Panel();


?>
