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
		do_action( 'anthologize_admin_init' );
	}

	function dashboard_hooks() {
		$plugin_pages = array();

		$plugin_pages[] = add_menu_page( __( 'Anthologize', 'anthologize' ), __( 'Anthologize', 'anthologize' ), 'manage_options', 'anthologize', array ( $this, 'display' ) );

//		$plugin_pages[] = add_submenu_page( 'anthologize', __('My Projects','bp-invite-anyone'), __('My Projects','bp-invite-anyone'), 'manage_options', __FILE__, array( $this, 'display' ) );

//		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Edit Project', 'anthologize' ), __('Edit Project', 'anthologize' ), 'manage_options', dirname( __FILE__ ) . '/class-project-organizer.php' );

		$plugin_pages[] = add_submenu_page( 'anthologize', __( 'Settings', 'anthologize' ), __( 'Settings', 'anthologize' ), 'manage_options', __FILE__, 'anthologize_admin_panel' );

		foreach ( $plugin_pages as $plugin_page ) {
			add_action( "admin_print_scripts-$plugin_page", 'anthologize_admin_scripts' );
			add_action( "admin_print_styles-$plugin_page", 'anthologize_admin_styles' );
		}
	}

	function load_project_organizer( $project_id ) {
		require_once( dirname( __FILE__ ) . '/class-project-organizer.php' );
		$project_organizer = new Anthologize_Project_Organizer( $project_id );
		$project_organizer->display();

	}

	function display() {
//		print_r($_GET); die();

		if ( $_GET['action'] == 'edit' && isset( $_GET['project_id'] ) ) {
			$this->load_project_organizer( $_GET['project_id'] );
		}


		if ( !isset( $_GET['action'] ) || $_GET['action'] == 'list-projects' ) { // todo: this is broken
		?>

		<div class="wrap">

		<h2>My Projects</h2>

		<?php

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
            		<th scope="col" class="bp-gm-group-id-header"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=group_id"><?php _e( 'Project Title', 'bp-group-management' ) ?></a></th>

            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=name"><?php _e( 'Number of Chapters', 'bp-group-management' ) ?></a></th>
            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=group_id"><?php _e( 'Number of Items', 'bp-group-management' ) ?></a></th>
            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=popular"><?php _e( 'Date Created', 'bp-group-management' ) ?></a></th>
            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=popular"><?php _e( 'Date Modified', 'bp-group-management' ) ?></a></th>

            		<?php do_action( 'bp_gm_group_column_header' ); ?>
            	</tr>
            </thead>

			<tbody>
				<?php while ( have_posts() ) : the_post(); ?>
					<tr>

						<tr>
            			<th scope="row" class="check-column">
						</th>

						<th scope="row"  class="post-title">
							<a href="admin.php?page=anthologize&action=edit&project_id=<?php the_ID() ?>" class="row-title"><?php the_title(); ?></a>

							<br/>
									<?php
									$controlActions	= array();
									$controlActions[]	= '<a href="admin.php?page=anthologize&action=edit&project_id=' . get_the_ID() .'" class="">' . __('Edit') . '</a>';


									?>

									<?php if (count($controlActions)) : ?>
									<div class="row-actions">
										<?php echo implode(' | ', $controlActions); ?>
									</div>
									<?php endif; ?>


						</th>


						<td scope="row" class="bp-gm-avatar">

 						</td>

						<td scope="row">


						</td>

						<td scope="row">
						</td>

						<td scope="row">
						</td>

						<?php do_action( 'bp_gm_group_column_data' ); ?>


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





}

endif;

$anthologize_admin_main = new Anthologize_Admin_Main();


function okokok( $it ) {
	echo $it;
	return $it;
}
//add_filter( 'posts_request', 'okokok' );





?>