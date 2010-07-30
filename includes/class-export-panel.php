<?php

if ( !class_exists( 'Anthologize_Export_Panel' ) ) :

class Anthologize_Export_Panel {

	var $project_id;

	/**
	 * The export panel. We are the champions, my friends
	 */
	function anthologize_export_panel ( $project_id ) {

		$this->project_id = $project_id;

	}



	function display() {
	?>
		<div class="wrap">

			<h2><?php echo $this->project_name ?></h2>



		</div>
		<?php

	}

}

endif;

?>