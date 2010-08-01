<?php

//error_reporting(0);

require_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');



$tei = new TeiDom($_POST);
$fileName = TeiDom::getFileName($_POST);
$ext = "rtf";

$bookTitle = $tei->xpath->query("//tei:titleStmt/tei:title/text()")->item(0)->wholeText;
$author = $tei->xpath->query("//tei:docAuthor/text()")->item(0)->wholeText;
$copyright = $tei->xpath->query("//tei:availability/tei:p/text()")->item(0)->wholeText;
$oneBlogTitle = $tei->xpath->query("//tei:body//tei:title/text()")->item(0)->wholeText;
$oneBlogText = $tei->xpath->query("//tei:body//html:body")->item(0)->wholeText;

/*
echo $bookTitle;
echo "<br />";
echo $author;
echo "<br />";
echo $copyright;
echo "<br />";
echo $oneBlogTitle;
echo "<br />";
echo $oneBlogText;
echo "<br />";

die();

*/


// get contents of template file
$filename = WP_PLUGIN_DIR . "/anthologize/templates/rtf/poc_template.rtf"; // future: this will be a user-uploaded template
$fp = fopen($filename, 'r'); // open for reading stream only
$output = fread($fp, filesize($filename)); //prepare the output variable
fclose ($fp);

// replace the place holders in the template with our data
// future: allow users to define things to replace, so this will not be hardcoded

$output = str_replace('[[BOOK TITLE]]', $bookTitle, $output);
$output = str_replace('[[AUTHOR NAME]]', $author, $output);
$output = str_replace('[[COPYRIGHT]]', $copyright, $output);
$output = str_replace('[[BLOG TITLE]]', $oneBlogTitle, $output);
$output = str_replace('[[BLOG CONTENT]]', $oneBlogText, $output);

//what we have to do with this is first remove these four lines from the existing $output
//\par \page
//\par [[BLOG TITLE]]
//\par
//\par [[BLOG CONTENT]]

//then what we need to do is, for each post, add to $output in a loop, as above.


//generate the headers to help a browser choose the correct application
header("Content-type: application/msword");
header("Content-Disposition: attachment; filename=$fileName.$ext");
header("Pragma: no-cache");
header("Expires: 0");

// send the generated document to the browser
echo $output;
die();
?>