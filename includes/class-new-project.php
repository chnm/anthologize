<?php

if ( !class_exists( 'Anthologize_New_Project' ) ) :

class Anthologize_New_Project {

	function anthologize_new_project() {
		$this->__construct();
	}

	function __construct() {
		if ( $_GET['page'] === 'anthologize/includes/class-new-project.php' )
			$this->display();
	}

	function save_project () {

        $post_data = array(
            'post_title' => 'Default Title',
            'post_type' => 'anth_project',
            'post_status' => '',
            'post_date' => date( "Y-m-d G:H:i" ),
            'post_date_gmt' => gmdate( "Y-m-d G:H:i" ),
        );

        if (!empty($_POST['post_title']))
            $post_data['post_title'] = $_POST['post_title'];

        if (!empty($_POST['post_status']))
            $post_data['post_status'] = $_POST['post_status'];

       // print_r($_POST); die();

        // If we're editing an existing project.
        if ( !empty($_POST['project_id'])) {
			
			if ( !$new_anthologize_meta = get_post_meta( 'anthologize_meta' ) ) {
				$new_anthologize_meta = $_POST['anthologize_meta'];
			} else {
				foreach ( $_POST['anthologize_meta'] as $key => $value ) {
					$new_anthologize_meta[$key] = $value;
				}
			}

        	$the_project = get_post( $_POST['project_id'] );
			if ( !empty ($_POST['post_status']) && ($the_project->post_status != $_POST['post_status'] ))
				$this->change_project_status( $_POST['project_id'], $_POST['post_status'] );

            $post_data['ID'] = $_POST['project_id'];
		    wp_update_post($post_data);

		    if ( is_null($new_anthologize_meta) ) {
		        delete_post_meta( $post_data['ID'] ,'anthologize_meta' );
		    } else {
				update_post_meta( $post_data['ID'], 'anthologize_meta', $new_anthologize_meta );
		    }

		} else { // Otherwise, we're creating a new project

            $new_post = wp_insert_post($post_data);
            update_post_meta($new_post, 'anthologize_meta', $new_anthologize_meta );

		}

		wp_redirect( get_admin_url() . 'admin.php?page=anthologize&project_saved=1' );
	}

	function change_project_status( $project_id, $status ) {
		if ( $status != 'publish' && $status != 'draft' )
			return;

		$args = array(
			'post_status' => array( 'draft', 'publish' ),
			'post_parent' => $project_id,
			'nopaging' => true,
			'post_type' => 'anth_part'
		);

		$parts = get_posts( $args);

		foreach ( $parts as $part ) {
			if ( $part->post_status != $status ) {
				$update_part = array(
					'ID' => $part->ID,
					'post_status' => $status,
				);
				wp_update_post( $update_part );
			}

			$args = array(
				'post_status' => array( 'draft', 'publish' ),
				'post_parent' => $part->ID,
				'nopaging' => true,
				'post_type' => 'anth_library_item'
			);

			$library_items = get_posts( $args );

			foreach( $library_items as $item ) {
				if ( $item->post_status != $status ) {
					$update_item = array(
						'ID' => $item->ID,
						'post_status' => $status,
					);
					wp_update_post( $update_item );
				}
			}
		}
	}

	function display() {

	    if ( isset($_POST['save_project']) ) {
            $this->save_project();
            return;
        }

        if (!empty($_GET['project_id'])) {
            // We are editing a project
            
            $project_id = $_GET['project_id'];
            $project = get_post( $project_id );
            if (empty($project)) {
                echo 'Unknown project ID';
                return; 
            }
            $meta = get_post_meta( $project->ID, 'anthologize_meta', TRUE );
           
        } else {
            $project = NULL;
        }

	?>
		<div class="wrap anthologize">

		<div id="anthologize-logo"><img src="<?php echo WP_PLUGIN_URL . '/anthologize/images/anthologize-logo.gif' ?>" /></div>
            <?php if ( $project ): ?>
			<h2><?php _e( 'Edit Project', 'anthologize' ) ?></h2>
            <?php else: ?>
            <h2><?php _e( 'Add New Project', 'anthologize' ) ?></h2>
    		<?php endif; ?>
            <form action="<?php echo get_bloginfo( 'wpurl' ) ?>/wp-admin/admin.php?page=anthologize/includes/class-new-project.php&noheader=true" method="post">
                <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="post_title"><?php _e( 'Project Title', 'anthologize' ) ?></label></th>
                    <td><input type="text" name="post_title" value="<?php if ($project) echo $project->post_title; ?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="anthologize_meta[subtitle]"><?php _e( 'Subtitle', 'anthologize' ) ?></label>
                    <td><input type="text" name="anthologize_meta[subtitle]" value="<?php if( $project && !empty($meta['subtitle']) ) echo $meta['subtitle']; ?>" /></td>
                </tr>

            	<tr valign="top">
            	    <th scope="row"><label><?php _e( 'Author Name <span>(optional)</span>', 'anthologize' ) ?></label></th>
            	    <td><textarea name="anthologize_meta[author_name]" rows="5" cols="50"><?php if( $project && !empty($meta['author_name']) ) echo $meta['author_name']; ?></textarea></td>
            	</tr>

				<?php /* Hidden until there is a more straightforward way to display projects on the front end of WP */ ?>
				<?php /*
            	<tr valign="top">
                    <th scope="row"><label for="post_status"><?php _e( 'Project Status', 'anthologize' ) ?></label></th>
                    <td>
                    	<input type="radio" name="post_status" value="publish" <?php if ( $project->post_status == 'publish' ) : ?>checked="checked"<?php endif; ?> > Published<br />
                    	<input type="radio" name="post_status" value="draft" <?php if ( $project->post_status != 'publish' ) : ?>checked="checked"<?php endif; ?>> Draft<br />
                    	<p><small><?php _e( 'Published projects are available via the web. Remember that you can change the status of your project later.', 'anthologize' ) ?></small></p>
                    </td>
                </tr>
				*/ ?>

            </table>


       	   <div class="anthologize-button"><input type="submit" name="save_project" value="<?php _e( 'Save Project', 'anthologize' ) ?>"></div>
            <input type="hidden" name="project_id" value="<?php if ($project) echo $project->ID ?>">
            </form>

		</div>
		<?php

	}
}

endif;

function item_meta_redirect($location) {
    $location = get_admin_url() . 'admin.php?page=anthologize';
    echo $location; exit;
    return $location;
}

add_filter('redirect_post_location', 'item_meta_redirect');

$new_project = new Anthologize_New_Project();

?>
