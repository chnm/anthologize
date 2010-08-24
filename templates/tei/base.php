<?php


//include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom2.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-api.php');

$tei = new TeiDom($_POST);

$api = new TeiApi($tei);
$fileName = TeiDom::getFileNameStatic($_POST);
//$fileName = $tei->getFileName();
$ext = "xml";




/*
echo $api->getProjectCopyright();
echo "<br/>";
echo $api->getProjectCopyright(true);
die();
*/

//echo $api->getSectionPartCount('body');
//print_r($api->getSectionPartMeta('body', 0));


//print_r($api->getSectionPartMetaEl('body', 0, 'author'));
//print_r($api->getSectionPartItemMeta('body', 0, 0));


//print_r($api->getSectionPartItemMetaEl('body', 0, 0, 'title'));
//print_r($api->getSectionPart)
//print_r($api->getSectionPartItemContent('body', 0, 0));

//print_r($api->getProjectOutputParams(array('paramName'=>'page-width')));


header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=$fileName.$ext");
echo $tei->getTeiString();


die();


