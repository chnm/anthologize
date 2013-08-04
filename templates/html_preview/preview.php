<?php

/**
 * Preview project/part template
 *
 * @package Anthologize
 * @since 0.6
 */


$post_id   = !empty( $_GET['post_id'] ) ? $_GET['post_id'] : false;
$post_type = !empty( $_GET['post_type'] ) ? $_GET['post_type'] : false;

query_posts( array( 'p' => $post_id, 'post_type' => $post_type ) );

if ( have_posts() ) { while ( have_posts() ) { the_post();
	$preview_title = get_the_title();
} }

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
	<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : ?>
		<?php the_post() ?>
		<title><?php echo $preview_title ?> <?php _e( '(Anthologize Preview Mode)', 'anthologize' ) ?></title>
	<?php endwhile ?>
	<?php endif ?>

	<link rel='stylesheet' id='anthologize-preview-css' href='<?php echo plugins_url( 'anthologize/css/preview.css' ) ?>' type='text/css' media='all' />

</head>

<body>

<p id="preview-notice">
	<?php printf( __( 'You are viewing a preview of <strong>%1$s</strong>. This preview is for proofreading purposes only. To get a more accurate sense of what your Anthologize project will look like, you may want to <a href="%2$s">export the project</a>.', 'anthologize' ), $preview_title, add_query_arg( array( 'page' => 'anthologize/includes/class-export-panel.php' ), admin_url( 'admin.php' ) ) ) ?>
</p>

<?php if ( have_posts() ) : ?>
	<ul>
	<?php while ( have_posts() ) : ?>
		<?php the_post() ?>
		
		<li>
			<h2><?php the_title() ?></h2>	
			<?php the_content() ?>
		
			<?php /* Get the children, if there are any */ ?>
			<?php if ( 'anth_library_item' != $post_type ) : ?>
				<?php $child_post_type = 'anth_part' == $post_type ? 'anth_library_item' : 'anth_part' ?>
				<?php $children = new WP_Query( array( 'post_parent' => $post_id, 'post_type' => $child_post_type ) ) ?>
				
				<?php if ( $children->have_posts() ) : ?>
					<ul>
					<?php while ( $children->have_posts() ) : ?>
						<?php $children->the_post() ?>
						<li>
						<h3><?php the_title() ?></h3>			
						<?php the_content() ?>
						
						<?php /* Get the grandchildren, if there are any */ ?>
						<?php if ( 'anth_project' == $post_type ) : ?>
							<?php $grandchildren = new WP_Query( array( 'post_parent' => get_the_ID(), 'post_type' => 'anth_library_item' ) ) ?>
							
							<?php if ( $grandchildren->have_posts() ) : ?>
								<ul>
								<?php while ( $grandchildren->have_posts() ) : ?>
									<?php $grandchildren->the_post() ?>
			
									<li>
									<h4><?php the_title() ?></h4>			
									<?php the_content() ?>
									</li>
								
								<?php endwhile ?>
								</ul>
							<?php endif ?>
						<?php endif ?>
						</li>
					<?php endwhile ?>
					</ul>
				<?php endif ?>
			<?php endif ?>
		</li>
		
	<?php endwhile ?>
	</ul>
<?php endif ?>
</body>
</html>
