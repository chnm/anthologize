<?php
/**
* base.php - Controller file for PDF generator.
*
* This file is part of Anthologize.
*
* @author Stephen Ramsay <sramsay.unl@gmail.com> for the Anthologize
* project @link http://www.anthologize.org/.
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
* along with Anthologize; see the file COPYING.  If not see
* @link http://www.gnu.org/licenses/.
*
* @package anthologize
*/

//error_reporting(0);

$class_tei = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'class-tei.php';
$class_pdf = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'class-pdf.php';

require_once($class_tei);
require_once($class_pdf);

function main() {

	$tei_master = new TeiMaster();

	$pdf = new TeiPdf($tei_master);

	//header('Content-type: application/pdf');
	$pdf->write_pdf();

}	

main();
die();
?>
