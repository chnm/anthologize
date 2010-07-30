<?php

error_reporting(0);

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-pdf.php');


<<<<<<< HEAD
$projectID = 865;
=======
$projectID = 877;
>>>>>>> b30e025455179e6b9f60116a9ac235910829f35c
$tei_dom = new TeiDom($projectID);


$pdf = new TeiPdf($tei_dom);

//header('Content-type: application/pdf');
$pdf->write_pdf();

//header("Content-type: text/xml");
//echo $tei->getTeiString();



// or $teiDom = $tei->getTeiDom();

die();
?>
