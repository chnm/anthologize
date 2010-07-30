<?php

error_reporting(0);

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-pdf.php');


$projectID = 877;

$tei_dom = new TeiDom($projectID);


$pdf = new TeiPdf($tei_dom);

//header('Content-type: application/pdf');
$pdf->write_pdf();

//header("Content-type: text/xml");
//echo $tei->getTeiString();



// or $teiDom = $tei->getTeiDom();

die();
?>
