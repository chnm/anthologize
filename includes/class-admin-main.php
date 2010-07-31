<?php




if ( !class_exists( 'Anthologize_Admin_Main' ) ) :

class Anthologize_Admin_Main {

	/**
	 * List all my projects. Pretty please
	 */
	function anthologize_admin_main () {

		$this->project_id = $project_id;

		$project = get_post( $project_id );

		$this->project_name = $project->post_title;

		add_action( 'admin_init', array ( $this, 'init' ) );

		add_action( 'admin_menu', array( $this, 'dashboard_hooks' ) );

	}

	function init() {

	    foreach ( array('projects', 'parts', 'library_items', 'imported_items') as $type )
    	{
            add_meta_box('anthologize', 'Anthologize', array($this,'item_meta_box'), $type, 'side', 'high');
    	}

    	add_action('save_post',array( $this, 'item_meta_save' ));

		do_action( 'anthologize_admin_init' );
	}

	function dashboard_hooks() {
		$plugin_pages = array();

		$plugin_pages[] = add_menu_page( __( 'Anthologize', 'anthologize' ), __( 'Anthologize', 'anthologize' ), 'manage_options', 'anthologize', array ( $this, 'display' ) );

        $plugin_pages[] = add_submenu_page( 'anthologize', __('New Project','anthologize'), __('New Project','anthologize'), 'manage_options', dirname( __FILE__ ) . '/class-new-project.php');

		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Export Project', 'anthologize' ), __( 'Export Project', 'anthologize' ), 'manage_options', dirname( __FILE__ ) . '/class-export-panel.php' );

		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Import Content', 'anthologize' ), __( 'Import Content', 'anthologize' ), 'manage_options', dirname( __FILE__ ) . '/class-import-feeds.php' );

		foreach ( $plugin_pages as $plugin_page ) {
			add_action( "admin_print_styles", array( $this, 'load_styles' ) );
			add_action( "admin_print_scripts-$plugin_page", array( $this, 'load_scripts' ) );
		}
	}

	function load_scripts() {
    	wp_enqueue_script( 'anthologize-js', WP_PLUGIN_URL . '/anthologize/js/project-organizer.js' );
    	wp_enqueue_script( 'jquery');
    	wp_enqueue_script( 'jquery-ui-core');
    	wp_enqueue_script( 'jquery-ui-sortable');
    	wp_enqueue_script( 'jquery-ui-draggable');
    	wp_enqueue_script( 'blockUI-js', WP_PLUGIN_URL . '/anthologize/js/jquery.blockUI.js' );
    	wp_enqueue_script( 'anthologize_admin-js', WP_PLUGIN_URL . '/anthologize/js/anthologize_admin.js' );
    	wp_enqueue_script( 'anthologize-sortlist-js', WP_PLUGIN_URL . '/anthologize/js/anthologize-sortlist.js' );
	}

	function load_styles() {
    	wp_enqueue_style( 'anthologize-css', WP_PLUGIN_URL . '/anthologize/css/project-organizer.css' );

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
			'post_type' => 'parts',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => ASC
		);

		$items_query = new WP_Query( $args );

		if ( $posts = $items_query->get_posts() ) {
            return $posts;
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
        			'post_type' => 'library_items',
        			'posts_per_page' => -1,
        			'orderby' => 'menu_order',
        			'order' => ASC
        		);

        		$items_query = new WP_Query( $args );

                // May need optimization
        		if ( $posts = $items_query->get_posts() ) {
                    foreach($posts as $post) {
                        $items[] = $post;
                    }
        		}
            }
        }
        return $items;

	}

	function display() {
//		print_r($_GET); die();

		$project = get_post( $_GET['project_id'] );

        if ( $_GET['action'] == 'delete' && $project ) {
			wp_delete_post($project->ID);
		}

		if ( $_GET['action'] == 'edit' && $project ) {
			$this->load_project_organizer( $_GET['project_id'] );
		}

		if (
			!isset( $_GET['action'] ) ||
			$_GET['action'] == 'list-projects' ||
			( $_GET['action'] == 'edit' && !$project )

		) {

		?>

		<div class="wrap anthologize">


		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
		<h2><?php _e( 'My Projects', 'anthologize' ) ?> <a href="admin.php?page=anthologize/includes/class-new-project.php" class="button add-new-h2"><?php _e( 'Add New', 'anthologize' ) ?></a></h2>


		<?php


		if ( $_GET['action'] == 'edit' && !isset( $_GET['project_id'] ) || isset( $_GET['project_id'] ) && !$project ) {
			$this->display_no_project_id_message();
		}

		query_posts( 'post_type=projects' );

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
            		<th scope="col"><?php _e( 'Project Title') ?></th>
            		<th scope="col"><?php _e( 'Created By') ?></th>
            		<th scope="col"><?php _e( 'Number of Parts') ?></th>
            		<th scope="col"><?php _e( 'Number of Items') ?></th>
            		<th scope="col"><?php _e( 'Date Created') ?></th>
            		<th scope="col"><?php _e( 'Date Modified') ?></th>
            	</tr>
            </thead>
			<tbody>
				<?php while ( have_posts() ) : the_post(); ?>

					<tr>
						<tr>
            			<th scope="row" class="check-column">
						</th>

						<th scope="row"  class="post-title">
							<a href="admin.php?page=anthologize&amp;action=edit&amp;project_id=<?php the_ID() ?>" class="row-title">#<?php the_ID(); ?> : <?php the_title(); ?></a>

							<br/>
									<?php
									$controlActions	= array();
									$controlActions[]	= '<a href="admin.php?page=anthologize/includes/class-new-project.php&project_id=' . get_the_ID() .'">' . __('Edit Project') . '</a>';
									$controlActions[]   = '<a href="admin.php?page=anthologize&action=edit&project_id=' . get_the_ID() .'">'.__('Manage Parts') . '</a>';
									$controlActions[]   = '<a href="admin.php?page=anthologize&action=delete&project_id=' . get_the_ID() .'">'.__('Delete Project') . '</a>';



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
						    <?php the_date(); ?>
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

        $current_data = get_post_meta($post_id, 'anthologize_meta', TRUE);

        $new_data = $_POST['anthologize_meta'];

        if ( $current_data )
    	{
    		if ( is_null($new_data) ) delete_post_meta($post_id,'anthologize_meta');
    		else update_post_meta($post_id,'anthologize_meta',$new_data);
			update_post_meta( $post_id, 'author_name', $new_data['author_name'] );
    	}
    	elseif ( !is_null($new_data) )
    	{
    		add_post_meta($post_id,'anthologize_meta',$new_data,TRUE);
			update_post_meta( $post_id, 'author_name', $new_data['author_name'] );
    	}

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

            <input type="hidden" name="menu_order" value="<?php echo $post->menu_order; ?>">
            <input type="hidden" name="anthologize_noncename" value="<?php echo wp_create_nonce(__FILE__); ?>" />
        </div>
    <?php
    }

}

endif;

$anthologize_admin_main = new Anthologize_Admin_Main();


?>
