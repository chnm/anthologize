<?php

if ( !class_exists( 'Anthologize_Import_Feeds_Panel' ) ) :

class Anthologize_Import_Feeds_Panel {

	/**
	 *	Creates the Dashboard Panel for importing feed content, and defines the business functions
	 */
	function anthologize_import_feeds_panel ( ) {
		$this->display();

	}

	function display() {

	?>
		<div class="wrap anthologize">

		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
			<h2><?php _e( 'Import Content', 'anthologize' ) ?></h2>

			<?php if ( !isset( $_POST['feedurl'] ) && !isset( $_POST['copyitems'] ) ) : ?>

				<div id="export-form">

				<p><?php _e( 'Want to populate your Anthologize project with content from another web site? Enter the RSS feed address of the site from which you\'d like to import and click Go.', 'anthologize' ) ?></p>

				<p><?php _e( 'Please respect the rights of copyright holders when using this import tool.', 'anthologize' ) ?></p>

				<form action="" method="post">

				<h4><?php _e( 'Feed URL:', 'anthologize' ) ?></h4>
				<input type="text" name="feedurl" id="feedurl" size="100" />

				<div class="anthologize-button"><input type="submit" name="submit" id="submit" value="<?php _e( 'Go', 'anthologize' ) ?>" /></div>

				</form>

			<?php elseif ( isset( $_POST['feedurl'] ) && !isset( $_POST['copyitems'] ) ) : ?>
				<?php $items = $this->grab_feed( $_POST['feedurl'] ) ?>
				<?php if ( isset( $items['error'] ) ) : ?>

					<p><?php _e( 'Sorry, no items were found. Please try another feed address.', 'anthologize' ) ?></p>

				<?php else : ?>

				<?php

				$the_items = serialize( $items );
				$_SESSION['items'] = $the_items;

				?>

				<div id="export-form">

				<p><?php _e( 'Select the items you\'d like to import to your Imported Items library and click Import.', 'anthologize' ) ?></p>

				<form action="" method="post">

					<h3><?php _e( 'Feed items:', 'anthologize' ) ?></h3>

					<ul class="potential-feed-items">
					<?php foreach ( $items as $key => $item ) : ?>
						<?php
							$author = '';
							foreach ( $item['authors'] as $author ) {
								$author .= $author->name . ' ';
							}
						?>
						<li>
							<input name="copyitems[]" type="checkbox" checked="checked" value="<?php echo $key ?>"> <strong><?php echo $item['title'] ?></strong>  <?php echo $item['description'] ?>
						</li>
					<?php endforeach; ?>
					</ul>

					<input type="hidden" name="feedurl" value="<?php echo $_POST['feedurl'] ?>" />
					<div class="anthologize-button"><input type="submit" name="submit_items" id="submit" value="<?php _e( 'Import', 'anthologize' ) ?>" /></div>

				</form>


				<p><?php _e( 'Or enter a new feed URL and click Go to import different feed content.', 'anthologize' ) ?></p>

				<form action="" method="post">

					<h3><?php _e( 'Feed URL:', 'anthologize' ) ?></h3>
					<input type="text" name="feedurl" id="feedurl" size="100" value="<?php echo $_POST['feedurl'] ?>" />

					<div class="anthologize-button"><input type="submit" name="submit" id="submit" value="<?php _e( 'Go', 'anthologize' ) ?>" /></div>

				</form>


				</div>


				<?php endif; ?>

			<?php elseif ( isset( $_POST['copyitems'] ) ) : ?>
				<?php

				$items = $this->grab_feed( $_POST['feedurl'] );

				if ( !isset( $items['error'] ) ) {


				foreach ( $items as $key => $item ) {
					if ( !in_array( $key, $_POST['copyitems'] ) )
						unset( $items[$key] );
				}
				$items = array_values( $items );

				?>

				<?php $imported_items = array(); ?>
				<?php foreach( $items as $item ) : ?>
					<?php $imported_items[] = $this->import_item( $item ) ?>
				<?php endforeach; ?>

				<?php $howmany = count( $imported_items ) ?>

				<h3><?php _e( 'Successfully imported!', 'anthologize' ) ?></h3>

				<?php } else { ?>

				<h3><?php _e( 'No items found. Please try another feed address.', 'anthologize' ) ?></h3>

				<?php } ?>


				<p><a href="admin.php?page=anthologize"><?php _e( 'Back to Anthologize', 'anthologize' ) ?></a></p>

			<?php endif; ?>

			</div>
		</div>
		<?php

	}

	function grab_feed( $feedurl ) {

		include_once( ABSPATH . 'wp-includes/rss.php' );

		$rss = fetch_feed( trim( $feedurl ) );

		if ( $rss->errors )
			return array( 'error' => 'unknown-error' );

		if ( !$maxitems = $rss->get_item_quantity() )
			return array( 'error' => 'no-items' );

		$feed_title = $rss->get_title();
		$feed_permalink = $rss->get_permalink();

		$rss_items = $rss->get_items(0, $maxitems);

		$items_data = array( 'feed_title' => $feed_title, 'feed_permalink' => $feed_permalink );

		$items = array();
		foreach ($rss->get_items(0, $maxitems) as $rss_item ) {
			$item_data = $items_data;

			$item_data['link'] = $rss_item->get_link();
			$item_data['title'] = $rss_item->get_title();
			$item_data['authors'] = $rss_item->get_authors();
			$item_data['created_date'] = $rss_item->get_date();
			$item_data['categories'] = $rss_item->get_categories();
			$item_data['contributors'] = $rss_item->get_contributors();
			$item_data['copyright'] = $rss_item->get_copyright();
			$item_data['description'] = $rss_item->get_description();
			$item_data['content'] = $rss_item->get_content();
			$item_data['permalink'] = $rss_item->get_permalink();

			$items[] = $item_data;
			//$this->record_item( $item_data );
		}

		return $items;
	}

	function import_item( $item ) {
		global $current_user;

		$tags = array();

		foreach( $item['categories'] as $cat ) {
			if ( $cat->term )
				$tags[] = $cat->term;
		}
		
		$args = array(
			'post_status' => 'draft',
			'post_type' => 'anth_imported_item',
			'post_author' => $current_user->ID,
			'guid' => $item['permalink'],
			'post_content' => $item['content'],
			'post_excerpt' => $item['description'],
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_title' => $item['title'],
			'tags_input' => $tags
		);
		
		if ( isset( $item['created_date'] ) ) {
			$original_post_date = date( "Y-m-d H:i:s", strtotime( $item['created_date'] ) );
			$args['post_date'] = $original_post_date;
			$args['post_date_gmt'] = $original_post_date;
		}

		$post_id = wp_insert_post( $args );

		$author_name = $item['authors'][0]->name;
		update_post_meta( $post_id, 'author_name', $author_name );
		update_post_meta( $post_id, 'imported_item_meta', $item );

		return $post_id;
	}

}

endif;

$import_feeds_panel = new Anthologize_Import_Feeds_Panel();

?>