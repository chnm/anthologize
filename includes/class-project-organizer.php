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

		$this->boonetester();
	}

		function boonetester() {
		$project_id = 1027;
		$post_id = 859;
		$new_post = 1;
		$dest_id = 1059;
		$source_id = 0;
		$dest_seq = array(
			1064 => 2,
			1091 => 1
		);
		;
/*		$src_seq = array(
			1064 => 2,
			1091 => 1
		);
*/
		$this->insert_item( $project_id, $post_id, $new_post, $dest_id, $source_id, $dest_seq, $src_seq );
	}


	function load_scripts() {
	}


	function display() {

		if ( isset( $_POST['new_item'] ) )
			$this->add_item_to_part( $_POST['item_id'], $_POST['part_id'] );

		if ( isset( $_POST['new_part'] ) )
			$this->add_new_part( $_POST['new_part_name'] );

		if ( isset( $_GET['move_up'] ) )
			$this->move_up( $_GET['move_up'] );

		if ( isset( $_GET['move_down'] ) )
			$this->move_down( $_GET['move_down'] );

		if ( isset( $_GET['remove'] ) )
			$this->remove_item( $_GET['remove'] );
//print_r($_POST); die();


// You need to make sure that the append_children are actually in the form

		if ( isset( $_POST['append_children'] ) ) {
			$this->append_children( $_POST['append_parent'], $_POST['append_children'] );
		}
		?>
		<div class="wrap" id="project-<?php echo $_GET['project_id'] ?>">


		<div class="icon32" id="icon-anthologize"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/med-logo.png' ?>" /></div>

		<h2><?php echo $this->project_name ?></h2>

		<?php if ( isset( $_GET['append_parent'] ) && !isset( $_GET['append_children'] ) ) : ?>
			<div id="message" class="updated below-h2">
				<p><?php _e( 'Select the items you would like to append and click Go.', 'anthologize' ) ?></p>
			</div>
		<?php endif; ?>

		<div id="project-organizer-frame">
			<div id="project-organizer-left-column" class="metabox-holder">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">

				<div id="add-custom-links" class="postbox ">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Items', 'Anthologize' ) ?></span></h3>
				<div class="inside">
					<div class="customlinkdiv" id="customlinkdiv">


							<p id="menu-item-name-wrap">
								<?php $this->sortby_dropdown() ?>
							</p>

							<p id="menu-item-name-wrap">
								<?php $this->filter_dropdown_tags() ?>
							</p>


							<h3 class="part-header"><?php _e( 'Posts', 'anthologize' ) ?></h3>
							<div id="posts-scrollbox">

								<?php $this->get_sidebar_posts() ?>

							</div>

					</div><!-- /.customlinkdiv -->
					</div>
				</div> <!-- /.postbox -->

				</div> <!-- .meta-box-sortables -->
			</div> <!-- .project-organizer-left-column -->

			<div class="metabox-holder" id="project-organizer-right-column">

				<div class="postbox" id="anthologize-parts-box">

				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Parts', 'Anthologize' ) ?></span><div class="part-item-buttons button" id="new-part"><a href="post-new.php?post_type=parts">New Part</a></div></h3>
				<?php /* Todo: Add argument to new part button for redirect */ ?>

				<div id="partlist">

				<ul class="project-parts">
                                    <?php $this->list_existing_parts() ?>
                                </ul>

				<noscript>
					<h3>New Parts</h3>
					<p>Wanna create a new part? You know you do.</p>
					<form action="" method="post">
						<input type="text" name="new_part_name" />
						<input type="submit" name="new_part" value="New Part" />
					</form>
				</noscript>

				<!--
					<br /><br />
					<p>See the *actual* project at <a href="http://mynameinklingon.org">mynameinklingon.org</a>. You lucky duck.</p>
				-->

				</div>

				</div> <!-- #anthologize-part-box -->

			<div class="button" id="export-project-button"><a href="#" id="export-project"><?php _e( 'Export Project', 'anthologize' ) ?></a></div>

			</div> <!-- #project-organizer-right-column -->


		</div> <!-- #project-organizer-frame -->






		</div> <!-- .wrap -->
		<?php

	}

	function sortby_dropdown() {
		$filters = array( 'tag' => __( 'Tag', 'anthologize' ), 'category' => __( 'Category', 'anthologize' ) );

		?>
			<select name="sortby" id="sortby-dropdown">
				<option value=""><?php _e( 'Sort by', 'anthologize' ) ?></option>
				<?php foreach( $filters as $filter => $name ) : ?>
					<option value="<?php echo $filter ?>"><?php echo $name ?></option>
				<?php endforeach; ?>
			</select>
		<?php
	}

	function filter_dropdown_tags() {
		$tags = get_tags();

		?>
			<select name="filter" id="filter">
				<option value="" disabled="disabled"> - </option>
				<?php foreach( $tags as $tag ) : ?>
					<option value="<?php echo $tag->term_id ?>"><?php echo $tag->name ?></option>
				<?php endforeach; ?>
			</select>
		<?php
	}


	function filter_dropdown_cats() {
		$cats = get_categories();

		?>
			<select name="filter" id="filter">
				<option value="" disabled="disabled"> - </option>
				<?php foreach( $cats as $cat ) : ?>
					<option value="<?php echo $cat->term_id ?>"><?php echo $cat->name ?></option>
				<?php endforeach; ?>
			</select>
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

		if ( !$imported_item_id = wp_insert_post( $args ) )
			return false;

		// Author data
		$user = get_userdata( $post->post_author );
		$author_name = $user->display_name;
		$author_name_array = array( $author_name );

		update_post_meta( $imported_item_id, 'author_name', $author_name );
		update_post_meta( $imported_item_id, 'author_name_array', $author_name_array );

		return $imported_item_id;
	}

	function add_new_part( $part_name ) {
		if ( !(int)$last_item = get_post_meta( $this->project_id, 'last_item', true ) )
			$last_item = 0;

		$last_item++;

		$args = array(
		  'post_title' => $part_name,
		  'post_type' => 'parts',
		  'post_status' => 'publish',
		  'post_parent' => $this->project_id
		);

		if ( !$part_id = wp_insert_post( $args ) )
			return false;

		// Store the menu order of the last item to enable easy moving later on
		update_post_meta( $this->project, 'last_item', $last_item );

		return true;
	}

	function list_existing_parts() {

		query_posts( 'post_type=parts&order=ASC&orderby=menu_order&post_parent=' . $this->project_id );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$part_id = get_the_ID();

				?>

				<form action="" method="post">

				<?php

				?>
					<li class="part" id="part-<?php echo $part_id ?>">
						<h3 class="part-header"><noscript><a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_up=<?php echo $part_id ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_down=<?php echo $part_id ?>">&darr;</a> </noscript><?php the_title() ?> <small><a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&remove=<?php the_ID() ?>" class="remove"><?php _e( 'Remove', 'anthologize' ) ?></a></small></h3>

						<div class="part-items">
                                                    <ul>
							<?php $this->get_part_items( $part_id ) ?>
                                                    </ul>

						</div>

						<?php /* Noscript solution. Removed at the moment to avoid db queries. Todo: refactor ?>
							<?php if ( isset( $_GET['append_parent'] ) && !isset( $_GET['append_children'] ) ) : ?>

								<input type="submit" name="append_submit" value="Go" />
								<input type="hidden" name="append_parent" value="<?php echo $_GET['append_parent']  ?>" />

							<?php else : ?>

								<select name="item_id">
									<?php $this->get_posts_as_option_list( $part_id ) ?>
								</select>
								<input type="submit" name="new_item" value="Add Item" />
								<input type="hidden" name="part_id" value="<?php echo $part_id ?>" />

							<?php endif; ?>

						<?php */ ?>

					</li>


				</form>
				<?php
			}
		} else {

		}



		wp_reset_query();
	}

	function get_sidebar_posts() {
		global $wpdb;

		$args = array(
			'post_type' => array('post', 'page', 'imported_items' ),
			'posts_per_page' => -1
		);

		$big_posts = new WP_Query( $args );

		if ( $big_posts->have_posts() ) {
		?>
			<ul id="sidebar-posts">
				<?php while ( $big_posts->have_posts() ) : $big_posts->the_post(); ?>
					<li class="item" id="new-<?php the_ID() ?>"><h3 class="part-item"><?php the_title() ?></h3></li>
				<?php endwhile; ?>
			</ul>
		<?php
		}
	}

	function get_posts_as_option_list( $part_id ) {
		global $wpdb;

		$items = get_post_meta( $part_id, 'items', true );

		$item_query = new WP_Query( 'post_type=items&post_parent=' . $part_id );

//		print_r($item_query->query());

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

		if ( isset( $_GET['append_parent'] ) )
			$append_parent = $_GET['append_parent'];

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

		if ( $items_query->have_posts() ) {

			while ( $items_query->have_posts() ) : $items_query->the_post();

				$this->display_item( $append_parent );

			endwhile;

		}
	}

	function move_up( $id ) {
		global $wpdb;

		$post = get_post( $id );
		$my_menu_order = $post->menu_order;

		$little_brother = 0;
		$minus = 0;

		while ( !$big_brother ) {
			$minus++;

			// Find the big brother
			$big_brother_q = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND menu_order = %d LIMIT 1", $post->post_parent, $my_menu_order-$minus );

			$bb = $wpdb->get_results( $big_brother_q, ARRAY_N );
			$big_brother = $bb[0][0];
		}

		// Downgrade the big brother
		$big_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order, $big_brother ) );

		// Upgrade self
		$little_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order-$minus, $id ) );

		return true;
	}

	function move_down( $id ) {
		global $wpdb;

		$post = get_post( $id );
		$my_menu_order = $post->menu_order;

		$little_brother = 0;
		$plus = 0;

		while ( !$little_brother ) {
			$plus++;

			// Find the little brother
			$little_brother_q = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND menu_order = %d LIMIT 1", $post->post_parent, $my_menu_order+$plus );

			$lb = $wpdb->get_results( $little_brother_q, ARRAY_N );
			$little_brother = $lb[0][0];
		}

		// Upgrade the little brother
		$little_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order, $little_brother ) );

		// Downgrade self
		$big_brother_q = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $my_menu_order+$plus, $id ) );

		return true;
	}



	function insert_item( $project_id, $post_id, $new_post, $dest_id, $source_id, $dest_seq, $src_seq ) {
		global $wpdb;

		if ( !isset( $project_id ) || !isset( $post_id ) || !isset( $dest_id ) || !isset( $dest_seq ) )
			return false;

		if ( !$new_post ) {
			if ( !isset( $source_id ) || !isset( $source_seq ) )
				return false;
		}

		/* $dest_seq, $src_seq:
			array(
				$item_id => $seq_no
			);
		*/

		if ( $new_post ) {
            $add_item_result = $this->add_item_to_part( $post_id, $dest_id ); 
			if (false === $add_item_result)
				return false;
            $post_id = $add_item_result;
        } else {
            // use wp_update_post
            // ID, post_parent
            $post_params = Array('ID' => $post_id,
                                 'post_parent' => $dest_id);
            $update_item_result = wp_update_post($post_params);
			if (false === $update_item_result)
				return false;
            $post_id = $update_item_result;
        }

        // JMC: not really any point in checking for errors at this point
        // Since the insert succeeded
        // We should use more detailed Exceptions eventually
        //
		// All items require the destination siblings to be reordered
/*		if ( !$this->rearrange_items( $dest_seq ) )
    return false;*/
        $this->rearrange_items( $dest_seq );


		// You only need to rearrange the source when moving between parts
        /*if ( !$new_post ) {
			if ( !$this->rearrange_items( $src_seq ) )
				return false;
        }*/

        $this->rearrange_items( $src_seq ); 

		return $post_id;

	}

	function rearrange_items( $seq ) {
		foreach ( $seq as $item_id => $pos ) {
			$q = "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d";
			$post_up_query = $wpdb->query( $wpdb->prepare( $q, $pos, $item_id ) );
		}

		return true;
	}

	function remove_item( $id ) {
		// Git ridda the post
		if ( !wp_delete_post( $id ) )
			return false;

		return true;
	}

	function append_children( $append_parent, $append_children ) {

		$parent_post = get_post( $append_parent );
		$pp_content = $parent_post->post_content;

		if ( !$author_name = get_post_meta( $append_parent, 'author_name', true ) )
			$author_name = '';

		if ( !$author_name_array = get_post_meta( $append_parent, 'author_name_array', true ) )
			$author_name_array = array();

		foreach( $append_children as $append_child ) {
			$child_post = get_post( $append_child );

			$cp_title = '<h2 class="anthologize-item-header">' . $child_post->post_title . '</h2>
			';

			$cp_content = $child_post->post_content;

			$pp_content .= $cp_title . $cp_content . '
			';

			if ( $author_name != '' )
				$author_name .= ', ';

			$cp_author_name = get_post_meta( $append_child, 'author_name', true );
			$author_name .= $cp_author_name;
			$author_name_array[] = $cp_author_name;

			wp_delete_post( $append_child );
		}

		$args = array(
			'ID' => $append_parent,
			'post_content' => $pp_content,
		);

		if ( !wp_update_post( $args ) )
			return false;

		update_post_meta( $append_parent, 'author_name', $author_name );
		update_post_meta( $append_parent, 'author_name_array', $author_name_array );

		return true;
		// todo Jeremy: make sure that the form action goes to the right place after an append
	}

	function display_item( $append_parent ) {
		global $post;

	?>

		<li id="item-<?php the_ID() ?>" class="item">

			<?php if ( $append_parent ) : ?>
				<input type="checkbox" name="append_children[]" value="<?php the_ID() ?>" <?php if ( $append_parent == $post->ID ) echo 'checked="checked" disabled=disabled'; ?>/> <?php echo $post->ID . " " . $append_parent ?>
			<?php endif; ?>

			<noscript>
				<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_up=<?php the_ID() ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_down=<?php the_ID() ?>">&darr;</a>
			</noscript>

			<h3 class="part-item">
				<span class="part-title"><?php the_title() ?></span>
				<div class="part-item-buttons">
					<a href="post.php?post=<?php the_ID() ?>&action=edit"><?php _e( 'Edit', 'anthologize' ) ?></a> |
					<a href="#" class="append">Ajax Append</a> |
					<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&append_parent=<?php the_ID() ?>"><?php _e( 'Append', 'anthologize' ) ?></a> |
					<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&remove=<?php the_ID() ?>" class="confirm"><?php _e( 'Remove', 'anthologize' ) ?></a>
				</div>
			</h3>
		</li>
	<?php
	}

}

endif;

?>
