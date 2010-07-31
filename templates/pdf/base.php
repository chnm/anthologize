<?php

error_reporting(0);

include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-pdf.php');

$tei_dom = new TeiDom($_POST);

$pdf = new TeiPdf($tei_dom);

header('Content-type: application/pdf');
$pdf->write_pdf();


die();
?>
