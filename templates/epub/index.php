<?php

  //error_reporting(0);

  echo "TEST";
  die;

  include(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');

  $projectID = 867;
  $tei = new TeiDom($projectID);
  $teiDom = $tei->getTeiDom();

  //echo WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php';
  //die();

  $xslDom = new DOMDocument();
  $xslDom->load("tei2html.xsl");

  $proc = new XSLTProcessor();
  $proc->importStylesheet($xslDom);

  // header("Content-type: application/xml");
  // echo $proc->transformToXML($xmlDom);

  die();

?>