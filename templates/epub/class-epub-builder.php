<?php

class EpubBuilder {

	public $tempDir;
	public $epubDir;
	public $oebpsDir;
	public $metaInfDir;
	public $outFileName;
	public $ncxXSL;
	public $opfXSL;
	public $htmlXSL;
	public $proc;
	public $tei;
	public $html;


	public function __construct($tei, $data) {
		$this->tei = $tei->dom;
		$this->createDirs();
		$this->outFileName = $tei->getFileName($_SESSION) . '.epub';
		$this->proc = new XSLTProcessor();
		$anthEpubDir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'epub' . DIRECTORY_SEPARATOR;
		$this->ncxXSL = $anthEpubDir . 'tei2ncx.xsl';
		$this->opfXSL = $anthEpubDir . 'tei2opf.xsl';

		if (is_string($data)) {

			if (file_exists($data)) {

				//$data is the path to an xslt
				$this->htmlXSL = $data;
				$this->html = $this->doProc($data, $this->tei);

			} else {
				// html needs to be a DOMDocument so we can xpath over it to fetch the images
				$this->html = new DOMDocument();
				$this->html->loadXML($data);
			}
		} elseif (get_class($data) == 'DOMDocument') {
			$this->html = $data;
		}

		$this->fetchImages();

		$this->saveContainer();
		$this->saveNCX();
		$this->saveOPF();
		$this->saveHTML();
	}

	public function fetchImages() {
		//TODO: switch to HTML based image work so arbitrary HTML can be passed in.
		$xpath = new DOMXPath($this->html);
//		$xpath->registerNamespace('html', 'http://www.w3.org/1999/xhtml');

		$srcNodes = $xpath->query("//img/@src");

		foreach ($srcNodes as $srcNode) // Iterate through images
		  {
		    // Get image url & open file

			$image_url = $srcNode->nodeValue;

			$image_filename = preg_replace('/^.*\//', '', $image_url); // Erase all but filename from URL (no directories)
			$new_filename = $this->saveImage($image_url, $image_filename);
			$srcNode->nodeValue = $new_filename;

			//TODO: sort out the danger of duplicate file names
		}

	}

	public function saveImage($image_url, $image_filename) {


		// TODO: check mimetype of image and assign generated name to file rather than derive from URL as above
		//sort out the danger of duplicate file names
		$count = 0;
		while(file_exists($image_filename)) {
			$index = strpos($image_filename, '-');
			$countPrefix = (int) substr($image_filename, $index);
			$image_filename = substr_replace($image_filename, $count, 0, $index);
		}

		$exploded = explode('?', $image_filename);
		$image_filename = $exploded[0];
		$ch = curl_init($image_url);

		$fp = fopen($this->oebpsDir . DIRECTORY_SEPARATOR . $image_filename, "w");

		// Fetch image from url & put into file

	    curl_setopt($ch, CURLOPT_FILE, $fp);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_exec($ch);
	    curl_close($ch);
	    fclose($fp);

	    return $image_filename;
	}

	public function createDirs() {

		$this->tempDir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize'
										. DIRECTORY_SEPARATOR . 'templates'
										. DIRECTORY_SEPARATOR . 'epub'
										. DIRECTORY_SEPARATOR . 'temp'
										. DIRECTORY_SEPARATOR
										. sha1(microtime()) //make sure that if two users export different project from same site, they don't clobber each other
										. DIRECTORY_SEPARATOR;

		$this->epubDir = $this->tempDir  . 'epub' ;
		$this->oebpsDir = $this->epubDir . DIRECTORY_SEPARATOR . 'OEBPS' . DIRECTORY_SEPARATOR;
		$this->metaInfDir = $this->epubDir . DIRECTORY_SEPARATOR . 'META-INF' . DIRECTORY_SEPARATOR;

		mkdir($this->tempDir, 0777, true);
		mkdir($this->epubDir, 0777, true);
		mkdir($this->oebpsDir, 0777, true);
		mkdir($this->metaInfDir, 0777, true);

	}

	public function saveContainer() {
	  $container_file_contents  = '<?xml version="1.0"?>';
	  $container_file_contents .= '<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">';
	  $container_file_contents .= '<rootfiles>';
	  $container_file_contents .= '<rootfile full-path="OEBPS/book.opf" media-type="application/oebps-package+xml"/>';
	  $container_file_contents .= '</rootfiles>';
	  $container_file_contents .= '</container>';
	  file_put_contents($this->metaInfDir . 'container.xml', $container_file_contents);
	}

	public function saveNCX() {
		$ncx = $this->doProc($this->ncxXSL, $this->tei);
		$ncx->save($this->oebpsDir . 'toc.ncx' );
	}

	public function saveOPF() {
		$opf = $this->doProc($this->opfXSL, $this->tei);
		$opf->save($this->oebpsDir . 'book.opf');
	}

	public function saveHTML() {
		$this->html->save($this->oebpsDir . 'main_content.html');
	}

	public function doProc($xsl, $dom) {
		$xslDOM = new DOMDocument();
		$xslDOM->load($xsl);
		$this->proc->importStylesheet($xslDOM);

		return $this->proc->transformToDoc($dom);
	}

	public function output() {

		$source = $this->epubDir;
		$destination = $this->tempDir . "book.epub";

	    if (is_readable($source) === true) {
	      // ZIP extension code

	      if (extension_loaded('zip') === true) {

	        $zip = new ZipArchive();
	        if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
	          $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

	          // Iterate through files & directories and add to archive object

	          foreach ($files as $file) {
				$exploded = explode(DIRECTORY_SEPARATOR, $file);
				if($exploded[count($exploded) - 1] == "." || $exploded[count($exploded) - 1] == "..") {
					continue;
				}

	            if (is_dir($file) === true) { // Create directories as they are found

	              $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
	            }
	            else if (is_file($file) === true) { // Add files as they are found

	              $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
	            }
	          }
	        }
	        else {

	          echo "Couldn't create zip file<br />";
	        }

	        $zip->close();
	      }

	      // ZLib extension code

	      elseif (extension_loaded('zlib') === true) {

	        $original_dir = getcwd(); // Remember CWD for later reset
	        chdir($source);           // Set CWD to temp area

	        // ZIP up files

	        File_Archive::extract(
	          File_Archive::read('.'),
	          File_Archive::toArchive(
	              $destination,
	              File_Archive::toFiles(),
	              'zip'
	          )
	        );

	        chdir($original_dir); // Reset CWD

	      }

	      // No ZIP compression available

	      else {

	        die("ePub requires a ZIP compression library");
	      }
	    }
	    else {

	      echo "Source content does not exist or is not readable<br />";
	    }

	 header("Content-type: application/epub+zip");
	 header("Content-Disposition: attachment; filename=" . $this->outFileName);
	 header("Pragma: no-cache");
	 header("Expires: 0");

	 readfile($destination);

	$this->cleanup();
	}

	public function cleanup($dir = false) {
		if ( ! $dir ) {
			$dir = $this->tempDir;
		} else {
			$dir = $dir . DIRECTORY_SEPARATOR;
		}

	    $files = scandir($dir);
	    array_shift($files);    // remove '.' from array
	    array_shift($files);    // remove '..' from array

	    foreach ($files as $file)
	    {
	      $file = $dir . $file;

	      if (is_dir($file))
	      {
	      	$this->cleanup($file);

	      }
	      else
	      {
	        unlink($file);
	      }
	    }
	    rmdir($dir);
	}

}
