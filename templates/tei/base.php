<?php

$tei = file_get_contents(dirname( __FILE__ ) . '/teiBase.xml');

header('Content-type: application/xml');
echo $tei;
//echo "xml someday";
die();
?>