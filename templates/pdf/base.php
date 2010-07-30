<?php

//error_reporting(0);

include(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
include(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-pdf.php');


$projectID = 867;
$tei_dom = new TeiDom($projectID);

$pdf = new TeiPdf($tei_dom);

header('Content-type: application/pdf');
$pdf->writePDF();

//header("Content-type: text/xml");
//echo $tei->getTeiString();



// or $teiDom = $tei->getTeiDom();

die();

