<?php

include_once(ANTHOLOGIZE_TEIDOM_PATH);
include_once(ANTHOLOGIZE_TEIDOMAPI_PATH);
require_once(WP_PLUGIN_DIR. '/anthologize/includes/theming-functions.php');
global $api;


//TODO: simplify and condense these options

$ops = array('includeStructuredSubjects' => true, //Include structured data about tags and categories
		'includeItemSubjects' => true, // Include basic data about tags and categories
		'includeCreatorData' => true, // Include basic data about creators
		'includeStructuredCreatorData' => true, //include structured data about creators
		'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories and creator data)
		'checkImgSrcs' => false, //whether to check availability of image sources
		'linkToEmbeddedObjects' => false,
		'indexSubjects' => false,
		'indexCategories' => false,
		'indexTags' => false,
		'indexAuthors' => false,
		'indexImages' => false,
		);


$ops['outputParams'] = $_SESSION['outputParams'];


$tei = new TeiDom($_SESSION, $ops);
$api = new TeiApi($tei);



$fileName = $api->getFileName();
$ext = "html";

if( isset($ops['outputParams']['download']) ) {
	header("Content-type: application/xml");
	header("Content-Disposition: attachment; filename=$fileName.$ext");
}


?>

<html>
	<head>
		<title><?php anth_the_project_title(true); ?></title>
	</head>

	<body>
	<h1><?php anth_the_project_title(); ?></h1>

	<?php anth_section('front'); ?>

	<?php while ( anth_parts() ): ?>

		<?php anth_part(); ?>

		<?php while ( anth_part_items() ): ?>

			<?php anth_item(); ?>
			<h2><?php anth_the_title(); ?></h2>
			<?php anth_the_item_content(); ?>
		<?php endwhile; ?>
	<?php endwhile; ?>


	<?php

	anth_section('body');
	while ( anth_parts() ) {

		anth_part();

		if ( anth_part_has_items() ) { // Anthologize assumes part_id from context

			?>

			<h2><?php anth_the_title(); ?></h2>
			<?php

			while( anth_part_items() ) {
				anth_item();
				echo "<p>Tags</p><ul>";
				while( anth_tags() ) {
					anth_tag_meta();
					echo "<li>";
					echo "<a href='" . anth_get_the_tag_detail('url') . "'>"  . anth_get_the_tag() . "</a>";
					echo "</li>";
				}
				echo "</ul>";

				echo "<p>Categories</p><ul>";
				while( anth_categories() ) {
					anth_category_meta();
					echo "<li>";
					echo "<a href='" . anth_get_the_category_detail('url') . "'>"  . anth_get_the_category() . "</a>";
					echo "</li>";
				}
				echo "</ul>";

				anth_author_meta();
				anth_anthologizer_meta();

				?>
				<h3><?php anth_the_title() ?></h3>
				<div class="item-meta" style="border: 1px solid blue; margin: 5px; padding: 5px;">

					<img class="gravatar" src="<?php anth_the_author_gravatar_url(); ?>" />
					<p class="item-author">By <?php anth_the_author(); ?></p>
					<p class="item-anthologizer">Anthologized by: <?php anth_the_anthologizer(); ?></p>
				</div>
				<div class="item-content">
					<?php anth_the_item_content() ?>
				</div>

				<?php
			}
		}
	}
	?>

	</body>

</html>
<?php die(); ?>