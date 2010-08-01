<?php


error_reporting(0);

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');


$tei = new TeiDom($_POST);
$fileName = TeiDom::getFileName($_POST);
$ext = "rtf";

header("Content-type: application/rtf");
header("Content-Disposition: attachment; filename=$fileName.$ext");


die();



?>
