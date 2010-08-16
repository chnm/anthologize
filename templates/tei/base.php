<?php

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');


$tei = new TeiDom($_SESSION);
$fileName = TeiDom::getFileName($_SESSION);
$ext = "xml";



header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=$fileName.$ext");
echo $tei->getTeiString();


die();

