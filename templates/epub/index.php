<?php

  /*
   
    Anthologize ePub generator
    
    1. Create directory structure in temporary ePub directory
    2. Populate ePub directory with fixed files (mimetype and container.xml)
    3. Grab all images referenced in Anthologize exchange TEI file and put into temporary ePub dir
    4. Transform Anthologize exchange TEI format data into ePub NCX, OPF, and HTML files & save into temporary ePub dir
    5. Zip up temporary ePub dir and serve it out
    6. Delete all temp stuff
   
  */
    
  $plugin_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize';
  $epub_dir   = $plugin_dir . DIRECTORY_SEPARATOR . 'templates';
  
  // echo $plugin_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php'; die();
  
  include_once($plugin_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
  
  // Constants

  $temp_dir_name          = $plugin_root . DIRECTORY_SEPARATOR . 'temp'; // Does this need to be mapped??
  $temp_epub_dir_name     = $temp_dir_name       . DIRECTORY_SEPARATOR . 'epub';
  $temp_epub_meta_inf_dir = $temp_epub_dir_name  . DIRECTORY_SEPARATOR . 'META-INF';
  $temp_epub_oebps_dir    = $temp_epub_dir_name  . DIRECTORY_SEPARATOR . 'OEBPS';
  $temp_epub_images_dir   = $temp_epub_oebps_dir . DIRECTORY_SEPARATOR . 'images';
  $temp_zip_filename      = $temp_dir_name       . DIRECTORY_SEPARATOR . 'book.zip';

  // echo realpath(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize . "$temp_dir_name) . "<br />" . $temp_epub_dir_name . "<br />";
  // include_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-tei-dom.php');
  
  die();

/*
  // Create directories in temp directory
  
  mkdir($temp_epub_meta_inf_dir, 0777, true);
  mkdir($temp_epub_oebps_dir,    0777, true);
  mkdir($temp_epub_images_dir,   0777, true);
  
  // Create & populate mimetype file
  
  $mimetype_filename = $temp_epub_dir_name . "/mimetype";
  $fp = fopen($mimetype_filename, "w") or die("Couldn't open temporary file for epub archive (mimetype)");
  fwrite($fp, "application/epub+zip");
  fclose($fp);
  
  // Create & populate container.xml file

  $container_filename = $temp_epub_dir_name . "/META-INF/container.xml";
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
  
  $teiDom = $tei->getTeiDom($_POST);
  
  // Get all images referenced in tei & copy over to image directory
  // DOM Query using xpath: http://www.exforsys.com/tutorials/php-oracle/querying-a-dom-document-with-xpath.html
  
  $xpath = new DOMXPath($teiDom);
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
  
  // Load stylesheets
  
  $tei2html_xsl = new DOMDocument();
  $tei2html_xsl->load("tei2html.xsl");
  
  $tei2ncx_xsl = new DOMDocument();
  $tei2ncx_xsl->load("tei2ncx.xsl");
  
  $tei2opf_xsl = new DOMDocument();
  $tei2opf_xsl->load("tei2opf.xsl");
  
  // Create XSLT processor
  
  $proc = new XSLTProcessor();
  
  // Import stylesheets & transform & save
  
  // XHTML
  
  $proc->importStylesheet($tei2html_xsl);
  $html_filename = $temp_epub_dir_name . "/META-INF/main_content.html";
  $fp = fopen($mimetype_filename, "w") or die("Couldn't open temporary file for epub archive (main_content.html)");
  fwrite($fp, $proc->transformToXML($teiDom));
  fclose($fp);
  
  // NCX
  
  $proc->importStylesheet($tei2ncx_xsl);
  $proc->transformToXML($teiDom);
  $ncx_filename = $temp_epub_dir_name . "/META-INF/toc.ncx";
  $fp = fopen($ncx_filename, "w") or die("Couldn't open temporary file for epub archive (toc.ncx)");
  fwrite($fp, $proc->transformToXML($teiDom));
  fclose($fp);
  
  // OPF
  
  $proc->importStylesheet($tei2opf_xsl);
  $proc->transformToXML($teiDom);
  $opf_filename = $temp_epub_dir_name . "/META-INF/book.opf";
  $fp = fopen($opf_filename, "w") or die("Couldn't open temporary file for epub archive (book.opf)");
  fwrite($fp, $proc->transformToXML($teiDom));
  fclose($fp);
  
  // zip up contents of temp directory into a ZIP file

  Zip($temp_epub_dir_name, $temp_zip_filename) or die "Couldn't create ZIP archive file: " . $temp_zip_filename;
  
  // Serve up zip file
  
  header("Content-type: application/xml");
  echo file_get_contents($temp_zip_filename);
  

  // Delete all contents in temp dir
  // Code derived from http://www.php.net/manual/en/class.recursiveiteratoriterator.php
  
  deleteDirectoryWithContents($temp_dir_name);
  //  $dir = '/home/nash/tmp';
  // rmdir_recursive($dir);

  
  // END
  
  die();

  // Function to take a source directory and zip it up into a destination archive
  // Code derived from http://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php

  function Zip($source, $destination) 
  {
    if (extension_loaded('zip') === true)
    {
      if (file_exists($source) === true)
      {
        $zip = new ZipArchive();
        
        if ($zip->open($destination, ZIPARCHIVE::CREATE) === true)
        {
          $source = realpath($source);
          $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
        
          // Iterate through files & directories and add to archive object
        
          foreach ($files as $file)
          {
            $file = realpath($file);
          
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
        return $zip->close();
      }
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
        echo "rm dir " . $file . "\n"; // DIAGNOSTIC
        // DISABLED
        // rmdir($file); 
      }
      else
      {
        echo "unlink " . $file . "\n"; // DIAGNOSTIC
        // DISABLED
        // unlink($file);
      }
    }
    rmdir($dir);
  }
  */

?>
