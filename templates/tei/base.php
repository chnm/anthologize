<?php




include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php'); //this will eventually drop the 2 at the end
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-api.php');

$tei = new TeiDom($_SESSION);
$api = new TeiApi($tei);

//if you want to make it a download.
$fileName = $api->getFileName();
$ext = ".html";


header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=$fileName.$ext");
echo $tei->getTeiString();


die();

