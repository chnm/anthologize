<?php
/**
* base.php - Controller file for PDF generator.
*
* This file is part of Anthologize {@link http://anthologize.org}.
*
* @author One Week | One Tool {@link http://oneweekonetool.org/people/}
*
* Last Modified: Fri Aug 06 15:54:55 CDT 2010
*
* @copyright Copyright (c) 2010 Center for History and New Media,
* George Mason University.
*
* Anthologize is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3, or (at your option) any
* later version.
*
* Anthologize is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
* for more details.
*
* You should have received a copy of the GNU General Public License
* along with Anthologize; see the file license.txt.  If not see
* @link http://www.gnu.org/licenses/.
*
* @package anthologize
*/

//error_reporting(0);

$class_tei_api = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-api.php';
$class_pdf = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'class-pdf.php';

require_once($class_tei_api);
require_once($class_pdf);

function main() {


	$ops = array( 'includeStructuredSubjects' => true, //Include structured data about tags and categories
		'includeItemSubjects' => true, // Include basic data about tags and categories
		'includeCreatorData' => true, // Include basic data about creators
		'includeStructuredCreatorData' => true, //include structured data about creators
		'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories)
		'avatarSize' => '96', //avatar size
		'avatarDefault' => 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536', //defaul (gr)avatar image
		'checkImgSrcs' => true, //whether to check availability of image sources
	);

	$tei_master = new TeiApi($_SESSION, $ops);

	$pdf = new TeiPdf($tei_master);

	//header('Content-type: application/pdf');
	$pdf->write_pdf();

}

main();
die();
?>
