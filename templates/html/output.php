<?php

include_once(ANTHOLOGIZE_TEIDOM_PATH);
include_once(ANTHOLOGIZE_TEIDOMAPI_PATH);
require_once(WP_PLUGIN_DIR. '/anthologize/includes/theming-functions.php');
global $api;

//TODO: simplify and condense these options

$ops = array('includeStructuredSubjects' => true, //Include structured data about tags and categories
		'includeComments' => false,
		'includeItemSubjects' => true, // Include basic data about tags and categories
		'includeCreatorData' => true, // Include basic data about creators
		'includeStructuredCreatorData' => true, //include structured data about creators
		'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories and creator data)
		'checkImgSrcs' => false, //whether to check availability of image sources
		'linkToEmbeddedObjects' => false,
		'indexSubjects' => false,
		'indexCategories' => false,
		'indexTags' => false,
		'indexPeople' => false,
		'indexImages' => false,
		);


$ops['outputParams'] = anthologize_get_session_output_params();
$session = anthologize_get_session();

$tei = new TeiDom($session, $ops);

header("Content-type: text/html");

$api = new TeiApi($tei);


$fileName = $api->getFileName();
$ext = "html";

if( isset($ops['outputParams']['download']) ) {
	header("Content-type: text/html");
	header("Content-Disposition: attachment; filename=$fileName.$ext");
}


?>

<!DOCTYPE html>
<html>
	<head>
	<meta charset='utf-8'>
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

				if ( anth_tags() ) {
					echo "<p>" . __( 'Tags', 'anthologize' ) . "</p><ul>";
					while( anth_tags() ) {
						anth_tag_details();
						echo "<li>";
						echo "<a href='" . anth_get_the_tag_detail('url') . "'>"  . anth_get_the_tag() . "</a>";
						echo "</li>";
					}
					echo "</ul>";
				}

				if ( anth_categories() ) {
					echo "<p>" . __( 'Categories', 'anthologize' ) . "</p><ul>";
					while( anth_categories() ) {
						anth_category_details();
						echo "<li>";
						echo "<a href='" . anth_get_the_category_detail('url') . "'>"  . anth_get_the_category() . "</a>";
						echo "</li>";
					}
					echo "</ul>";
				}

				anth_person_details();
				anth_person_details('anthologizer');

				?>
				<h3><?php anth_the_title() ?></h3>
				<div class="item-meta" style="border: 1px solid black; margin: 5px; padding: 5px;">

					<img class="gravatar" src="<?php anth_the_person_gravatar_url(); ?>" />
					<p class="item-author">By <?php anth_the_person(); ?></p>
					<p class="item-anthologizer">Anthologized by: <?php anth_the_person('anthologizer'); ?></p>
					<p class="item-asserted-author">Attributed to: <?php anth_the_person('assertedAuthor'); ?></p>
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
