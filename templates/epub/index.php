<?php

  error_reporting(0);

  /*

    Anthologize ePub generator

    1. Create directory structure in temporary ePub directory
    2. Populate ePub directory with fixed files (mimetype and container.xml)
    3. Grab all images referenced in Anthologize exchange TEI file and put into temporary ePub dir
    4. Transform Anthologize exchange TEI format data into ePub NCX, OPF, and HTML files & save into temporary ePub dir
    5. Zip up temporary ePub dir and serve it out
    6. Delete all temp stuff

  */

  require_once('Zip.php');

  define('TEI',  'http://www.tei-c.org/ns/1.0'  );
  define('HTML', 'http://www.w3.org/1999/xhtml' );
  define('ANTH', 'http://www.anthologize.org/ns');

  $plugin_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize';
  $epub_dir   = $plugin_dir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'epub';

  // echo $plugin_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php'; die();

  include_once($plugin_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');

  // Constants

  $temp_dir_name          = WP_CONTENT_DIR       . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'epub-tmp'; // Temporary area for building ZIP
  $temp_epub_dir_name     = $temp_dir_name       . DIRECTORY_SEPARATOR . 'epub_under_construction'; // ePub dir structure temp area
  $temp_epub_meta_inf_dir = $temp_epub_dir_name  . DIRECTORY_SEPARATOR . 'META-INF';
  $temp_epub_oebps_dir    = $temp_epub_dir_name  . DIRECTORY_SEPARATOR . 'OEBPS';
  $temp_epub_images_dir   = $temp_epub_oebps_dir . DIRECTORY_SEPARATOR; // . 'images';
  $temp_zip_filename      = $temp_dir_name       . DIRECTORY_SEPARATOR . 'book.epub'; // Temporary ZIP file

  $zip_download_filename  = TeiDom::getFileName($_POST) . '.epub'; // The name of the filename when it downloads

  // Set internal & output text encoding to UTF-16

  // iconv_set_encoding("internal_encoding", "UTF-16");
  // iconv_set_encoding("output_encoding",   "UTF-16");

  // Create temp directory if doesn't exist

  if (! file_exists ( $temp_dir_name ))
  {
    mkdir($temp_dir_name, 0777, true);
  }

  // Create directories in temp directory

  mkdir($temp_epub_meta_inf_dir, 0777, true);
  mkdir($temp_epub_oebps_dir,    0777, true);
  mkdir($temp_epub_images_dir,   0777, true);

  // Create & populate mimetype file

  $mimetype_filename = $temp_epub_dir_name .  DIRECTORY_SEPARATOR . "mimetype";
  $fp = fopen($mimetype_filename, "w") or die("Couldn't open temporary file for epub archive (mimetype)");
  fwrite($fp, "application/epub+zip");
  fclose($fp);

  // Create & populate container.xml file

  $container_filename = $temp_epub_dir_name .  DIRECTORY_SEPARATOR . "META-INF" .  DIRECTORY_SEPARATOR . "container.xml";
  $fp = fopen($container_filename, "w") or die("Couldn't open temporary file for epub archive (container.xml)");

  $container_file_contents  = '<?xml version="1.0"?>';
  $container_file_contents .= '<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">';
  $container_file_contents .= '<rootfiles>';
  $container_file_contents .= '<rootfile full-path="OEBPS/book.opf" media-type="application/oebps-package+xml"/>';
  $container_file_contents .= '</rootfiles>';
  $container_file_contents .= '</container>';

  fwrite($fp, $container_file_contents);
  fclose($fp);

  // Load intermediate TEI file

  $tei_data = new TeiDom($_POST);
  $teiDom = $tei_data->getTeiDom();

  // echo $tei_data->getTeiString(); die();

  // Get all images referenced in tei & copy over to image directory
  // DOM Query using xpath: http://www.exforsys.com/tutorials/php-oracle/querying-a-dom-document-with-xpath.html

  $xpath = new DOMXPath($teiDom);
  $xpath->registerNamespace('tei', TEI);
  $xpath->registerNamespace('html', HTML);
  $xpath->registerNamespace('anth', ANTH);

  $query = '//img/@src';
  $image_url_nodes = $xpath->query($query);

  foreach ($image_url_nodes as $image_url_node) // Iterate through images
  {
    // Get image url & open file

    $image_url = $image_url_node->nodeValue;
    $image_filename = preg_replace('/^.*\//', '', $image_url); // Erase all but filename from URL

    $ch = curl_init($image_url);
    $fp = fopen($temp_epub_images_dir . '/' . $image_filename, "w");

    // Fetch image from url & put into file

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);

    fclose($fp);
  }

  $xsl_html_file = $epub_dir . DIRECTORY_SEPARATOR . 'tei2html.xsl';
  $xsl_ncx_file  = $epub_dir . DIRECTORY_SEPARATOR . 'tei2ncx.xsl';
  $xsl_opf_file  = $epub_dir . DIRECTORY_SEPARATOR . 'tei2opf.xsl';

  // Load stylesheets

  $tei2html_xsl = new DOMDocument();
  $tei2html_xsl->load($xsl_html_file);

  $tei2ncx_xsl = new DOMDocument();
  $tei2ncx_xsl->load($xsl_ncx_file);

  $tei2opf_xsl = new DOMDocument();
  $tei2opf_xsl->load($xsl_opf_file);

  // Create XSLT processor

  $proc = new XSLTProcessor();

  // Import stylesheets & transform & save

  $html_filename = $temp_epub_oebps_dir . DIRECTORY_SEPARATOR . "main_content.html";
  $ncx_filename  = $temp_epub_oebps_dir . DIRECTORY_SEPARATOR . "toc.ncx";
  $opf_filename  = $temp_epub_oebps_dir . DIRECTORY_SEPARATOR . "book.opf";

  // XHTML

  $proc->importStylesheet($tei2html_xsl);
  $fp = fopen($html_filename, "w") or die("Couldn't open temporary file for epub archive (main_content.html)");
  $html = $proc->transformToXML($teiDom);
  $empty_namespace_pattern = '/\sxmlns=""\s/i';
  //$html_no_empty_namespaces = preg_replace('/x/', 'xyz', $html);
  $html_no_empty_namespaces = preg_replace('/xmlns=""/u', '', $html);
  //$html_no_empty_namespaces .= "<!-- THIS IS A NEW DOC -->";
  fwrite($fp, $html_no_empty_namespaces);
  // fwrite($fp, $proc->transformToXML($teiDom));
  fclose($fp);

  // (clean out empty namespaces using a regex)
  /*
  $string = 'April 15, 2003';
$pattern = '/(\w+) (\d+), (\d+)/i';
$replacement = '${1}1,$3';
echo preg_replace($pattern, $replacement, $string);
  */

  // NCX

  $proc->importStylesheet($tei2ncx_xsl);
  $fp = fopen($ncx_filename, "w") or die("Couldn't open temporary file for epub archive (toc.ncx)");
  fwrite($fp, $proc->transformToXML($teiDom));
  fclose($fp);

  // OPF

  $proc->importStylesheet($tei2opf_xsl);
  $fp = fopen($opf_filename, "w") or die("Couldn't open temporary file for epub archive (book.opf)");
  fwrite($fp, $proc->transformToXML($teiDom));
  fclose($fp);

  // zip up contents of temp directory into a ZIP file

  zip_it($temp_epub_dir_name, $temp_zip_filename) or die("Couldn't create ZIP archive file: '" . $temp_zip_filename . "'");

  // Serve up zip file

  header("Content-type: application/epub+zip");
  header("Content-Disposition: attachment; filename=" . $zip_download_filename);
  header("Pragma: no-cache");
  header("Expires: 0");
  readfile($temp_zip_filename);

  // Delete all contents in temp dir
  // Code derived from http://www.php.net/manual/en/class.recursiveiteratoriterator.php

  // deleteDirectoryWithContents($temp_epub_dir_name);
  // unlink($temp_zip_filename);

  die();  // END

  // Function to take a source directory and zip it up into a destination archive
  // Code derived from http://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php

  function zip_it($source, $destination)
  {
    $source = realpath($source);
    if (is_readable($source) === true)
    {
      if (extension_loaded('zip') === true)
      {
        $zip = new ZipArchive();

        if ($zip->open($destination, ZIPARCHIVE::CREATE) === true)
        {
          $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

          // Iterate through files & directories and add to archive object

          foreach ($files as $file)
          {
            if (is_dir($file) === true) // Create directories as they are found
            {
              $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true) // Add files as they are found
            {
              $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
          }
        }
        else
        {
          echo "Couldn't create zip file<br />";
        }
        return $zip->close();
      } 
      elseif (extension_loaded('zlib') === true) 
      {
        $files_to_zip = Array();
        $zipper = new Archive_Zip($destination);

        $source = realpath($source);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        // Iterate through files & directories and add to archive object
        foreach ($files as $file)
        {
          $file = realpath($file);

          if (is_dir($file) === true) // Create directories as they are found
          {
            $files_to_zip[] = $file . '/';
          }
          else if (is_file($file) === true) // Add files as they are found
          {
            $files_to_zip[] = $file;
          }
        }
        if (count($files_to_zip) > 0) {
          $zipper->create($files_to_zip, Array('remove_path' => $source . '/'));
        }

        if (false !== $zip_result) {
          return true;
        }
        else
        {
          echo "Couldn't create zip file<br />";
        }
      }
      else
      {
        echo "Zip and zlib extensions not installed<br />";
      }
    } else {
      echo "Source content does not exist or is not readable<br />";
    }

    return false;
  }

  // Delete a directory and its contents
  // Derived from code at http://nashruddin.com/Remove_Directories_Recursively_with_PHP

  function deleteDirectoryWithContents ($dir)
  {
    // DISABLED UNTIL TESTED

    $files = scandir($dir);
    array_shift($files);    // remove '.' from array
    array_shift($files);    // remove '..' from array

    foreach ($files as $file)
    {
      $file = $dir . '/' . $file;

      if (is_dir($file))
      {
        deleteDirectoryWithContents($file);
      }
      else
      {
        unlink($file);
      }
    }
    rmdir($dir);
  }

?>
