<?php

class Anthologize_About {

	/**
	 * Singleton bootstrap
	 *
	 * @since 0.7
	 * @return obj Anthologize instance
	 */
	public static function init() {
		static $instance;
		if ( empty( $instance ) ) {
			$instance = new Anthologize_About();
		}
		return $instance;
	}

	/**
	 * Constructor method
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	function __construct() {
		$this->display();
	}

	/**
	 * Markup for the settings panel
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	function display() {
		?>

		<div class="wrap anthologize">

			<div id="anthologize-logo"><img src="<?php echo esc_url( plugins_url() . '/anthologize/images/anthologize-logo.gif' ) ?>" alt="<?php esc_attr_e( 'Anthologize logo', 'anthologize' ); ?>" /></div>

			<h2><?php _e( 'About Anthologize', 'anthologize' ) ?></h2>

			<p><?php _e( '<a href="http://anthologize.org">Anthologize</a> was initially developed as part of the <a href="http://oneweekonetool.org">One Week | One Tool</a> project at the <a href="http://chnm.gmu.edu">Roy Rosenzweig Center for History and New Media</a> of George Mason University.', 'anthologize' ) ?></p>

			<p><?php _e( 'Members of the original Anthologize team: Jason Casden, Boone Gorges, Kathie Gossett, Scott Hanrath, Effie Kapsalis, Douglas Knox, Zachary McCune, Julie Meloni, Patrick Murray-John, Stephen Ramsay, Patrick Rashleigh, Jana Remy. The project manager was Tom Scheinfeldt. Special thanks to Jeremy Boggs, Sheila Brennan, Sharon Leon, Trever Owens, and other members of the CHNM team.', 'anthologize' ) ?></p>

			<h2><?php _e( 'Sponsors', 'anthologize' ) ?></h2>

			<p><?php _e( 'The initial and ongoing development of Anthologize has been made possible by the generous sponsorship of a number of organizations and individuals.', 'anthologize' ) ?></p>

			<h3><?php _e( 'Major Sponsors', 'anthologize' ) ?></h3>

			<ul>
				<li><a href="http://www.neh.gov/divisions/odh">The Office of Digital Humanities of the National Endowment for the Humanties</a></li>
				<li><a href="http://chnm.gmu.edu">The Roy Rosenzweig Center for History and New Media</a></li>
				<li><a href="http://openlab.citytech.cuny.edu">City Tech OpenLab</a></li>
				<li><a href="http://www.demokratie-dialog.de/">Demokratie & Dialog e.V.</a></li>
			</ul>

			<h3><?php _e( 'Individual Sponsors', 'anthologize' ) ?></h3>

			<?php
				$sponsors = array(
					array( 'name' => 'Jean Amaral', 'link' => 'http://www.jamaral.info/', ),
					array( 'name' => 'Jared Bennett', 'link' => 'http://commons.hwdsb.on.ca/', ),
					array( 'name' => 'Sheila Brennan', 'link' => 'http://lotfortynine.org/', ),
					array( 'name' => 'Martha Burtis', 'link' => 'http://wrapping.marthaburtis.net/', ),
					array( 'name' => 'Mel Choyce', 'link' => 'http://choycedesign.com/', ),
					array( 'name' => 'Jay Collier', 'link' => 'http://jaycollier.net', ),
					array( 'name' => 'Catherine Derecki', 'link' => '', ),
					array( 'name' => 'Sherman Dorn', 'link' => 'http://shermandorn.com', ),
					array( 'name' => 'Jack Dougherty', 'link' => 'http://internet2.trincoll.edu/facProfiles/Default.aspx?fid=1004266', ),
					array( 'name' => 'Andrew Famiglietti', 'link' => 'http://copyvillain.org/', ),
					array( 'name' => 'Matthew K Gold', 'link' => '', ),
					array( 'name' => 'Kathie Gossett', 'link' => 'http://www.kathiegossett.com/', ),
					array( 'name' => 'Jim Groom', 'link' => 'http://bavatuesdays.com', ),
					array( 'name' => 'Josh Honn', 'link' => 'http://joshhonn.com/', ),
					array( 'name' => 'Helen Hou-Sand&iacute;', 'link' => 'http://helenhousandi.com/', ),
					array( 'name' => 'Sarah Hovde', 'link' => '', ),
					array( 'name' => 'Tonya Howe', 'link' => 'http://cerisia.cerosia.org/', ),
					array( 'name' => 'Cyri Jones', 'link' => 'http://24posts.com/', ),
					array( 'name' => 'Korey Jackson', 'link' => 'http://koreybjackson.com/', ),
					array( 'name' => 'Aaron Jorbin', 'link' => 'http://aaron.jorb.in/', ),
					array( 'name' => 'Trip Kirkpatrick', 'link' => '', ),
					array( 'name' => 'Diane Kovach', 'link' => '', ),
					array( 'name' => 'Douglas Knox', 'link' => '', ),
					array( 'name' => 'Sharon Leon', 'link' => 'http://www.6floors.org/bracket/', ),
					array( 'name' => 'Tammie Lister', 'link' => 'http://logicalbinary.com/', ),
					array( 'name' => 'Eric A Mann', 'link' => 'http://eamann.com/', ),
					array( 'name' => 'Siobhan McKeown', 'link' => 'http://wordsforwp.com', ),
					array( 'name' => 'Beno&icirc; Melan&ccedil;on', 'link' => 'http://mapageweb.umontreal.ca/melancon/', ),
					array( 'name' => 'Patrick Murray-John', 'link' => 'http://hackingthehumanities.org/', ),
					array( 'name' => 'Rebecca Onion', 'link' => '', ),
					array( 'name' => 'David Parry', 'link' => 'http://outsidethetext.com', ),
					array( 'name' => 'PressCrew', 'link' => 'http://presscrew.com', ),
					array( 'name' => 'Quint Rahaman', 'link' => '', ),
					array( 'name' => 'Range', 'link' => 'http://ran.ge/', ),
					array( 'name' => 'Shayne Sanderson', 'link' => 'http://shaynesanderson.com', ),
					array( 'name' => 'Paul Schacht', 'link' => 'http://www.geneseo.edu/~schacht/', ),
					array( 'name' => 'Tom Scheinfeldt', 'link' => 'http://foundhistory.org/', ),
					array( 'name' => 'Daniel Schulz-Jackson', 'link' => '', ),
					array( 'name' => 'Michael Branson Smith', 'link' => 'http://michaelbransonsmith.net/', ),
					array( 'name' => 'Kerry Thompson', 'link' => '', ),
					array( 'name' => 'Nathan Tyler', 'link' => 'http://croixhaug.com/', ),
					array( 'name' => 'Hannah Warmbier', 'link' => '', ),
					array( 'name' => 'Jane Wells', 'link' => 'http://jane.wordpress.com/', ),
					array( 'name' => 'K.Adam White', 'link' => 'http://kadamwhite.com/', ),
					array( 'name' => 'Brad Williams', 'link' => 'http://strangework.com/', ),
					array( 'name' => 'George H. Williams', 'link' => 'http://georgehwilliams.net/', ),
				);

				$sponsor_texts = array();
				foreach ( $sponsors as $s ) {
					$text = $s['name'];

					if ( $s['link'] ) {
						$text = '<a href="' . $s['link'] . '">' . $text . '</a>';
					}

					$sponsor_texts[] = $text;
				}
			?>

			<p><?php echo implode( ', ', $sponsor_texts ) ?></p>

		</div>

		<?php
	}
}
