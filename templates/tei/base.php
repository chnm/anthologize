<?php


error_reporting(0);

include(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');

$projectID = 877;
$tei = new TeiDom($projectID);
//$teiDom = $tei->getTeiDom();
echo $tei->getTeiString();
die();

