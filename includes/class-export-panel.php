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
	?>
		<div class="wrap">

			<h2><?php _e( 'Export Project', 'anthologize' ) ?></h2>

			<div id="export-form">

			<?php if ( !isset( $_POST['submit'] ) ) : ?>

			<form action="" method="post">

			<?php $projects = $this->get_projects() ?>

			<select name="project_id">
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
					<td><input type="text" id="cyear" name="cyear" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Name', 'anthologize' ) ?></th>
					<td><input type="text" id="cname" name="cname" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Type', 'anthologize' ) ?></th>
					<td>
						<input type="radio" id="ctype" name="ctype" value="c" /> <?php _e( 'Copyright', 'anthologize' ) ?><br />
						<input type="radio" id="ctype" name="ctype" value="cc" /> <?php _e( 'Creative Commons', 'anthologize' ) ?>
							<select id="cctype" name="cctype">
								<option value=""><?php _e( 'Select One...', 'anthologize' ) ?></option>
								<option value="by"><?php _e( 'Attribution', 'anthologize' ) ?></option>
								<option value="by-sa"><?php _e( 'Attribution Share-Alike', 'anthologize' ) ?></option>
								<option value="by-nd"><?php _e( 'Attribution No Derivatives', 'anthologize' ) ?></option>
								<option value="by-nc"><?php _e( 'Attribution Non-Commercial', 'anthologize' ) ?></option>
								<option value="by-nc-sa"><?php _e( 'Attribution Non-Commercial Share Alike', 'anthologize' ) ?></option>
								<option value="by-nc-nd"><?php _e( 'Attribution Non-Commercial No Derivatives', 'anthologize' ) ?></option>
							</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Edition', 'anthologize' ) ?></th>
					<td><input type="text" id="edition" name="edition" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e( 'Add Author(s)', 'anthologize' ) ?></th>
					<td><textarea id="authors" name="authors"><?php echo get_post_meta( get_the_ID(), 'author_name', true ) ?></textarea></td>
				</tr>
			</table>

			<div id="export-next"><input type="submit" name="submit" id="submit" value="<?php _e( 'Next', 'anthologize' ) ?>" /></div>

			</form>

			<?php else : ?>

			<?php $project_id = $_POST['project_id']; ?>
			<?php $project = get_post( $project_id ); ?>

			<form action="" method="post">
				<?php print_r($_POST); ?>

				<input type="text" name="post-title" id="post-title" value="<?php echo $project->post_title ?>" />

				<textarea id="dedication" name="dedication"></textarea>
				<textarea id="acknowledgements" name="acknowledgements"></textarea>

				<h3><?php _e( 'Publishing Options', 'anthologize' ) ?></h3>
				<div id="publishing-options">
					<div>
						<div class="pub-options-title"><?php _e( 'Type', 'anthologize' ) ?></div>
						<input type="radio" name="filetype" value="epub" /> <?php _e( 'ePub', 'anthologize' ) ?><br />
						<input type="radio" name="filetype" value="pdf" /> <?php _e( 'PDF', 'anthologize' ) ?><br />
						<input type="radio" name="filetype" value="tei" /> <?php _e( 'TEI', 'anthologize' ) ?>
					</div>
					<div>
						<div class="pub-options-title"><?php _e( 'Size', 'anthologize' ) ?></div>
						<input type="radio" name="size" value="epub" /> <?php _e( 'Letter', 'anthologize' ) ?><br />
						<input type="radio" name="size" value="pdf" /> <?php _e( 'A4', 'anthologize' ) ?>
					</div>



				</div>

				<input type="hidden" name="cyear" value="<?php echo $_POST['cyear'] ?>" />
				<input type="hidden" name="cname" value="<?php echo $_POST['cname'] ?>" />
				<input type="hidden" name="ctype" value="<?php echo $_POST['ctype'] ?>" />
				<?php if ( $_POST['ctype'] == 'cc' ) : ?>
					<input type="hidden" name="cctype" value="<?php echo $_GET['cctype'] ?>" />
				<?php endif; ?>
				<input type="hidden" name="edition" value="<?php echo $_GET['edition'] ?>" />
				<input type="hidden" name="authors" value="<?php echo $_GET['authors'] ?>" />

			</form>

			<?php endif; ?>

			</div>
		</div>
		<?php

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