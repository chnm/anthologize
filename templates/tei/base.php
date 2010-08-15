<?php


//include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom2.php');

//$tei = new TeiDom($_POST);
$tei = new TeiDom2($_POST);
//$fileName = TeiDom::getFileName($_POST);
$fileName = $tei->getFileName();
$ext = "xml";


//print_r($tei->getBodyPartMeta(0));

//print_r($tei->getBodyPartMetaEl(0, 'author'));
//print_r($tei->getBodyPartItemMeta(0, 0));


//print_r($tei->getBodyPartItemMetaEl(0, 0, 'title'));

//print_r($tei->getBodyPartItem(0, 0));

//print_r($tei->getProjectOutputParams(array('paramName'=>'page-width')));

header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=$fileName.$ext");
echo $tei->getTeiString();


die();

