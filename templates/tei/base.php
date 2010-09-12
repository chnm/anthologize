<?php


include_once(ANTHOLOGIZE_TEIDOM_PATH);
include_once(ANTHOLOGIZE_TEIDOMAPI_PATH);

$ops = array(	'includeStructuredSubjects' => true, //Include structured data about tags and categories
		'includeItemSubjects' => true, // Include basic data about tags and categories
		'includeCreatorData' => true, // Include basic data about creators
		'includeStructuredCreatorData' => true, //include structured data about creators
		'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories)
		'avatarSize' => '96', //avatar size
		'avatarDefault' => 'http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536', //defaul (gr)avatar image
		'checkImgSrcs' => true, //whether to check availability of image sources
		);


$ops['outputParams'] = $_SESSION['tei'];

$tei = new TeiDom($_SESSION, $ops);
$api = new TeiApi($tei);


//if you want to make it a download.
$fileName = $api->getFileName();
$ext = "xml";



header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=$fileName.$ext");
echo $tei->getTeiString();


die();

