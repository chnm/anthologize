<?php
// base.php - Controller file for PDF generator.
//
// This file is part of Anthologize.
//
// Written and maintained by Stephen Ramsay <sramsay.unl@gmail.com>
//
// Last Modified: Sat Jul 31 08:16:10 EDT 2010
//
// Copyright (c) 2010 Center for History and New Media, George Mason
// University.
//
// Anthologize is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3, or (at your option) any
// later version.
//
// Anthologize is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License
// along with Anthologize; see the file COPYING.  If not see
// <http://www.gnu.org/licenses/>.

error_reporting(0);

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-pdf.php');

$tei_dom = new TeiDom($_POST);

$pdf = new TeiPdf($tei_dom);

header('Content-type: application/pdf');
$pdf->write_pdf();


die();
?>
