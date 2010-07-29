<?php

if ( !class_exists( 'Anthologize_Project_Organizer' ) ) :

class Anthologize_Project_Organizer {

	var $project_id;

	/**
	 * The project organizer. Git 'er done
	 */
	function anthologize_project_organizer ( $project_id ) {

		$this->project_id = $project_id;

		$project = get_post( $project_id );

		$this->project_name = $project->post_title;

	}

	function display() {

		if ( isset( $_POST['new_item'] ) )
			$this->add_item_to_part( $_POST['item_id'], $_POST['part_id'] );

		if ( isset( $_POST['new_part'] ) )
			$this->add_new_part( $_POST['new_part_name'] );

		if ( isset( $_POST['move_up'] ) )
			$this->move_up( $_POST['move_up'] );

		if ( isset( $_POST['move_down'] ) )
			$this->move_down( $_POST['move_up'] );

		?>
		<div class="wrap">

			<h2><?php echo $this->project_name ?></h2>

			<?php $this->list_existing_parts() ?>

			<h3>New Parts</h3>
			<p>Wanna create a new part? You know you do.</p>
			<form action="" method="post">
				<input type="text" name="new_part_name" />
				<input type="submit" name="new_part" value="New Part" />
			</form>


			<br /><br />
			<p>See the *actual* project at <a href="http://mynameinklingon.org">mynameinklingon.org</a></p>

		</div>
		<?php

	}

	function add_item_to_part( $item_id, $part_id ) {
		global $wpdb;

		if ( !(int)$last_item = get_post_meta( $part_id, 'last_item', true ) )
			$last_item = 0;

		$last_item++;
		$post = get_post( $item_id );

		$args = array(
		  'menu_order' => $last_item,
		  'comment_status' => $post->comment_status,
		  'ping_status' => $post->ping_status,
		  'pinged' => $post->pinged,
		  'post_author' => $post->post_author,
		  'post_content' => $post->post_content,
		  'post_date' => $post->post_date,
		  'post_date_gmt' => $post->post_date_gmt,
		  'post_excerpt' => $post->post_excerpt,
		  'post_parent' => $part_id,
		  'post_password' => $post->post_password,
		  'post_status' => $post->post_status, // todo: yes?
		  'post_title' => $post->post_title,
		  'post_type' => 'library_items',
		  'to_ping' => $post->to_ping, // todo: tags and categories
		);

		$imported_item_id = wp_insert_post( $args );

		update_post_meta( $part_id, 'last_item', $last_item );

		/*if ( !$items = get_post_meta( $part_id, 'items', true ) )
			$items = array();

		$items[$item_id] = $imported_item_id;

		update_post_meta( $part_id, 'items', $items );*/
	}

	function add_new_part( $part_name ) {
		$args = array(
		  'post_title' => $part_name,
		  'post_type' => 'parts',
		  'post_status' => 'publish',
		  'post_parent' => $this->project_id
		);

		$part_id = wp_insert_post( $args );
	}

	function list_existing_parts() {

		//echo 'post_type=parts&order=ASC&post_parent=' . $this->project_id; die();

		query_posts( 'post_type=parts&order=ASC&post_parent=' . $this->project_id );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$part_id = get_the_ID();

				?>
					<div class="part" id="part-<?php echo $part_id ?>">
						<h3><?php the_title() ?></h3>

						<?php $this->get_part_items( $part_id ) ?>


						<form action="" method="post">
							<select name="item_id">
								<?php $this->get_posts_as_option_list( $part_id ) ?>
							</select>
							<input type="submit" name="new_item" value="Add Item" />
							<input type="hidden" name="part_id" value="<?php echo $part_id ?>" />
						</form>
					</div>



				<?php
			}
		} else {
			echo "no";
		}

		wp_reset_query();
	}

	function get_posts_as_option_list( $part_id ) {
		global $wpdb;

		$items = get_post_meta( $part_id, 'items', true );

		$item_query = new WP_Query( 'post_type=items&post_parent=' . $part_id );

		print_r($item_query->query());

		$sql = "SELECT id, post_title FROM wp_posts WHERE post_type = 'page' OR post_type = 'post' OR post_type = 'imported_items'";
		$ids = $wpdb->get_results($sql);

		$counter = 0;
		foreach( $ids as $id ) {
			if ( in_array( $id->id, $items ) || array_key_exists( $id->id, $items ) ) // Todo: adjust so that it references parent stuff
				continue;

			echo '<option value="' . $id->id . '">' . $id->post_title . '</option>';
			$counter++;
		}

		if ( !$counter )
			echo '<option disabled="disabled">Sorry, no content to add</option>';

	}


	function get_part_items( $part_id ) {
		$items = get_post_meta( $part_id, 'items', true );

		//echo "<pre>";
		//print_r($items); die();
		//if ( empty( $items ) )
		//	return;

		$args = array(
			'post_parent' => $part_id,
			'post_type' => 'library_items',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => ASC
		);

		$items_query = new WP_Query( $args );
		$items_query->query();

		if ( $items_query->have_posts() ) {

			echo "<ol>";

			while ( $items_query->have_posts() ) : $items_query->the_post();

				$this->display_item();

			endwhile;

			echo "</ol>";

		}

	}

	function move_up( $id ) {
		// Todo! With menu order

	}

	function move_down( $id ) {

	}

	function display_item() {
	?>
		<li>
			<a href="admin.php?page=anthologize/includes/class-project-organizer.php&project_id=1&move_up=<?php the_ID() ?>">&uarr;</a> <a href="admin.php?page=anthologize/includes/class-project-organizer.php&project_id=1&move_down=<?php the_ID() ?>">&darr;</a>
			<?php the_title() ?> - <a href="post.php?post=<?php the_ID() ?>&action=edit">Edit</a>
		</li>
	<?php
	}

}

endif;

$project_id = $_GET['project_id'];

$anthologize_project_organizer = new Anthologize_Project_Organizer( $project_id );
$anthologize_project_organizer->display();


?>