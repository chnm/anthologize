<?php



if ( !class_exists( 'Booyakasha_Book_Organizer' ) ) :

class Booyakasha_Book_Organizer {

	var $book_id;

	/**
	 * The book organizer. Git 'er done
	 */
	function booyakasha_book_organizer ( $book_id ) {

		$this->book_id = $book_id;

		$book = get_post( $book_id );

		$this->book_name = $book->post_title;

		add_action( 'admin_init', array ( $this, 'init' ) );

		//add_action( 'admin_menu', array( $this, 'dashboard_hooks' ) );

	}

	function init() {
		do_action( 'booyakasha_admin_init' );
	}

	function display() {

		if ( isset( $_POST['new_item'] ) )
			$this->add_item_to_part( $_POST['item_id'], $_POST['part_id'] );

		if ( isset( $_POST['new_part'] ) )
			$this->add_new_part( $_POST['new_part_name'] );

		// todo: make sure you're only pulling up the chapters from a specific book

		?>
		<div class="wrap">
			<h2>You're lucky this page is not in Klingon</h2>

			<h2><?php echo $this->book_name ?></h2>

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
		$post = get_post( $item_id );

		$args = array(
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
		  'post_parent' => $this->book_id
		);

		$part_id = wp_insert_post( $args );
	}

	function list_existing_parts() {

		//echo 'post_type=parts&order=ASC&post_parent=' . $this->book_id; die();

		query_posts( 'post_type=parts&order=ASC&post_parent=' . $this->book_id );

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
		);

		$items_query = new WP_Query( $args );
		$items_query->query();

		if ( $items_query->have_posts() ) {

			$return = "<ol>";

			while ( $items_query->have_posts() ) : $items_query->the_post();

			//foreach( $items as $item ) {
				$return .= "<li>";
				$return .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
				$return .= "</li>";
			//}

			endwhile;

			$return .= "</ol>";

		}

		echo $return;


	}



}

endif;

$book_id = $_GET['book_id'];

$booyakasha_book_organizer = new Booyakasha_Book_Organizer( $book_id );
$booyakasha_book_organizer->display();

//$booyakasha_book_organizer = new Booyakasha_Book_Organizer( 1 );







?>