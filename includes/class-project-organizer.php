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

		if ( isset( $_POST['append_children'] ) ) {
			$this->append_children( $_POST['append_parent'], $_POST['append_children'] );
		}

		?>
		<div class="wrap anthologize" id="project-<?php echo $_GET['project_id'] ?>">

        <div id="blockUISpinner">
            <img src="<?php echo WP_PLUGIN_URL ?>/anthologize/images/wait28.gif"</img>
            <p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
        </div>

		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>

		<h2><?php echo $this->project_name ?>

		<div id="project-actions">
			<a href="admin.php?page=anthologize/includes/class-new-project.php&project_id=<?php echo $this->project_id ?>"><?php _e( 'Project Details', 'anthologize' ) ?></a> |
			<a href="admin.php?page=anthologize&action=delete&project_id=<?php echo $this->project_id ?>" class="confirm-delete"><?php _e( 'Delete Project', 'anthologize' ) ?></a>
		</div>

		</h2>

		<?php if ( isset( $_GET['append_parent'] ) && !isset( $_GET['append_children'] ) ) : ?>
			<div id="message" class="updated below-h2">
				<p><?php _e( 'Select the items you would like to append and click Go.', 'anthologize' ) ?></p>
			</div>
		<?php endif; ?>

		<div id="project-organizer-frame">
			<div id="project-organizer-left-column" class="metabox-holder">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">

				<div id="add-custom-links" class="postbox ">
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'anthologize' ) ?>"><br></div><h3 class="hndle"><span><?php _e( 'Items', 'anthologize' ) ?></span></h3>
				<div class="inside">
					<div class="customlinkdiv" id="customlinkdiv">

							<p id="menu-item-name-wrap">
								<?php $this->sortby_dropdown() ?>
							</p>

							<p id="termfilter">
								<?php $this->filter_dropdown() ?>
							</p>
							<p id="datefilter">
								<?php $this->filter_date(); ?>
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

				<div class="handlediv" title="<?php _e( 'Click to toggle', 'anthologize' ) ?>"><br></div><h3 class="hndle"><span><?php _e( 'Parts', 'anthologize' ) ?></span><div class="part-item-buttons button" id="new-part"><a href="post-new.php?post_type=anth_part&project_id=<?php echo $this->project_id ?>&new_part=1"><?php _e( 'New Part', 'anthologize' ) ?></a></div></h3>

				<div id="partlist">

				<ul class="project-parts">
                	<?php $this->list_existing_parts() ?>
                </ul>

				<noscript>
					<h3><?php _e( 'New Parts', 'anthologize' ) ?></h3>
					<p><?php _e( 'Wanna create a new part? You know you do.', 'anthologize' ) ?></p>
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

			<div class="button" id="export-project-button"><a href="admin.php?page=anthologize/includes/class-export-panel.php&project_id=<?php echo $this->project_id ?>" id="export-project"><?php _e( 'Export Project', 'anthologize' ) ?></a></div>

			</div> <!-- #project-organizer-right-column -->


		</div> <!-- #project-organizer-frame -->

		</div> <!-- .wrap -->
		<?php

	}

	function sortby_dropdown() {
		$filters = array( 
			'tag' => __( 'Tag', 'anthologize' ), 
			'category' => __( 'Category', 'anthologize' ),
			'date' => __( 'Date Range', 'anthologize' ),
			'post_type' => __( 'Post Type', 'anthologize' )
		);
		if ( isset( $_COOKIE['anth-filter'] ) )
			$cfilter = $_COOKIE['anth-filter'];
		?>
            <span><?php _e( 'Filter by', 'anthologize' ) ?></span>
			<select name="sortby" id="sortby-dropdown">
				<option value="" selected="selected"><?php _e( 'All posts', 'anthologize' ) ?></option>
				<?php foreach( $filters as $filter => $name ) : ?>
					<option value="<?php echo $filter ?>" <?php if ( $filter == $cfilter ) : ?>selected="selected"<?php endif; ?>><?php echo $name ?></option>
				<?php endforeach; ?>
			</select>
		<?php
	}

	function filter_dropdown() {

		$cterm = ( isset( $_COOKIE['anth-term'] ) ) ? $_COOKIE['anth-term'] : false;
		
		$cfilter = ( isset( $_COOKIE['anth-filter'] ) ) ? $_COOKIE['anth-filter'] : false;
		
		$cstartdate = ( isset( $_COOKIE['anth-startdate'] ) ) ? $_COOKIE['anth-startdate'] : false;
				
		$cenddate = ( isset( $_COOKIE['anth-enddate'] ) ) ? $_COOKIE['anth-enddate'] : false;		
	
		switch ( $cfilter ) {
			case 'tag' :
				$terms = get_tags();
				$nulltext = __( 'All tags', 'anthologize' );
				break;
			case 'category' :
				$terms = get_categories();
				$nulltext = __( 'All categories', 'anthologize' );
				break;
			case 'post_type' :
				$types = $this->available_post_types();
				$terms = array();
				foreach ( $types as $type_id => $type_label ) {
					$type_object = null;
					$type_object->term_id = $type_id;
					$type_object->name = $type_label;
					$terms[] = $type_object;
				}
				$nulltext = __( 'All post types', 'anthologize' );
				break;
			default :
				$terms = Array();
				$nulltext = ' - ';
				break;
		}

		?>
			
			<select name="filter" id="filter">
				<option value=""><?php echo $nulltext; ?></option>
				<?php foreach( $terms as $term ) : ?>
					<?php $term_value = ( $_COOKIE['anth-filter'] == 'tag' ) ? $term->slug : $term->term_id; ?>
					<option value="<?php echo $term_value ?>" <?php if ( $cterm == $term_value ) : ?>selected="selected"<?php endif; ?>><?php echo $term->name ?></option>
				<?php endforeach; ?>
			</select>
			
		<?php
	}

	function filter_date(){
		?>
		
		<label for="startdate">Start</label> <input name="starddate" id="startdate" type="text"/>
		<br />
		<label for="enddate">End</label> <input name="enddate" id="enddate" type="text" />
		<br />
		<input type="button" id="launch_date_filter" value="Filter" /> 
		<?php
	}
	
	// A filterable list of post types that can
	// serve as a filter for the project organizer
	function available_post_types() {
		$types = array(
			'post' => __( 'Posts' ),
			'page' => __( 'Pages' ),
			'anth_imported_item' => __( 'Imported Items', 'anthologize' )
		);
		
		return apply_filters( 'anth_available_post_types', $types );		
	}

	function add_item_to_part( $item_id, $part_id ) {
		global $wpdb, $current_user;

		if ( !(int)$last_item = get_post_meta( $part_id, 'last_item', true ) )
			$last_item = 0;

		$last_item++;
		$the_item = get_post( $item_id );
		$part = get_post( $part_id );

		$args = array(
		  'menu_order' => $last_item,
		  'comment_status' => $the_item->comment_status,
		  'ping_status' => $the_item->ping_status,
		  'pinged' => $the_item->pinged,
		  'post_author' => $current_user->ID,
		  'post_content' => $the_item->post_content,
		  'post_date' => $the_item->post_date,
		  'post_date_gmt' => $the_item->post_date_gmt,
		  'post_excerpt' => $the_item->post_excerpt,
		  'post_parent' => $part_id,
		  'post_password' => $the_item->post_password,
		  'post_status' => $part->post_status, // post_status is set to the post_status of the parent part
		  'post_title' => $the_item->post_title,
		  'post_type' => 'anth_library_item',
		  'to_ping' => $the_item->to_ping, // todo: tags and categories
		);

		if ( !$imported_item_id = wp_insert_post( $args ) )
			return false;
		
		// Update the parent project's Date Modified field to right now
		$this->update_project_modified_date();

		// Author data
		$user = get_userdata( $the_item->post_author );

		if ( !$author_name = get_post_meta( $item_id, 'author_name', true ) )
			$author_name = $user->display_name;
		$author_name_array = array( $author_name );

		$anthologize_meta = apply_filters( 'anth_add_item_postmeta', array(
			'author_name' => $author_name,
			'author_name_array' => $author_name_array,
			'author_id' => $the_item->post_author,
			'original_post_id' => $item_id
		) );
		
		update_post_meta( $imported_item_id, 'anthologize_meta', $anthologize_meta );
		
		update_post_meta( $imported_item_id, 'author_name', $author_name ); // Deprecated - please use anthologize_meta
		update_post_meta( $imported_item_id, 'author_name_array', $author_name_array ); // Deprecated - please use anthologize_meta

		return $imported_item_id;
	}
	
	function update_project_modified_date() {
		$project_post = get_post( $this->project_id );
		$project_args = array(
			'ID' => $this->project_id,
            'post_modified' => date( "Y-m-d G:H:i" ),
            'post_modified_gmt' => gmdate( "Y-m-d G:H:i" )
		);
		wp_update_post( $project_args );
	}

	function add_new_part( $part_name ) {
		if ( !(int)$last_item = get_post_meta( $this->project_id, 'last_item', true ) )
			$last_item = 0;

		$last_item++;

		$project = get_post( $this->project_id );

		$args = array(
		  'post_title' => $part_name,
		  'post_type' => 'anth_part',
		  'post_status' => $project->post_status,
		  'post_parent' => $this->project_id
		);

		if ( !$part_id = wp_insert_post( $args ) )
			return false;

		// Store the menu order of the last item to enable easy moving later on
		update_post_meta( $this->project, 'last_item', $last_item );

		$this->update_project_modified_date();

		return true;
	}

	function list_existing_parts() {

		$args = array(
			'post_type' => 'anth_part',
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'post_per_page' => -1,
			'showposts' => -1,
			'post_parent' => $this->project_id
		);

		query_posts( $args );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$part_id = get_the_ID();

				?>

				<!--// <form action="" method="post"> //-->

				<?php

				?>
					<li class="part" id="part-<?php echo $part_id ?>">
						<h3 class="part-header"><noscript><a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_up=<?php echo $part_id ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&move_down=<?php echo $part_id ?>">&darr;</a> </noscript>
						<span class="part-title-header"><?php the_title() ?></span>

						<div class="part-buttons">
							<a href="post.php?post=<?php the_ID() ?>&action=edit&return_to_project=<?php echo $this->project_id ?>"><?php _e( 'Edit', 'anthologize' ) ?></a> |
							<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&remove=<?php the_ID() ?>" class="remove"><?php _e( 'Remove', 'anthologize' ) ?></a> |
							<a href="#collapse" class="collapsepart"> - </a> 
						</div>

						</h3>

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


				<!--// </form> //-->
				<?php
			}
		} else {
		?>
			<p><?php echo sprintf( __( 'You haven\'t created any parts yet! Click <a href="%1$s">"New Part"</a> to get started.', 'anthologize' ), 'post-new.php?post_type=anth_part&project_id=' . $this->project_id . '&new_part=1' ) ?></p>
		<?php
		}



		wp_reset_query();
	}

	function get_sidebar_posts() {
		global $wpdb;

		$args = array(
			'post_type' => array('post', 'page', 'anth_imported_item' ),
			'posts_per_page' => -1,
			'orderby' => 'post_title',
			'order' => 'DESC'
		);
				
		$cfilter = ( isset( $_COOKIE['anth-filter'] ) ) ? $_COOKIE['anth-filter'] : false;
		
		if ( $cfilter == 'date' ) {
			$startdate = mysql_real_escape_string($_COOKIE['anth-startdate']);
			$enddate = mysql_real_escape_string($_COOKIE['anth-enddate']);				
							
			$date_range_where = '';
			if (strlen($startdate) > 0){
			$date_range_where = " AND post_date >= '".$startdate."'";
			}
			if (strlen($enddate) > 0){
			$date_range_where .= " AND post_date <= '".$enddate."'";
			}

			$where_func = '$where .= "'.$date_range_where.'"; return $where;'; 
			$filter_where = create_function('$where', $where_func);
			add_filter('posts_where', $filter_where);
		} else {
		
			$cterm = ( isset( $_COOKIE['anth-term'] ) ) ? $_COOKIE['anth-term'] : false;
					
			if ( $cterm ) {
				if ( $cfilter ) {
					switch( $cfilter ) {
						case 'tag' :
							$filtertype = 'tag';
							break;
						case 'category' :
							$filtertype = 'cat';
							break;
						case 'post_type' :
							$filtertype = 'post_type';
							break;
					}
					
					$args[$filtertype] = $cterm;
				}
			}

		}

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

		$sql = "SELECT id, post_title FROM wp_posts WHERE post_type = 'page' OR post_type = 'post' OR post_type = 'anth_imported_item'";
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
			'post_type' => 'anth_library_item',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC'
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



	function insert_item( $project_id, $post_id, $new_post, $dest_id, $source_id, $dest_seq, $source_seq ) {
		global $wpdb;
		if ( !isset( $project_id ) || !isset( $post_id ) || !isset( $dest_id ) || !isset( $dest_seq ) )
			return false;

		if ( !$new_post ) {
			if ( !isset( $source_id ) || !isset( $source_seq ) )
				return false;
		}

		if ( true === $new_post ) {
			$add_item_result = $this->add_item_to_part( $post_id, $dest_id );
			if (false === $add_item_result) {
				return false;
			}
			$post_id = $add_item_result;
      // $dest_seq[$post_id] = $dest_seq['new_new_new'];
      // unset($dest_seq['new_new_new']);
		} else {
			$post_params = Array('ID' => $post_id,
				'post_parent' => $dest_id);
			$update_item_result = wp_update_post($post_params);
			if (0 === $update_item_result) {
				return false;
			}
			$post_id = $update_item_result;
			$this->rearrange_items( $source_seq );
		}

        // not really any point in checking for errors at this point
        // Since the insert succeeded
        // We should use more detailed Exceptions eventually
        //
		// All items require the destination siblings to be reordered
/*		if ( !$this->rearrange_items( $dest_seq ) )
    return false;*/
		//$this->rearrange_items( $dest_seq );

		return $post_id;
	}

	function rearrange_items( $seq ) {
        global $wpdb;
		foreach ( $seq as $item_id => $pos ) {
			$q = "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d";
			$post_up_query = $wpdb->query( $wpdb->prepare( $q, $pos, $item_id ) );
		}
		
		$this->update_project_modified_date();

		return true;
	}

	function remove_item( $id ) {
		// Git ridda the post
		if ( !wp_delete_post( $id ) )
			return false;
		
		$this->update_project_modified_date();

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

		$this->update_project_modified_date();

		return true;
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

					<a href="#append" class="append"><?php _e( 'Append', 'anthologize' ) ?></a><span class="append-sep"> |</span>
					<?
					// admin.php?page=anthologize&action=edit&project_id=$this->project_id&append_parent= the_ID()
					?>
					<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo $this->project_id ?>&remove=<?php the_ID() ?>" class="confirm"><?php _e( 'Remove', 'anthologize' ) ?></a>
				</div>
			</h3>
		</li>
	<?php
	}

}

endif;

?>
