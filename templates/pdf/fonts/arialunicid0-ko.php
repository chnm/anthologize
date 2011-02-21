<?php


$tcpdfFontsPath = WP_PLUGIN_DIR .
	DIRECTORY_SEPARATOR . 'anthologize' .
	DIRECTORY_SEPARATOR . 'templates' .
	DIRECTORY_SEPARATOR . 'pdf' .
	DIRECTORY_SEPARATOR . 'tcpdf' .
	DIRECTORY_SEPARATOR . 'fonts' .
	DIRECTORY_SEPARATOR ;

include($tcpdfFontsPath . 'arialunicid0.php');

$enc='UniKS-UTF16-H';
$cidinfo=array('Registry'=>'Adobe','Ordering'=>'Korea1','Supplement'=>0);
include($tcpdfFontsPath . 'uni2cid_ak12.php');


// --- EOF ---
