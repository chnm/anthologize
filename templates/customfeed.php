<?php
/**
 * Temporary "epub" output - actually just XHTML
 */



  if (!class_exists('XSLTProcessor', false))
    die ('Custom feed export requires XSL support');

  $rssUrl = get_bloginfo( 'rss2_url' );
  $rssXml = file_get_contents($rssUrl);

  $xslDoc = new DOMDocument();
  $xslDoc->load( dirname( __FILE__ ) . "/pr_rss2html.xsl");

  $xmlDoc = new DOMDocument();
  $xmlDoc->loadXML($rssXml);

  $proc = new XSLTProcessor();
  $proc->importStylesheet($xslDoc);
  echo $proc->transformToXML($xmlDoc);
  die();
  //echo $rssXml;

?>
