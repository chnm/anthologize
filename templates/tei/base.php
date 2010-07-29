<?php

error_reporting(0);

include(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');


$projectID = 867;
$tei = new TeiDom($projectID);


//header("Content-type: text/xml");
echo $tei->getTeiString();



// or $teiDom = $tei->getTeiDom();
 
die();

