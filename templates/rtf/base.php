<?php
/**
* base.php - Controller file for RTF generator.
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

include_once(ANTHOLOGIZE_TEIDOM_PATH);
include_once(ANTHOLOGIZE_TEIDOMAPI_PATH);
$anthPluginDir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR;
require_once($anthPluginDir . 'includes'  . DIRECTORY_SEPARATOR . 'class-anthologizer.php');
require_once($anthPluginDir . 'templates' . DIRECTORY_SEPARATOR . 'rtf' . DIRECTORY_SEPARATOR . 'class-rtf-anthologizer.php' );

$ops = array(
  'includeStructuredSubjects' => false, //Include structured data about tags and categories
  'includeItemSubjects' => false, // Include basic data about tags and categories
  'includeCreatorData' => false, // Include basic data about creators
  'includeStructuredCreatorData' => false, //include structured data about creators
  'includeOriginalPostData' => false, //include data about the original post (true to use tags and categories)
  'checkImgSrcs' => true, //whether to check availability of image sources
  'linkToEmbeddedObjects' => true,
  'indexSubjects' => false,
  'indexCategories' => false,
  'indexTags' => false,
  'indexAuthors' => false,
  'indexImages' => false
);

$tei = new TeiDom( anthologize_get_session(), $ops );
$api = new TeiApi($tei);
$rtfer = new RtfAnthologizer($api);
$rtfer->output();
die();

?>
