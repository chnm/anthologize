<?php


include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
//include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom2.php');

$tei = new TeiDom($_POST);
//$tei = new TeiDom2($_POST);
$fileName = TeiDom::getFileName($_POST);
$fileName = $tei->getFileName();
$ext = "xml";

//echo $tei->getBodyPartCount();
//print_r($tei->getBodyPartMeta(0));
header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=$fileName.$ext");
echo $tei->getTeiString();


die();

