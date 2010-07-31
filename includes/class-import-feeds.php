<?php


if ( !class_exists( 'Anthologize_Import_Feeds_Panel' ) ) :

class Anthologize_Import_Feeds_Panel {

	/**
	 *
	 */
	function anthologize_import_feeds_panel ( ) {
		$this->display();

	}

	function display() {

	?>
		<div class="wrap">

			<h2><?php _e( 'Import Content', 'anthologize' ) ?></h2>

			<?php if ( !isset( $_POST['feedurl'] ) ) : ?>

				<div id="export-form">

				<p><?php _e( 'Want to populate your Anthologize project with content from another web site? Enter the RSS feed address of the site from which you\'d like to import and click Go.', 'anthologize' ) ?></p>


				<form action="" method="post">

				<h4><?php _e( 'Feed URL:', 'anthologize' ) ?></h4>
				<input type="text" name="feedurl" id="feedurl" size="100" />

				<p><input type="submit" name="submit" id="submit" value="<?php _e( 'Go', 'anthologize' ) ?>" /></p>

				</form>

			<?php else : ?>

				<?php include_once( ABSPATH . 'wp-includes/rss.php' ) ?>

				<div id="export-form">

				<p><?php _e( 'Select the items you\'d like to import to your Imported Items library and click Import.', 'anthologize' ) ?></p>

				<p><?php _e( 'Or enter a new feed URL and click Go to import different feed content.', 'anthologize' ) ?></p>

				<form action="" method="post">

					<h4><?php _e( 'Feed items:', 'anthologize' ) ?></h4>

					<p><input type="submit" name="submit_items" id="submit" value="<?php _e( 'Import', 'anthologize' ) ?>" /></p>

				</form>


				<form action="" method="post">

					<h4><?php _e( 'Feed URL:', 'anthologize' ) ?></h4>
					<input type="text" name="feedurl" id="feedurl" size="100" value="<?php echo $_POST['feedurl'] ?>" />

					<p><input type="submit" name="submit" id="submit" value="<?php _e( 'Go', 'anthologize' ) ?>" /></p>

				</form>


				</div>

			<?php endif; ?>

			</div>
		</div>
		<?php

	}

}

endif;

if ( isset( $_GET['project_id'] ) )
	$project_id = $_GET['project_id'];

$import_feeds_panel = new Anthologize_Import_Feeds_Panel( $project_id );

?>