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

        $this->tei = $tei;
        $this->createDirs();
        $this->outFileName = $tei->getFileName( anthologize_get_session() ) . '.epub';
        $this->proc = new XSLTProcessor();
        $anthEpubDir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'epub' . DIRECTORY_SEPARATOR;
        $this->ncxXSL = $anthEpubDir . 'tei2ncx.xsl';
        $this->opfXSL = $anthEpubDir . 'tei2opf.xsl';

        //dig up the selected cover image
		$cover_item = $this->tei->xpath->query("//anth:param[@name = 'cover']")->item(0);
		if ( $cover_item && isset( $cover_item->nodeValue ) ) {
			$this->coverImage = $cover_item->nodeValue;
		} else {
			$this->coverImage = 'none';
		}

        $this->localizeLinks();

        if (is_string($data)) {
            if (file_exists($data)) {
                //$data is the path to an xslt
                $this->htmlXSL = $data;
                $this->html = $this->doProc($data, $this->tei->dom);

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

        //do any final processing, especially things like rewriting the ToC etc that need to happen
        //after everything is done
        $this->finish();
    }

    public function fetchImages() {
        //TODO: switch to HTML based image work so arbitrary HTML can be passed in.
        //will require adjusting the XSL so that it does not remove the full URL
        $xpath = $this->tei->xpath;

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

        $upload_dir = wp_upload_dir( null, false );
        $tempDir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'anthologize-temp';
        if(! is_dir($tempDir)) {
            mkdir($tempDir);
        }

        $this->tempDir = 	$tempDir .
                    DIRECTORY_SEPARATOR .
                    sha1(microtime()) . //make sure that if two users export different project from same site, they don't clobber each other
                    DIRECTORY_SEPARATOR;

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
        $ncx = $this->doProc($this->ncxXSL, $this->tei->dom);
        $this->rewriteTOC($ncx);
        $ncx->save($this->oebpsDir . 'toc.ncx' );
    }

    public function saveOPF() {
        $opf = $this->doProc($this->opfXSL, $this->tei->dom);
        // overwrite the bad metadata
        //TODO address this in the core xsl
        $xpath = new DOMXPath($opf);
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $titleNode = $xpath->query("//dc:title")->item(0);

        $teiTitleNL = $this->tei->xpath->query("//tei:front/tei:head/tei:bibl/tei:title[@type='main']");
        $teiTitle = $teiTitleNL->item(0);

        $titleNode->nodeValue = trim($teiTitle->nodeValue);
        $teiCreatorNode = $this->tei->xpath->query("//tei:front/tei:head/tei:bibl/tei:author[@role='projectCreator']")->item(0);
/*
 * this is being hard-coded in the opf xsl
        $creatorNode = $xpath->query("//dc:creator")->item(0);
        $creatorNode->nodeValue = trim($teiCreatorNode->nodeValue);
*/
        //add a cover image, if it is set
        if($this->coverImage != 'none') {
            //add the meta element
            $metadataNode = $opf->getElementsByTagName('metadata')->item(0);
            $coverNode = $opf->createElement('meta');
            $coverNode->setAttribute('name', 'cover');
            $coverNode->setAttribute('content', 'cover');
            $metadataNode->appendChild($coverNode);

            //add to the manifest
            $manifestNode = $opf->getElementsByTagName('manifest')->item(0);
            $coverItemNode = $opf->createElement('item');
            $coverItemNode->setAttribute('href', 'cover.jpg');
            $coverItemNode->setAttribute('id', 'cover');
            $coverItemNode->setAttribute('media-type', 'image/jpeg');
            $manifestNode->appendChild($coverItemNode);
            //copy the image over to the epub tmp dir
            $coverImgPath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'epub' . DIRECTORY_SEPARATOR . 'covers' . DIRECTORY_SEPARATOR . $this->coverImage;
            copy($coverImgPath, $this->oebpsDir . 'cover.jpg');
        }
        $opf->save($this->oebpsDir . 'book.opf');
    }

    public function saveHTML() {
        $this->html->save($this->oebpsDir . 'main_content.html');
    }

    public function doProc($xsl, $dom) {
        $xslDOM = new DOMDocument();
        $xslDOM->load($xsl);

        $do_colophon = isset( $this->tei->outputParams['colophon'] ) && 'on' == $this->tei->outputParams['colophon'] ? 'on' : false;
        $this->proc->setParameter( '', 'doColophon', $do_colophon );

        $this->proc->importStylesheet($xslDOM);

        return $this->proc->transformToDoc($dom);
    }

    public function output() {

        $source = $this->epubDir;
        $destination = $this->tempDir . "book.epub";

        if (is_readable($source) === true) {
          // ZIP extension code

          if (extension_loaded('zip') === true) {
            // make the archive first
            // EPUB wants the first file to be mimetype, and not compressed. PHP can't do this
            // This fancy trick came from http://stackoverflow.com/questions/3142810/adding-a-file-to-a-zip-uncompressed-with-php
            file_put_contents($destination, base64_decode("UEsDBAoAAAAAAOmRAT1vYassFAAAABQAAAAIAAAAbWltZXR5cGVhcHBsaWNhdGlvbi9lcHViK3ppcFBLAQIUAAoAAAAAAOmRAT1vYassFAAAABQAAAAIAAAAAAAAAAAAIAAAAAAAAABtaW1ldHlwZVBLBQYAAAAAAQABADYAAAA6AAAAAAA="));
            $zip = new ZipArchive();
            // open archive
            if ($zip->open($destination)) {
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
          //@TODO: figure out how to use the same trick as above for the mimetype file
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

    protected function finish() {


    }

    //TODO: move this into the XSLT
    protected function rewriteTOC($tocDOM) {

        //$tocDOM = new DOMDocument();
        //$tocDOM->load($this->oebpsDir . 'toc.ncx');

        //remove <navMap>
        //change depth?

        $navMap = $tocDOM->getElementsByTagName('navMap')->item(0);
        while($navMap->childNodes->length != 0 ) {
            $navMap->removeChild($navMap->firstChild);
        }
        //$parts = $htmlXPath->query("//div[@id='body']/div[@class='part']");
        $parts = $this->tei->xpath->query("//tei:body/tei:div[@type='part']");


        // for jdh, bring in the tei api so I can dig up the authors
        require_once(ANTHOLOGIZE_TEIDOMAPI_PATH);
        $api = new TeiApi($this->tei);

        $playOrder = 0;
        for($partN = 0; $partN < $parts->length; $partN++) {

            $part = $parts->item($partN);
            $title = $part->firstChild->firstChild->textContent; //shitty practice, I know
            $partNavPoint = $this->newNavPoint("body-$partN", $title, $tocDOM);
            $partNavPoint = $navMap->appendChild($partNavPoint);
            $partNavPoint->setAttribute('playOrder', $playOrder);
            $playOrder++;
            //set playorder on $partNavPoint
            $navMap->appendChild($partNavPoint);
            $items = $this->tei->xpath->query("tei:div[@type='libraryItem']", $part);
            for($itemN = 0; $itemN < $items->length; $itemN++) {
                $item = $items->item($itemN);

                $author = $api->getSectionPartItemAssertedAuthor('body', $partN, $itemN);
                $itemTitle = $item->firstChild->firstChild->textContent; //shitty practice, I know
                $itemNavPoint = $this->newNavPoint("body-$partN-$itemN", $itemTitle . " by $author", $tocDOM);

                //set playOrder
                //append where it goes
                //lets try this
                $itemNavPoint->setAttribute('playOrder', $playOrder);
                $playOrder++;
                $partNavPoint->appendChild($itemNavPoint);
            }

        }

    }

    protected function newNavPoint($id, $label, $tocDOM) {
        $label = htmlspecialchars($label);
        $navPoint = $tocDOM->createElement('navPoint');
        $navPoint->setAttribute('id', $id);
        $navLabelNode = $tocDOM->createElement('navLabel');
        $text = $tocDOM->createElement('text', $label);
        $navLabelNode->appendChild($text);
        $navPoint->appendChild($navLabelNode);
        $content = $tocDOM->createElement('content');
        $content->setAttribute('src', "main_content.html#$id");
        $navPoint->appendChild($content);
        return $navPoint;
    }

    private function localizeLinks() {
//TODO: this will likely play hell with links directly to anchor!
        $test = substr(get_bloginfo('url'),7);
          $links = $this->tei->xpath->query("//a[contains(@href, '$test' )]");

          foreach($links as $link) {
              $guid = $link->getAttribute('href');

              $targetGuidNL = $this->tei->xpath->query("//tei:ident[@type = 'permalink'][ . = '$guid']");

              if($targetGuidNL->length == 0 ) {
                  //I hate the problem of links and trailing slashes
                  //if length is zero, see if including the slash produces matches
                  $targetGuidNL = $this->tei->xpath->query("//tei:ident[@type = 'permalink'][ . = '$guid/']");
              }

            if($targetGuidNL->length != 0) {
                $item = $this->tei->getParentItem($targetGuidNL->item(0));
                  $link->setAttribute('href', '#' . $this->tei->getId($item));
            }

          }
    }
}
