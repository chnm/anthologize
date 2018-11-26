<?php

if ( ! class_exists( 'Anthologize_Project_Organizer' ) ) :

class Anthologize_Project_Organizer {

	var $project_id;

	/**
	 * The project organizer. Git 'er done
	 */
	function __construct( $project_id ) {
		$this->project_id = $project_id;

		$project = get_post( $project_id );

		if ( ! empty( $project->post_title ) ) {
			$this->project_name = $project->post_title;
		}
	}

	/**
	 * @todo Do this noscript logic and other $_REQUEST parsing earlier
	 */
	function display() {
		wp_enqueue_script( 'anthologize-sortlist-js' );
		wp_enqueue_script( 'anthologize-project-organizer' );

		if ( isset( $_POST['new_item'] ) ) {
			$this->add_item_to_part( $_POST['item_id'], $_POST['part_id'] );
		}

		if ( isset( $_POST['new_part'] ) ) {
			$this->add_new_part( $_POST['new_part_name'] );
		}

		if ( isset( $_GET['move_up'] ) ) {
			$this->move_up( $_GET['move_up'] );
		}

		if ( isset( $_GET['move_down'] ) ) {
			$this->move_down( $_GET['move_down'] );
		}

		if ( isset( $_GET['remove'] ) ) {
			$this->remove_item( $_GET['remove'] );
		}

		if ( isset( $_POST['append_children'] ) ) {
			$this->append_children( $_POST['append_parent'], $_POST['append_children'] );
		}

		?>

		<div class="wrap anthologize" id="project-<?php echo esc_attr( $_GET['project_id'] ) ?>">

			<div id="blockUISpinner">
				<img src="<?php echo plugins_url() ?>/anthologize/images/wait28.gif" alt="<?php esc_html_e( 'Please wait...', 'anthologize' ); ?>" aria-hidden="true" />
				<p id="ajaxErrorMsg"><?php _e('There has been an unexpected error. Please wait while we reload the content.', 'anthologize') ?></p>
			</div>

			<div id="anthologize-logo"><img src="<?php echo esc_url( plugins_url() . '/anthologize/images/anthologize-logo.gif' ) ?>" alt="<?php esc_attr_e( 'Anthologize logo', 'anthologize' ); ?>" /></div>

			<h2>
				<?php echo esc_html( $this->project_name ) ?>

				<div id="project-actions">
					<a href="admin.php?page=anthologize_new_project&project_id=<?php echo esc_attr( $this->project_id ) ?>"><?php _e( 'Project Details', 'anthologize' ) ?></a> |
					<a target="_blank" href="<?php echo esc_url( $this->preview_url( $this->project_id, 'anth_project' ) ) ?>"><?php _e( 'Preview Project', 'anthologize' ) ?></a> |
					<a href="admin.php?page=anthologize&action=delete&project_id=<?php echo esc_attr( $this->project_id ) ?>" class="confirm-delete"><?php _e( 'Delete Project', 'anthologize' ) ?></a>
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
							<div class="handlediv" title="<?php _e( 'Click to toggle', 'anthologize' ) ?>"><br></div>
							<h3 class="hndle"><span><?php _e( 'Items', 'anthologize' ) ?></span></h3>

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
							</div><!-- /.inside -->

						</div> <!-- /.postbox -->

					</div> <!-- .meta-box-sortables -->
				</div> <!-- .project-organizer-left-column -->

				<div class="metabox-holder" id="project-organizer-right-column">

					<div class="postbox" id="anthologize-parts-box">

						<div class="handlediv" title="<?php _e( 'Click to toggle', 'anthologize' ) ?>"><br></div>
						<h3 class="hndle">
							<span><?php _e( 'Parts', 'anthologize' ) ?></span>
							<div class="part-item-buttons button anth-buttons" id="new-part">
								<a href="post-new.php?post_type=anth_part&project_id=<?php echo esc_attr( $this->project_id ) ?>&new_part=1"><?php _e( 'New Part', 'anthologize' ) ?></a>
							</div>
						</h3>

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

					</div> <!-- #anthologize-parts-box -->

					<div class="button" id="export-project-button"><a href="admin.php?page=anthologize_export_project&project_id=<?php echo esc_attr( $this->project_id ) ?>" id="export-project"><?php _e( 'Export Project', 'anthologize' ) ?></a></div>

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

		$cfilter = isset( $_COOKIE['anth-filter'] ) ? $_COOKIE['anth-filter'] : '';

		?>

		<label for="sortby-dropdown"><?php _e( 'Filter by', 'anthologize' ) ?></label>

		<select name="sortby" id="sortby-dropdown">
			<option value="" selected="selected"><?php _e( 'All posts', 'anthologize' ) ?></option>
			<?php foreach( $filters as $filter => $name ) : ?>
				<option value="<?php echo esc_attr( $filter ) ?>" <?php if ( $filter == $cfilter ) : ?>selected="selected"<?php endif; ?>><?php echo esc_html( $name ) ?></option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	function filter_dropdown() {

		$cterm      = ( isset( $_COOKIE['anth-term'] ) ) ? $_COOKIE['anth-term'] : false;
		$cfilter    = ( isset( $_COOKIE['anth-filter'] ) ) ? $_COOKIE['anth-filter'] : false;
		$cstartdate = ( isset( $_COOKIE['anth-startdate'] ) ) ? $_COOKIE['anth-startdate'] : false;
		$cenddate   = ( isset( $_COOKIE['anth-enddate'] ) ) ? $_COOKIE['anth-enddate'] : false;

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
					$type_object = new stdClass;
					$type_object->term_id = $type_id;
					$type_object->name = $type_label;
					$terms[] = $type_object;
				}
				$nulltext = __( 'All post types', 'anthologize' );
				break;

			default :
				$terms = array();
				$nulltext = ' - ';
				break;
		}

