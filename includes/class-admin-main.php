<?php


if ( !class_exists( 'Anthologize_Admin_Main' ) ) :

class Anthologize_Admin_Main {

	/**
	 * List all my projects. Pretty please
	 */
	function anthologize_admin_main () {

		add_action( 'admin_init', array ( $this, 'init' ) );

		add_action( 'admin_menu', array( $this, 'dashboard_hooks' ) );

		add_action( 'admin_notices', array( $this, 'version_nag' ) );

	}

	function init() {

	    foreach ( array('anth_project', 'anth_part', 'anth_library_item', 'anth_imported_item') as $type )
    	{
            add_meta_box('anthologize', __( 'Anthologize', 'anthologize' ), array($this,'item_meta_box'), $type, 'side', 'high');
            add_meta_box('anthologize-save', __( 'Save', 'anthologize' ), array($this,'meta_save_box'), $type, 'side', 'high');
            remove_meta_box( 'submitdiv' , $type , 'normal' );
    	}

    	add_action('save_post',array( $this, 'item_meta_save' ));

		do_action( 'anthologize_admin_init' );
	}

	function dashboard_hooks() {
		global $menu;

		if ( !current_user_can( 'manage_options' ) )
			return;

		$menu[57] = array(
			1 => 'read',
			2 => 'separator-anthologize',
			4 => 'wp-menu-separator'
		);

		$plugin_pages = array();

		// Adds the top-level Anthologize Dashboard menu button
		$this->add_admin_menu_page( array(
			'menu_title' => __( 'Anthologize', 'anthologize' ),
			'page_title' => __( 'Anthologize', 'anthologize' ),
			'access_level' => 'manage_options', 'file' => 'anthologize',
			'function' => array( $this, 'display'),
			'position' => 56
		) );

		// Creates the submenu items
		$plugin_pages[] = add_submenu_page( 'anthologize', __('My Projects','anthologize'), __('My Projects','anthologize'), 'manage_options', 'anthologize', array ( $this, 'display' ) );

        $plugin_pages[] = add_submenu_page( 'anthologize', __('New Project','anthologize'), __('New Project','anthologize'), 'manage_options', dirname( __FILE__ ) . '/class-new-project.php');

		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Export Project', 'anthologize' ), __( 'Export Project', 'anthologize' ), 'manage_options', dirname( __FILE__ ) . '/class-export-panel.php' );

		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Import Content', 'anthologize' ), __( 'Import Content', 'anthologize' ), 'manage_options', dirname( __FILE__ ) . '/class-import-feeds.php' );

		foreach ( $plugin_pages as $plugin_page ) {
			add_action( "admin_print_styles", array( $this, 'load_styles' ) );
			add_action( "admin_print_scripts", array( $this, 'load_scripts' ) );
		}


	}

