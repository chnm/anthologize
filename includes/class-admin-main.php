<?php

if ( !class_exists( 'Booyakasha_Admin_Main' ) ) :

class Booyakasha_Admin_Main {

	/**
	 * List all my books. Pretty please
	 */
	function booyakasha_admin_main () {

		$this->book_id = $book_id;

		$book = get_post( $book_id );

		$this->book_name = $book->post_title;

		add_action( 'admin_init', array ( $this, 'init' ) );

		add_action( 'admin_menu', array( $this, 'dashboard_hooks' ) );

	}

	function init() {
		do_action( 'booyakasha_admin_init' );
	}

	function dashboard_hooks() {
		$plugin_pages = array();

		$plugin_pages[] = add_menu_page( 'Booyakasha', 'Booyakasha', 'manage_options', 'booyakasha', array ( $this, 'display' ) );

		$plugin_pages[] = add_submenu_page( 'booyakasha', __('My Books','bp-invite-anyone'), __('My Books','bp-invite-anyone'), 'manage_options', __FILE__, array( $this, 'display' ) );
		$plugin_pages[] = add_submenu_page( 'booyakasha', __('Add importers','bp-invite-anyone'), __('Add importers','bp-invite-anyone'), 'manage_options', dirname( __FILE__ ) . '/class-book-organizer.php' );
		$plugin_pages[] = add_submenu_page( 'booyakasha', __('Settings','bp-invite-anyone'), __('Settings','bp-invite-anyone'), 'manage_options', __FILE__, 'booyakasha_admin_panel' );

		foreach ( $plugin_pages as $plugin_page ) {
			add_action( "admin_print_scripts-$plugin_page", 'booyakasha_admin_scripts' );
			add_action( "admin_print_styles-$plugin_page", 'booyakasha_admin_styles' );
		}
	}

	function load_book_organizer( $book_id ) {
		require_once( dirname( __FILE__ ) . '/class-book-organizer.php' );
		$book_organizer = new Booyakasha_Book_Organizer( 1 );
		$book_organizer->display();

	}

	function display() {
//		print_r($_GET); die();
		if ( !isset( $_GET['action'] ) || $_GET['action'] == 'list-books' ) { // todo: this is broken
		?>

		<div class="wrap">

		<h2>My Projects</h2>

		<?php

		query_posts( 'post_type=books' );

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
            		<th scope="col" class="bp-gm-group-id-header"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=group_id"><?php _e( 'Book Title', 'bp-group-management' ) ?></a></th>

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

						<th scope="row"  class="bp-gm-group-id">
							<?php the_title(); ?>

							<br/>
									<?php
									$controlActions	= array();
									$controlActions[]	= '<a href="admin.php?page=booyakashacopy/includes/class-book-organizer.php&book_id=' . get_the_ID() .'" class="edit">' . __('Edit') . '</a>';


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

$booyakasha_admin_main = new Booyakasha_Admin_Main( 1 );








?>