		?>

		<label class="screen-reader-text" for="filter"><?php esc_html_e( 'Filter by specific term', 'anthologize' ); ?></label>

		<select name="filter" id="filter">
			<option value=""><?php echo esc_html( $nulltext ); ?></option>
			<?php foreach( $terms as $term ) : ?>
				<?php $term_value = ( $_COOKIE['anth-filter'] == 'tag' ) ? esc_attr( $term->slug ) : esc_attr( $term->term_id ); ?>
				<option value="<?php echo esc_attr( $term_value ) ?>" <?php if ( $cterm == $term_value ) : ?>selected="selected"<?php endif; ?>><?php echo esc_html( $term->name ) ?></option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	function filter_date() {
		?>

		<label for="startdate">Start</label> <input name="starddate" id="startdate" type="text"/>
		<br />
		<label for="enddate">End</label> <input name="enddate" id="enddate" type="text" />
		<br />
		<input type="button" id="launch_date_filter" value="Filter" />

		<?php
	}

	/**
	 * Provide a list of post types available as a filter on the project organizer screen.
	 *
	 * @package Anthologize
	 * @subpackage Project Organizer
	 * @since 0.5
	 *
	 * @return array A list of post type labels, keyed by name
	 */
	function available_post_types() {
		$all_post_types = get_post_types( array(
			'public' => true
		), false );

		$excluded_post_types = apply_filters( 'anth_excluded_post_types', array(
			'anth_library_item',
			'anth_part',
			'anth_project',
			'attachment',
			'revision',
			'nav_menu_item'
		) );

		$types = array();
		foreach ( $all_post_types as $name => $post_type ) {
			if ( ! in_array( $name, $excluded_post_types ) ) {
				$types[ $name ] = isset( $post_type->labels->name ) ? $post_type->labels->name : $name;
			}
		}

		return apply_filters( 'anth_available_post_types', $types );
	}

