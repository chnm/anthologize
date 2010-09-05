<?php




include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php'); //this will eventually drop the 2 at the end
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-api.php');

$ops = array(	'includeStructuredSubjects' => true, //Include structured data about tags and categories
		'includeItemSubjects' => true, // Include basic data about tags and categories
		'includeCreatorData' => true, // Include basic data about creators
		'includeStructuredCreatorData' => true, //include structured data about creators
		'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories)
		'avatarSize' => '96', //avatar size
		'avatarDefault' => 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536', //defaul (gr)avatar image
		'checkImgSrcs' => true, //whether to check availability of image sources

		);

$tei = new TeiDom($_SESSION);
$api = new TeiApi($tei);




//if you want to make it a download.
$fileName = $api->getFileName();
$ext = "xml";



header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=$fileName.$ext");
echo $tei->getTeiString();


die();