	// Borrowed, with much love, from BuddyPress. Allows us to put Anthologize way up top.
	function add_admin_menu_page( $args = '' ) {
		global $menu, $admin_page_hooks, $_registered_pages;

		$defaults = array(
			'page_title' => '',
			'menu_title' => '',
			'access_level' => 2,
			'file' => false,
			'function' => false,
			'icon_url' => false,
			'position' => 100
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$file = plugin_basename( $file );

		$admin_page_hooks[$file] = sanitize_title( $menu_title );

		$hookname = get_plugin_page_hookname( $file, '' );
		if (!empty ( $function ) && !empty ( $hookname ))
			add_action( $hookname, $function );

		if ( empty($icon_url) )
			$icon_url = 'images/generic.png';
		elseif ( is_ssl() && 0 === strpos($icon_url, 'http://') )
			$icon_url = 'https://' . substr($icon_url, 7);

		do {
			$position++;
		} while ( !empty( $menu[$position] ) );

		$menu[$position] = array ( $menu_title, $access_level, $file, $page_title, 'menu-top ' . $hookname, $hookname, $icon_url );
		unset( $menu[$position][5] );

		$_registered_pages[$hookname] = true;

		return $hookname;
	}

	function load_scripts() {
    	wp_enqueue_script( 'anthologize-js', WP_PLUGIN_URL . '/anthologize/js/project-organizer.js' );
    	wp_enqueue_script( 'jquery');
    	wp_enqueue_script( 'jquery-ui-core');
    	wp_enqueue_script( 'jquery-ui-sortable');
    	wp_enqueue_script( 'jquery-ui-draggable');
			wp_enqueue_script( 'jquery-ui-datepicker', WP_PLUGIN_URL . '/anthologize/js/jquery-ui-datepicker.js');
    	wp_enqueue_script( 'jquery-cookie', WP_PLUGIN_URL . '/anthologize/js/jquery-cookie.js' );
    	wp_enqueue_script( 'blockUI-js', WP_PLUGIN_URL . '/anthologize/js/jquery.blockUI.js' );
    	wp_enqueue_script( 'anthologize_admin-js', WP_PLUGIN_URL . '/anthologize/js/anthologize_admin.js' );
    	wp_enqueue_script( 'anthologize-sortlist-js', WP_PLUGIN_URL . '/anthologize/js/anthologize-sortlist.js' );
	}

	function load_styles() {
    	wp_enqueue_style( 'anthologize-css', WP_PLUGIN_URL . '/anthologize/css/project-organizer.css' );
			wp_enqueue_style( 'jquery-ui-datepicker-css', WP_PLUGIN_URL . '/anthologize/css/jquery-ui-1.7.3.custom.css');
	}

	function load_project_organizer( $project_id ) {
		require_once( dirname( __FILE__ ) . '/class-project-organizer.php' );
		$project_organizer = new Anthologize_Project_Organizer( $project_id );
		$project_organizer->display();

	}

	function display_no_project_id_message() {
		?>
			<div id="notice" class="error below-h2">
				<p><?php _e( 'Project not found', 'anthologize' ) ?></p>
			</div>
		<?php
	}

    function get_project_parts($project_id = null) {

        global $post;

        if (!$project_id) {
            $project_id = $post->ID;
        }

		$args = array(
			'post_parent' => $project_id,
			'post_type' => 'anth_part',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);

		$parts_query = new WP_Query( $args );

		if ( $parts = $parts_query->get_posts() ) {
            return $parts;
		}

	}

	function get_project_items($project_id = null) {

        global $post;

        if (!$project_id) {
            $project_id = $post->ID;
        }

        $parts = $this->get_project_parts($project_id);

        $items = array();
        if ($parts) {
            foreach ($parts as $part) {
                $args = array(
        			'post_parent' => $part->ID,
        			'post_type' => 'anth_library_item',
        			'posts_per_page' => -1,
        			'orderby' => 'menu_order',
        			'order' => 'ASC'
        		);

        		$items_query = new WP_Query( $args );

                // May need optimization
        		if ( $child_posts = $items_query->get_posts() ) {
                    foreach($child_posts as $child_post) {
                        $items[] = $child_post;
                    }
        		}
            }
        }
        return $items;

	}

	function display() {
//		print_r($_GET); die();

		if ( isset( $_GET['project_id'] ) )
			$project = get_post( $_GET['project_id'] );

		if ( isset( $_GET['action'] ) ) {
			if ( $_GET['action'] == 'delete' && $project ) {
				wp_delete_post($project->ID);
			}

			if ( $_GET['action'] == 'edit' && $project ) {
				$this->load_project_organizer( $_GET['project_id'] );
			}
		}

		if (
			!isset( $_GET['action'] ) ||
			$_GET['action'] == 'list-projects' ||
			( $_GET['action'] == 'edit' && !$project ) ||
			( $_GET['action'] == 'delete')

		) {

		?>

		<div class="wrap anthologize">



		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
		<h2><?php _e( 'My Projects', 'anthologize' ) ?> <a href="admin.php?page=anthologize/includes/class-new-project.php" class="button add-new-h2"><?php _e( 'Add New', 'anthologize' ) ?></a></h2>


		<?php if ( isset( $_GET['project_saved'] ) ) : ?>
			<div id="message" class="updated fade">
				<p><?php _e( 'Project Saved', 'anthologize' ) ?></p>
			</div>
		<?php endif; ?>


		<?php


		if ( $_GET['action'] == 'edit' && !isset( $_GET['project_id'] ) || isset( $_GET['project_id'] ) && !$project ) {
			$this->display_no_project_id_message();
		}

		query_posts( 'post_type=anth_project' );

		if ( have_posts() ) {
		?>
			<div class="tablenav">
    			<div class="tablenav-pages">
					<span class="displaying-num" id="group-dir-count">
					</span>

					<span class="page-numbers" id="group-dir-pag">
					</span>

				</div>
			</div>

			<table cellpadding="0" cellspacing="0" class="widefat">
			<thead>
				<tr>
					<th scope="col" class="check-column"></th>
            		<th scope="col"><?php _e( 'Project Title', 'anthologize' ) ?></th>
            		<th scope="col"><?php _e( 'Created By', 'anthologize' ) ?></th>
            		<th scope="col"><?php _e( 'Number of Parts', 'anthologize' ) ?></th>
            		<th scope="col"><?php _e( 'Number of Items', 'anthologize' ) ?></th>
            		<th scope="col"><?php _e( 'Date Created', 'anthologize' ) ?></th>
            		<th scope="col"><?php _e( 'Date Modified', 'anthologize' ) ?></th>
            	</tr>
            </thead>
			<tbody>
				<?php while ( have_posts() ) : the_post(); ?>
				
					<tr>
						<tr>
            			<th scope="row" class="check-column">
						</th>

						<th scope="row"  class="post-title">
							<a href="admin.php?page=anthologize&amp;action=edit&amp;project_id=<?php the_ID() ?>" class="row-title"><?php the_title(); ?></a>

							<br/>
									<?php
									$controlActions	= array();
									$controlActions[]	= '<a href="admin.php?page=anthologize/includes/class-new-project.php&project_id=' . get_the_ID() .'">' . __('Project Details', 'anthologize') . '</a>';
									$controlActions[]   = '<a href="admin.php?page=anthologize&action=edit&project_id=' . get_the_ID() .'">'.__('Manage Parts', 'anthologize') . '</a>';
									$controlActions[]   = '<a href="admin.php?page=anthologize&action=delete&project_id=' . get_the_ID() .'" class="confirm-delete">'.__('Delete Project', 'anthologize') . '</a>';



									?>

									<?php if (count($controlActions)) : ?>
									<div class="row-actions">
										<?php echo implode(' | ', $controlActions); ?>
									</div>
									<?php endif; ?>


						</th>


						<td scope="row anthologize-created-by">
                            <?php the_author(); ?>
 						</td>

						<td scope="row anthologize-number-parts">
                            <?php $parts = $this->get_project_parts();  echo count($parts); ?>
						</td>

                        <td scope="row anthologize-number-items">
                            <?php $items = $this->get_project_items();  echo count($items); ?>
						</td>

						<td scope="row anthologize-date-created">
						    <?php global $post; echo date( "F j, Y", strtotime( $post->post_date ) ) ?>
						</td>

						<td scope="row anthologize-date-modified">
						    <?php the_modified_date(); ?>
						</td>

						<?php do_action( 'anthologize_project_column_data' ); ?>


            		</tr>

				<?php endwhile; ?>

			</tbody>


			</table>




		<?php
		} else {
		?>
			<p><?php _e( 'You haven\'t created any projects yet.', 'anthologize' ) ?></p>

			<p><a href="admin.php?page=anthologize/includes/class-new-project.php"><?php _e( 'Start a new project.', 'anthologize' ) ?></a></p>

		<?php
		} // have_posts()

		?>
		</div> <? /* wrap */ ?>
	<?php

		} // isset $_GET['action']

	}

    /**
     * item_delete
     *
     * Deletes an item. Fun!
     **/
     function item_delete($post_id)
     {

     }

	function meta_save_box( $post_id ) {
		?>
	<div class="inside">
		<div class="submitbox" id="submitpost">

			<div id="minor-publishing">

				<div style="display:none;">
					<input type="submit" name="save" value="Save">
				</div>

				<div id="minor-publishing-actions">
					<div id="save-action">
					<input type="submit" name="save" id="save-post" value="<?php _e( 'Save Changes', 'anthologize' ) ?>" tabindex="4" class="button button-highlighted">
					</div>

				</div>

				<div id="major-publishing-actions">

				</div>
			</div>
		</div>
	</div>

		<?php
	}



    /**
     * item_meta_save
     *
     * Processes post save from the item_meta_box function. Saves
     * custom post metadata. Also responsible for correctly
     * redirecting to Anthologize pages after saving.
     **/
    function item_meta_save($post_id)
    {
        // make sure data came from our meta box
        if ( !wp_verify_nonce($_POST['anthologize_noncename'],__FILE__) ) return $post_id;

        // check user permissions
        if ( !current_user_can('edit_post', $post_id) ) return $post_id;

		if ( !$item_id = $_POST['item_id'] )
			return false;

        if ( !$new_data = $_POST['anthologize_meta'] )
        	$new_data = array();

		if ( !$anthologize_meta = get_post_meta( $item_id, 'anthologize_meta', true ) )
			$anthologize_meta = array();

		foreach( $new_data as $key => $value ) {
			$anthologize_meta[$key] = maybe_unserialize( $value );
		}

		update_post_meta($post_id,'anthologize_meta', $anthologize_meta);
		update_post_meta( $post_id, 'author_name', $new_data['author_name'] );

        add_filter('redirect_post_location', array($this , 'item_meta_redirect'));
    	return $post_id;
    }

    function item_meta_redirect($location) {
        $postParent = get_post($_POST['post_parent']);
        if ( isset( $_POST['new_part'] ) )
        	$arg = $_POST['post_parent'];
        else
        	$arg = $postParent->post_parent;
        $location = 'admin.php?page=anthologize&action=edit&project_id='.$arg;


		if ( isset( $_POST['return_to_project'] ) )
			$location = 'admin.php?page=anthologize&action=edit&project_id=' . $_POST['return_to_project'];

        return $location;
    }
    /**
     * item_meta_box
     *
     * Displays form for editing item metadata associated with
     * Anthologize. Includes hidden fields for post_parent and
     * menu_order because WP sets those values to 0 if those
     * fields are not present on the form.
     **/
    function item_meta_box() {

        global $post;

        $meta = get_post_meta( $post->ID, 'anthologize_meta', TRUE );
        $imported_item_meta = get_post_meta( $post->ID, 'imported_item_meta', true );
       	$author_name = get_post_meta( $post->ID, 'author_name', true );
        ?>
        <div class="my_meta_control">

        	<label>Author Name <span>(optional)</span></label>

        	<p>
        		<textarea name="anthologize_meta[author_name]" rows="3" cols="27"><?php echo $author_name ?></textarea>
        	</p>

        	<?php /* Display content for imported feed, if there is any */ ?>
        	<?php if ( $imported_item_meta ) : ?>
        		<dl>
        		<?php foreach ( $imported_item_meta as $key => $value ) : ?>
        			<?php
        				$the_array = array( 'feed_title', 'link', 'created_date' );
        				if ( !in_array( $key, $the_array ) )
        					continue;

						switch ( $key ) {
							case 'feed_title':
								$dt = __( 'Source feed:', 'anthologize' );
								$dd = '<a href="' . $imported_item_meta['feed_permalink'] . '">' . $value . '</a>';
								break;
							case 'link':
								$dt = __( 'Source URL:', 'anthologize' );
								$dd = '<a href="' . $value . '">' . $value . '</a>';
								break;
							/*case 'authors':
								$dt = __( 'Author:', 'anthologize' );
								$ddv = $value[0];
								$dd = $ddv->name;
								break; todo: fixme */
							case 'created_date':
								$dt = __( 'Date created:', 'anthologize' );
								$dd = $value;
								break;
							default:
								continue;
								break;
						}
        			?>


        			<dt><?php echo $dt ?></dt>
        			<dd><?php echo $dd ?></dd>
        		<?php endforeach; ?>
        		</dl>

        	<?php endif; ?>

			<?php if ( isset( $_GET['return_to_project'] ) ) : ?>
				<input type="hidden" name="return_to_project" value="<?php echo $_GET['return_to_project'] ?>" />
			<?php endif; ?>

        	<?php if ( isset( $_GET['project_id'] ) ) : ?>
        		<input type="hidden" name="parent_id" value="<?php echo $_GET['project_id'] ?>">
            <?php else : ?>
                 <input type="hidden" name="parent_id" value="<?php echo $post->post_parent; ?>">
            <?php endif; ?>

            <?php if ( isset( $_GET['new_part'] ) ) : ?>
            	<input type="hidden" name="new_part" value="1" />
            <?php endif; ?>

            <?php if ( isset( $post->ID ) ) : ?>
            	<input type="hidden" name="item_id" value="<?php echo $post->ID ?>" />
            <?php endif; ?>

            <input type="hidden" name="menu_order" value="<?php echo $post->menu_order; ?>">
            <input type="hidden" name="anthologize_noncename" value="<?php echo wp_create_nonce(__FILE__); ?>" />
        </div>
    <?php
    }



	function version_nag() {
		global $wp_version;

		?>

		<?php if ( version_compare( $wp_version, '3.0', '<' ) ) : ?>
		<div id="message" class="updated fade">
			<p style="line-height: 150%"><?php printf( __( "<strong>Anthologize will not work with your version of WordPress</strong>. You are currently running version WordPress v%s, and Anthologize requires version 3.0 or greater. Please upgrade WordPress if you'd like to use Anthologize. ", 'buddypress' ), $wp_version ) ?></p>
		</div>
		<?php endif; ?>

		<?php
	}

}

endif;

$anthologize_admin_main = new Anthologize_Admin_Main();


?>