	function add_item_to_part( $item_id, $part_id ) {
		global $wpdb, $current_user;

		if ( ! (int) $last_item = get_post_meta( $part_id, 'last_item', true ) ) {
			$last_item = 0;
		}

		$last_item++;
		$the_item = get_post( $item_id );
		$part = get_post( $part_id );

		$args = array(
			'menu_order'     => $last_item,
			'comment_status' => $the_item->comment_status,
			'ping_status'    => $the_item->ping_status,
			'pinged'         => $the_item->pinged,
			'post_author'    => $current_user->ID,
			'post_content'   => $the_item->post_content,
			'post_date'      => $the_item->post_date,
			'post_date_gmt'  => $the_item->post_date_gmt,
			'post_excerpt'   => $the_item->post_excerpt,
			'post_parent'    => $part_id,
			'post_password'  => $the_item->post_password,
			'post_status'    => $part->post_status, // post_status is set to the post_status of the parent part
			'post_title'     => $the_item->post_title,
			'post_type'      => 'anth_library_item',
			'to_ping'        => $the_item->to_ping, // todo: tags and categories
		);

		// WordPress will strip these slashes off in wp_insert_post
		$args = add_magic_quotes($args);

		if ( ! $imported_item_id = wp_insert_post( $args ) ) {
			return false;
		}

		// Update the parent project's Date Modified field to right now
		$this->update_project_modified_date();

		// Author data
		$user = get_userdata( $the_item->post_author );

		if ( ! $author_name = get_post_meta( $item_id, 'author_name', true ) && $user ) {
			$author_name = $user->display_name;
		}

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
			'ID'                => $this->project_id,
			'post_modified'     => date( "Y-m-d G:H:i" ),
			'post_modified_gmt' => gmdate( "Y-m-d G:H:i" )
		);
		wp_update_post( $project_args );
	    }

	function add_new_part( $part_name ) {
		if ( ! (int) $last_item = get_post_meta( $this->project_id, 'last_item', true ) ) {
			$last_item = 0;
		}

		$last_item++;

		$project = get_post( $this->project_id );

		$args = array(
			'post_title'  => $part_name,
			'post_type'   => 'anth_part',
			'post_status' => $project->post_status,
			'post_parent' => $this->project_id
		);

		if ( ! $part_id = wp_insert_post( $args ) ) {
			return false;
		}

		// Store the menu order of the last item to enable easy moving later on
		update_post_meta( $this->project, 'last_item', $last_item );

		$this->update_project_modified_date();

		return true;
	}

	function list_existing_parts() {

		$args = array(
			'post_type'     => 'anth_part',
			'order'         => 'ASC',
			'orderby'       => 'menu_order',
			'post_per_page' => -1,
			'showposts'     => -1,
			'post_parent'   => $this->project_id
		);

		// @todo - no
		query_posts( $args );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$part_id = get_the_ID();

				?>

				<li class="part" id="part-<?php echo esc_html( $part_id ) ?>">
					<div class="part-header">
						<h3 class="part-title-header">
							<noscript><a href="admin.php?page=anthologize&action=edit&project_id=<?php echo esc_attr( $this->project_id ) ?>&move_up=<?php echo esc_attr( $part_id ) ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo esc_attr( $this->project_id ) ?>&move_down=<?php echo esc_attr( $part_id ) ?>">&darr;</a> </noscript>
							<span class="part-title-header"><?php the_title() ?></span>
						</h3>

						<div class="part-buttons anth-buttons">
							<a href="post.php?post=<?php the_ID() ?>&action=edit&return_to_project=<?php echo esc_attr( $this->project_id ) ?>"><?php _e( 'Edit', 'anthologize' ) ?></a> |
							<a target="_blank" href="<?php echo esc_url( $this->preview_url( get_the_ID(), 'anth_part' ) ) ?>" class=""><?php _e( 'Preview', 'anthologize' ) ?></a> |
							<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo esc_attr( $this->project_id ) ?>&remove=<?php the_ID() ?>" class="remove"><?php _e( 'Remove', 'anthologize' ) ?></a> |
							<a href="#collapse" class="collapsepart"> - </a>
						</div>
					</div>

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
				<?php
			} // while ( have_posts() )

		} else {

			?>

			<p><?php echo sprintf( __( 'You haven\'t created any parts yet! Click <a href="%1$s">"New Part"</a> to get started.', 'anthologize' ), esc_url( admin_url( 'post-new.php?post_type=anth_part&project_id=' . $this->project_id . '&new_part=1' ) ) ) ?></p>

			<?php
		} // if ( have_posts() )

		wp_reset_query();
	}

	function get_sidebar_posts() {
		global $wpdb;

		$args = array(
			'post_type' => array('post', 'page', 'anth_imported_item' ),
			'posts_per_page' => -1,
			'orderby' => 'post_title',
			'order' => 'DESC',
			'post_status' => $this->source_item_post_statuses(),
		);

		$cfilter = isset( $_COOKIE['anth-filter'] ) ? $_COOKIE['anth-filter'] : false;

		if ( $cfilter == 'date' ) {
			$startdate = wp_unslash( $_COOKIE['anth-startdate'] );
			$enddate   = wp_unslash( $_COOKIE['anth-enddate'] );

			$date_range_where = '';

			if ( strlen( $startdate ) > 0 ) {
				$date_range_where .= $wpdb->prepare( " AND post_date >= %s", $startdate );
			}

			if ( strlen( $enddate ) > 0 ) {
				$date_range_where .= $wpdb->prepare( " AND post_date <= %s,", $enddate );
			}

			$where_func   = '$where .= "' . $date_range_where . '"; return $where;';
			$filter_where = create_function( '$where', $where_func );
			add_filter( 'posts_where', $filter_where );
		} else {

			$cterm = isset( $_COOKIE['anth-term'] ) ? $_COOKIE['anth-term'] : false;

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
					<?php
					$item_metadata = array(
						'link'   => sprintf(
							'<a href="%s">%s</a>',
							esc_attr( get_permalink() ),
							esc_html__( 'View post', 'anthologize' )
						),
					);

					$item_post   = get_post( get_the_ID() );
					$item_author = get_userdata( $item_post->post_author );
					$item_tags   = get_the_term_list( get_the_ID(), 'post_tag', '', ', ' );
					$item_cats   = get_the_term_list( get_the_ID(), 'category', '', ', ' );

					if ( $item_author ) {
						$item_metadata['author'] = sprintf(
							__( 'Author: %s', 'anthologize' ),
							esc_html( sprintf( '%s (%s)', $item_author->display_name, $item_author->user_login ) )
						);
					}

					if ( $item_tags ) {
						$item_metadata['tags'] = sprintf( __( 'Tags: %s', 'anthologize' ), $item_tags );
					}

					if ( $item_cats ) {
						$item_metadata['cats'] = sprintf( __( 'Categories: %s', 'anthologize' ), $item_cats );
					}

					/**
					 * Filters the metadata shown below a post item in the project organizer.
					 *
					 * @since 0.8.0
					 *
					 * @param array $item_metadata Metadata assembled by Anthologize.
					 * @param int   $item_id       ID of the post.
					 */
					$item_metadata = apply_filters( 'anthologize_source_item_metadata', $item_metadata, get_the_ID() );

					?>
					<li class="part-item item has-accordion accordion-closed">
						<span class="fromNewId">new-<?php the_ID() ?></span>
						<h3 class="part-item-title"><?php the_title() ?></h3>
						<span class="accordion-toggle hide-if-no-js">
							<span class="accordion-toggle-glyph"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Show details', 'anthologize' ); ?></span>
						</span>

						<div class="item-details">
							<ul>
							<?php foreach ( $item_metadata as $im ) : ?>
								<li><?php echo $im; ?></li>
							<?php endforeach; ?>
							</ul>
						</div>
					</li>
				<?php endwhile; ?>
			</ul>
		<?php
		}

		if ( $cfilter == 'date' ) {
			remove_filter( 'posts_where', $filter_where );
		}
	}

	function get_posts_as_option_list( $part_id ) {
		global $wpdb;

		$items = get_post_meta( $part_id, 'items', true );

		$item_query = new WP_Query( 'post_type=items&post_parent=' . $part_id );

		// @todo This could be a WP_Query
		$sql = "SELECT id, post_title FROM wp_posts WHERE post_type = 'page' OR post_type = 'post' OR post_type = 'anth_imported_item'";
		$ids = $wpdb->get_results($sql);

		$counter = 0;
		foreach( $ids as $id ) {
			if ( in_array( $id->id, $items ) || array_key_exists( $id->id, $items ) ) { // Todo: adjust so that it references parent stuff
				continue;
			}

			echo '<option value="' . esc_attr( $id->id ) . '">' . esc_html( $id->post_title ) . '</option>';
			$counter++;
		}

		if ( ! $counter ) {
			echo '<option disabled="disabled">' . __( 'Sorry, no content to add', 'anthologize' ) . '</option>';
		}
	}

	function get_part_items( $part_id ) {

		$append_parent = !empty( $_GET['append_parent'] ) ? $_GET['append_parent'] : false;

		$items = get_post_meta( $part_id, 'items', true );

		//echo "<pre>";
		//print_r($items); die();
		//if ( empty( $items ) )
		//	return;

		$args = array(
			'post_parent'    => $part_id,
			'post_type'      => 'anth_library_item',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
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

		if ( ! isset( $project_id ) || ! isset( $post_id ) || ! isset( $dest_id ) || ! isset( $dest_seq ) ) {
			return false;
		}

		if ( ! $new_post ) {
			if ( ! isset( $source_id ) || ! isset( $source_seq ) ) {
				return false;
			}
		}

		if ( true === $new_post ) {
			$add_item_result = $this->add_item_to_part( $post_id, $dest_id );

			//clone over the attachments to the original post and associate them with the new
			//library item. That should make things like the [gallery] shortcode work
			$attArgs = array( 'post_parent'=> $post_id, 'post_type' => 'attachment' );
			$attachments = get_children( $attArgs );
			foreach ( $attachments as $attachment ) {
				$attPostArgs = array(
					'post_parent'    => $add_item_result,
					'post_type'      => 'attachment',
					'guid'           => $attachment->guid,
					'post_title'     => $attachment->post_title,
					'post_status'    => $attachment->post_status,
					'post_name'      => $attachment->post_name,
					'post_mime_type' => $attachment->post_mime_type
				);
				wp_insert_post( $attPostArgs );
			}

			if ( false === $add_item_result ) {
				return false;
			}
			$post_id = $add_item_result;

			// $dest_seq[$post_id] = $dest_seq['new_new_new'];
			// unset($dest_seq['new_new_new']);
		} else {
			$post_params = array( 'ID' => $post_id, 'post_parent' => $dest_id );
			$update_item_result = wp_update_post( $post_params );
			if ( 0 === $update_item_result ) {
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
		if ( ! wp_delete_post( $id ) ) {
		    return false;
		}

		$this->update_project_modified_date();

		return true;
	}

	function append_children( $append_parent, $append_children ) {

		$parent_post = get_post( $append_parent );
		$pp_content = $parent_post->post_content;

		if ( ! $author_name = get_post_meta( $append_parent, 'author_name', true ) ) {
			$author_name = '';
		}

		if ( ! $author_name_array = get_post_meta( $append_parent, 'author_name_array', true ) ) {
			$author_name_array = array();
		}

		foreach ( $append_children as $append_child ) {
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
			'ID'           => $append_parent,
			'post_content' => $pp_content,
		);

		if ( ! wp_update_post( $args ) ) {
			return false;
		}

		update_post_meta( $append_parent, 'author_name', $author_name );
		update_post_meta( $append_parent, 'author_name_array', $author_name_array );

		$this->update_project_modified_date();

		return true;
	}

	function display_item( $append_parent ) {
		global $post;

		/**
		 * Pull up some comment data to be used in the Comments (x/y) area.
		 * Comments themselves are fetched with AJAX as needed.
		 */

		// First, the original post
		$anth_meta = get_post_meta( get_the_ID(), 'anthologize_meta', true );

		$original_comment_count = 0;
		if ( ! empty( $anth_meta['original_post_id'] ) ) {
			$original_post = get_post( $anth_meta['original_post_id'] );
			if ( $original_post ) {
				$original_comment_count = (int) $original_post->comment_count;
			}
		}

		// Then, see how many comments are being brought along to the export
		$included_comment_count = 0;
		if ( ! empty( $anth_meta['included_comments'] ) ) {
			$included_comment_count = count( $anth_meta['included_comments'] );
		}

		?>

		<li id="item-<?php the_ID() ?>" class="part-item item">

			<?php if ( $append_parent ) : ?>
				<input type="checkbox" name="append_children[]" value="<?php the_ID() ?>" <?php if ( $append_parent == $post->ID ) echo 'checked="checked" disabled=disabled'; ?>/> <?php echo esc_html( $post->ID ) . " " . esc_html( $append_parent ) ?>
			<?php endif; ?>

			<noscript>
				<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo esc_attr( $this->project_id ) ?>&move_up=<?php the_ID() ?>">&uarr;</a> <a href="admin.php?page=anthologize&action=edit&project_id=<?php echo esc_attr( $this->project_id ) ?>&move_down=<?php the_ID() ?>">&darr;</a>
			</noscript>

			<h3 class="part-item-title">
				<span class="part-title"><?php the_title() ?></span>

				<div class="part-item-buttons anth-buttons">
					<a href="post.php?post=<?php the_ID() ?>&action=edit&return_to_project=<?php echo esc_attr( $this->project_id ) ?>"><?php _e( 'Edit', 'anthologize' ) ?></a> |

					<?php /* Comments are being pushed to a further release */ ?>
					<?php /*
					<a href="#comments" class="comments toggle"><?php printf( __( 'Comments (<span class="included-comment-count">%1$d</span>/%2$d)', 'anthologize' ), $included_comment_count, $original_comment_count ) ?></a><span class="comments-sep toggle-sep"> |</span>
					*/ ?>

					<a href="#append" class="append toggle"><?php _e( 'Append', 'anthologize' ) ?></a><span class="append-sep toggle-sep"> |</span>

					<a target="new" href="<?php echo esc_url( $this->preview_url( get_the_ID(), 'anth_library_item' ) ) ?>" class=""><?php _e( 'Preview', 'anthologize' ) ?></a><span class="toggle-sep"> |</span>

					<a href="admin.php?page=anthologize&action=edit&project_id=<?php echo esc_attr( $this->project_id ) ?>&remove=<?php the_ID() ?>" class="confirm"><?php _e( 'Remove', 'anthologize' ) ?></a>
				</div>
			</h3>

		</li>

		<?php
	}

	/**
	 * Get the href for an object's Preview link
	 *
	 * @package Anthologize
	 * @since 0.6
	 *
	 * @param int $post_id The id of the post (item, part, or project) being previewed
	 * @param str $post_type The post type of the post being previewed
	 */
	function preview_url( $post_id = false, $post_type = 'anth_library_item' ) {
		$query_args = array(
		    'page'         => 'anthologize',
		    'anth_preview' => '1',
		    'post_id' 	   => $post_id,
		    'post_type'	   => $post_type
		);

		$url = add_query_arg( $query_args, admin_url( 'admin.php' ) );

		return $url;
	}

	/**
	 * Gets the post statuses of source items to show in the project organizer.
     *
     * @package Anthologize
     * @since 0.8.0
	 *
	 * @return array
	 */
	function source_item_post_statuses() {
		/**
		 * Status of posts to include in the project organizer.
		 * Defaults to just published, pending, future and private.
		 *
		 * @since 0.8.0
		 *
		 * @param array $statuses statuses of posts/pages to include in the project organizer
		 */
		return apply_filters(
			'anthologize_source_item_post_statuses',
			array( 'publish', 'pending', 'future', 'private' )
		);
	}
}

endif;
