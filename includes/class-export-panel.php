<?php

if ( !class_exists( 'Anthologize_Export_Panel' ) ) :

class Anthologize_Export_Panel {

	var $project_id;

	/**
	 * The export panel. We are the champions, my friends
	 */
	function anthologize_export_panel ( $project_id ) {
		$this->project_id = $project_id;
	}

	function display() {
		if ( isset( $_GET['project_id'] ) )
			$options = get_post_meta( $_GET['project_id'], 'anthologize_meta', true );

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

		if ( extension_loaded('zip') === true )
			$zip_is_enabled = true;

		?>
		<div class="wrap anthologize">

		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
			<h2><?php _e( 'Export Project', 'anthologize' ) ?></h2>

			<br />

			<div id="export-form">

			<?php if ( !isset( $_POST['export-step'] ) ) : ?>

			<form action="" method="post">

			<?php $projects = $this->get_projects() ?>

			<select name="project_id" id="project-id-dropdown">
			<option value=""><?php _e( 'Select Project...', 'anthologize' ) ?></option>
			<?php foreach ( $projects as $project_id => $project_name ) : ?>
				<option value="<?php echo $project_id ?>"

				<?php if ( $project_id == $this->project_id ) : ?>selected="selected"<?php endif; ?>

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

			<input type="hidden" name="export-step" value="1" />
			<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Next', 'anthologize' ) ?>" /></div>

			</form>

			<?php elseif ( $_POST['export-step'] == 1 ) : ?>

			<?php anthologize_save_project_meta() ?>

			<?php $project_id = $_POST['project_id']; ?>
			<?php $project = get_post( $project_id ); ?>

			<form action="admin.php?page=anthologize/includes/class-export-panel.php&project_id=<?php echo $project_id ?>&noheader=true" method="post">

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


				<div style="clear: both;"> </div>

				<h3><?php _e( 'Publishing Options', 'anthologize' ) ?></h3>
				<div id="publishing-options">
					<div style="width: 150px; float: left; padding: 8px;">
						<div class="pub-options-title"><?php _e( 'Type', 'anthologize' ) ?></div>

						<?php if ( $zip_is_enabled ) : ?>
							<input type="radio" name="filetype" value="epub" /> <?php _e( 'ePub', 'anthologize' ) ?><br />
						<?php else : ?>
							<input type="radio" name="filetype" value="epub" disabled="disabled" /> <span class="not-enabled"><?php _e( 'ePub requires the PHP Zip library to be enabled. Contact your hosting provider to enable Zip.', 'anthologize' ) ?></span><br />
						<?php endif; ?>
						<input type="radio" name="filetype" value="pdf" checked="checked" /> <?php _e( 'PDF', 'anthologize' ) ?><br />
						<input type="radio" name="filetype" value="tei" /> <?php _e( 'TEI (plus HTML)', 'anthologize' ) ?><br />

						<input type="radio" name="filetype" value="rtf" /> <?php _e( 'RTF', 'anthologize' ) ?>
					</div>

					<div style="width: 150px; float: left; padding: 8px;">
						<div class="pub-options-title"><?php _e( 'Page Size', 'anthologize' ) ?></div>
						<input type="radio" name="page-size" value="letter" checked="checked" /> <?php _e( 'Letter', 'anthologize' ) ?><br />
						<input type="radio" name="page-size" value="a4" /> <?php _e( 'A4', 'anthologize' ) ?>
					</div>

					<div style="width: 150px; float: left; padding: 8px;">
						<div class="pub-options-title"><?php _e( 'Font Size', 'anthologize' ) ?></div>
						<select name="font-size">
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12" selected="selected">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
						</select>
					</div>

					<div style="width: 150px; float: left; padding: 8px;">
						<div class="pub-options-title"><?php _e( 'Font Face', 'anthologize' ) ?></div>
						<select name="font-face">
							<option value="times" class="serif">Serif</option>
							<option value="helvetica" class="sans-serif">Sans-serif</option>
							<option value="courier" class="fixed-width">Fixed-width</option>
						</select>
					</div>

					<div style="width: 150px; float: left; padding: 8px;">
						<div class="pub-options-title"><?php _e( 'Shortcodes', 'anthologize' ) ?></div>
						<p><small><?php _e( 'WordPress shortcodes (such as [caption]) can sometimes cause problems with output formats. If shortcode content shows up incorrectly in your output, choose "Disable" to keep Anthologize from processing them.', 'anthologize' ) ?></small></p>
						<select name="do-shortcodes">
							<option value="1" checked="checked"><?php _e( 'Enable', 'anthologize' ) ?></option>
							<option value="0"><?php _e( 'Disable', 'anthologize' ) ?></option>
						</select>
					</div>

				</div>

				<input type="hidden" name="cyear" value="<?php echo $_POST['cyear'] ?>" />
				<input type="hidden" name="cname" value="<?php echo $_POST['cname'] ?>" />
				<input type="hidden" name="ctype" value="<?php echo $_POST['ctype'] ?>" />
				<?php if ( $_POST['ctype'] == 'cc' ) : ?>
					<input type="hidden" name="cctype" value="<?php echo $_POST['cctype'] ?>" />
				<?php endif; ?>
				<input type="hidden" name="edition" value="<?php echo $_POST['edition'] ?>" />
				<input type="hidden" name="authors" value="<?php echo $_POST['authors'] ?>" />
				<input type="hidden" name="project_id" value="<?php echo $_POST['project_id'] ?>" />

				<input type="hidden" name="export-step" value="2" />

				<div style="clear: both;"> </div>

				<div class="anthologize-button" id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Export', 'anthologize' ) ?>" /></div>

			</form>


			<?php elseif ( $_POST['export-step'] == 2 ) : ?>
				<!-- Where the magic happens -->
				<?php /* You should never actually get to this point. Method load_template() in anthologize.php should grab all requests with $_POST['filetype'], send a file to the user, and die. If someone ends up here, it means that something has gone awry. */ ?>
				<p>

				<?php /* $this->load_template() */ ?>
			<?php endif; ?>

			</div>
		</div>
		<?php

	}


	function load_template() {
		// The goggles! Zey do nossing!
		// Check anthologize.php for the real handler method load_template, which happens before headers are sent.
	}

	function get_projects() {
		$projects = array();

		query_posts( 'post_type=projects' );

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

if ( isset( $_GET['project_id'] ) )
	$project_id = $_GET['project_id'];

$export_panel = new Anthologize_Export_Panel( $project_id );
$export_panel->display();


?>