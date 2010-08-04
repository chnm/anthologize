<?php


//PMJ--I don't understand why the encoding done in class-tei-dom isn't coming through, but this seems to work

function convertSmartQuotes($string)
{
  $search = array(chr(0xe2) . chr(0x80) . chr(0x98),
                  chr(0xe2) . chr(0x80) . chr(0x99),
                  chr(0xe2) . chr(0x80) . chr(0x9c),
                  chr(0xe2) . chr(0x80) . chr(0x9d),
                  chr(0xe2) . chr(0x80) . chr(0x93),
                  chr(0xe2) . chr(0x80) . chr(0x94));

    $replace = array("'",
                     "'",
                     '"',
                     '"',
                     '-');


  return str_replace($search, $replace, $string);
}


error_reporting(0);

require_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');


$tei = new TeiDom($_POST);
$fileName = TeiDom::getFileName($_POST);
$ext = "rtf";

$bookTitle = convertSmartQuotes($tei->xpath->query("//tei:titleStmt/tei:title/text()")->item(0)->wholeText);
$author = convertSmartQuotes($tei->xpath->query("//tei:docAuthor/text()")->item(0)->wholeText);
$copyright = convertSmartQuotes($tei->xpath->query("//tei:availability/tei:p/text()")->item(0)->wholeText);


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

$libraryItems = $tei->xpath->query("//tei:div[@type='libraryItem']");

$subOutput = "";
for($i=0; $i<$libraryItems->length; $i++) {
	$title = convertSmartQuotes($tei->xpath->query("tei:head/tei:title/text()", $libraryItems->item($i))->item(0)->wholeText);
  $htmlContent = convertSmartQuotes($tei->xpath->query("body", $libraryItems->item($i))->item(0)->textContent);
  $subOutput .= "\par $title";
  $subOutput .= "\par";
  $subOutput .= "\par $htmlContent";
  $subOutput .= "\par";
  $subOutput .= "\par";

}

$replaceChunk = "[[BLOG CONTENT]]";

$output = str_replace($replaceChunk, $subOutput, $output);


//generate the headers to help a browser choose the correct application
header("Content-type: application/rtf");
header("Charset=UTF-8");
header("Content-Disposition: attachment; filename=$fileName.$ext");
header("Pragma: no-cache");
header("Expires: 0");

// send the generated document to the browser
echo $output;
die();
?>