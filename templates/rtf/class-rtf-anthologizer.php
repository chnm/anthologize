<?php

/*
 * class RtfAnthologizer
 * @package Anthologize
 * @subpackage RTF-Template
 *
 * Produces RTF from Anthologize TEI
 *
 */


/* CONSTRUCTOR

	function __construct($api) {
		$this->api = $api;
		$this->init();
		$this->appendFront();
		$this->appendBody();
		$this->appendBack();
		$this->finish();
	}
	
*/


class RtfAnthologizer extends Anthologizer {

  public $baseFontSize = 24; // In TWIPS (RTF units) 

	public $partH = '16'; // Part Header font size in points
	public $itemH = '12'; // Item Header font size in points

  public $rtf = array(); // Holds RTF code snippets
  // public $tempImageFilename = $upload_dir_array['basedir'] . DIRECTORY_SEPARATOR . 'anthologize-rtf-temp-img.png';

  public $HTML_PREVIEW = false; // For debugging: show output in browser

  public function rtfInit() {

    $this->rtf = array(
    
      'init_rtf_header' => "{\\rtf1\\ansi\\ansicpg1252\\deff0\\deflang1033\n",
      'title_page_global' => array("\n{\\qc", '}'),
 //   'cover_title' => '\sb120\sa120 {\b\fs' . floor($baseFontSize * 2),
      'title_page_title' => '\par\sb120\sa120 {\b\fs48 ',
      'title_page_creator' => '\par\sb120\sa120 {\b\fs36 ',
      'title_page_license' => '\par { ',
      
      'dedication_page_global' => "\n\\page {",
      'dedication_page_title' => '\sb120\sa120 {\qc\b\fs48 ',
      
      'acknowledgements_page_global' => array("\n\\page {", '}'),
      'acknowledgements_page_title' => '\sb120\sa120 {\qc\b\fs48 ',
      
      'new_line' => '\line ',
      'paragraph' => '\par ',
      'page_break' => '\page ',
      
      'center_text' => '{\qc ',
      'left_align_text' => '{\ql ',
      'right_align_text' => '{\rl ',
      
      // HTML element translations
      
      'p' => array('\par ', ''),
      'div' => array('\pard \sa60 ', '\par '),
      
      // Stuff that looks like italics
      'em' => array('{\i ', '}'),
      'i' => array('{\i ', '}'),
      'dfn' => array('{\i ', '}'),
      'var' => array('{\i ', '}'),
      'cite' => array('{\i ', '}'),
      
      // Stuff that looks bold
      'strong' => array('{\b ','}'),
      'b' => array('{\b ','}'),
      
      // Underline (who uses <u> anymore?)
      'u' => array('{\ul ','}'),
      
      // Stuff that has a monospace font
      'tt' => array('{\f1 ','}'),
      'code' => array('{\f1 ','}'),
      'samp' => array('{\f1 ','}'),
      'kbd' => array('{\f1 ','}'),
      
      // Blockquote
      'blockquote' => array('{\li800','}'),
      
      // Line break
      'br' => array('\line ', ''),
      
      // Hyperlink
      'a' => array( ' {\field{\*\fldinst {\cs1\ul\cf2 HYPERLINK "', // Follow with URL
                    '"}}{\fldrslt {\cs1\ul\cf2 ', // Follow with text
                    '}}}' ),
      
      // Image
      'img' => array('{\pict','}'),
      'img_type' => array('png' => '\pngblip', 'jpeg' => '\jpegblip'),
      
//    '' => array('{\b ','}'),
    );
    
    // More HTML element translations
    
    // Headers
    $headerPrefix = '\sb120\sa120 {\b\fs';
    $headerSuffix = '}\par ';
    $this->rtf['h1'] = array($headerPrefix . floor($this->baseFontSize * 2)   . ' ', $headerSuffix);
    $this->rtf['h2'] = array($headerPrefix . floor($this->baseFontSize * 1.5) . ' ', $headerSuffix);
    $this->rtf['h3'] = array($headerPrefix . floor($this->baseFontSize * 1.2) . ' ', $headerSuffix);
    $this->rtf['h4'] = array($headerPrefix . floor($this->baseFontSize * 1.0) . ' ', $headerSuffix);
    $this->rtf['h5'] = array($headerPrefix . floor($this->baseFontSize * 0.9) . ' ', $headerSuffix);
    $this->rtf['h6'] = array($headerPrefix . floor($this->baseFontSize * 0.8) . ' ', $headerSuffix);
    $this->rtf['h7'] = array($headerPrefix . floor($this->baseFontSize * 0.7) . ' ', $headerSuffix);
    
    $this->rtf['part_head'] = $this->rtf['h1'];
    $this->rtf['item_head'] = $this->rtf['h2'];
  }

  public function init() {
	
    $this->rtfInit();
    	
    $this->output = ""; // Holds RTF code output
    $this->output .= $this->rtf['init_rtf_header'];
    
    // Font table definitions
    // TODO: Parametrize fonts
    
    $this->output .= '{\fonttbl';
    $this->output .= '{\f0\fswiss\fcharset0 Times New Roman}';
    $this->output .= '{\f1\fswiss\fcharset0 Courier New}';
    $this->output .= '{\f3\fnil\fcharset2 Symbol;}';
    $this->output .= "}\n";
    
    // TODO: DOC INFO
    
    // Colour table
    
    $this->output .= '{\colortbl;\red0\green0\blue0;\red0\green0\blue255;'
                      . '\red0\green255\blue0;\red255\green0\blue0;'
                      . '\red255\green255\blue255}' . "\n";
    // TODO: Layout code
    
    // Header and footer code
    
    $this->output .= '\pgnstart1'; // Page start number 
    $this->output .= '{\footer\pard\fs' . $this->baseFontSize
      . '\qc Page\~{\field{\*\fldinst PAGE}}/{\field{\*\fldinst NUMPAGES}}\~-\~'
      . '{\field{\*\fldinst DATE \\@ "dd/MM/yyyy" }}\~-\~'
      . '{\field{\*\fldinst TIME \\@ "hh:mm:ss" }}\par }';
	}

  // Add the front matter

	public function appendFront() {

		// Title and author page
		
		$creator = $this->api->getProjectCreator(false, false);
		$book_title = $this->api->getProjectTitle(true);
		
		$this->output .= $this->rtf['title_page_global'][0]; // Start title page

    $this->output .= $this->rtf['title_page_title'] . $book_title . '}';
		$this->output .= $this->rtf['title_page_creator'] . $creator . '}';
		$year = substr( $this->api->getProjectPublicationDate(), 0, 4 );
		$this->output .= $this->rtf['title_page_license'] . $this->api->getProjectCopyright(false, false) . ' - ' . $year . '}';

		$this->output .= $this->rtf['title_page_global'][1]; // End title page
    
    // Dedication page 
    
		$dedication = $this->api->getSectionPartItemContent('front', 0, 0);
		
		if ($dedication) {

      $this->output .= $this->rtf['dedication_page_global']; // Start dedication page
      
      $titleNode = $this->api->getSectionPartItemTitle('front', 0, 0, true);
			$title = $titleNode->nodeValue;
      $this->output .= $this->rtf['dedication_page_title'] . $title . '}';
      $this->convertHTMLtoRTF($dedication);
      $this->output .= '}'; // End dedication page
    }

		// Acknowledgements page
    
    $acknowledgements = $this->api->getSectionPartItemContent('front', 0, 1);
    
    if ($acknowledgements) {
    
      $titleNode = $this->api->getSectionPartItemTitle('front', 0, 1, true);
      $title = $titleNode->nodeValue;
      
      $this->output .= $this->rtf['acknowledgements_page_global'][0]; // Start acknow page
      
      $this->output .= $this->rtf['acknowledgements_page_title'] . $title . '}';
      $this->convertHTMLtoRTF($acknowledgements);
      $this->output .= $this->rtf['acknowledgements_page_global'][1]; // End acknow page
    }
    
    // TEST Anthologize logo
    /*
    $logo_file =  WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' 
                                . DIRECTORY_SEPARATOR . 'templates' 
                                . DIRECTORY_SEPARATOR . 'rtf' 
                                . DIRECTORY_SEPARATOR . 'anthologize_logo.png';
                                
    $this->output .= $this->getImageRTF($logo_file, 0, 0); */
	}


  // Given a URL and display dimensions, return RTF code for an image
  // TODO: implement image dimensions based on <img> tag atts (or CSS classes).

  protected function getImageRTF ($imgUrl, $imgDisplayWidth, $imgDisplayHeight) {
    
    list($imgWidth, $imgHeight, $imgType) = getimagesize($imgUrl);
    
    // Amongst the 'native' formats of the web, RTF only handles PNGs and JPEGs
    
    if ($imgType == IMAGETYPE_JPEG || $imgType == IMAGETYPE_PNG) {
    
      // $blipType = ($imgType == IMAGETYPE_JPEG) ? '\jpgblip' : '\pngblip'; 
      $blipType = '\pngblip';
    
      $rtfCode =  "{\\pict\n"
                  . '\picscalex100\picscaley100'
                  . '\picw' . $imgWidth . '\pich' . $imgHeight 
                  . $blipType . "\n" 
                  . $this->getImageHexCode($imgUrl)
                  . "}\n";          
    } 
    else { $rtfCode = ''; } // TODO: Image conversion routine goes here ...?
    
    /*
    
    Image conversion resources:
    
    Loading GIFs
    http://www.php.net/manual/en/function.imagecreatefromgif.php
    Converting formats
    http://stackoverflow.com/questions/755781/convert-jpg-image-to-gif-png-bmp-format-using-php
    
    */
    
    return $rtfCode;
  }

  public function appendBody() {

    $partsCount = $this->api->getSectionPartCount('body');
    
    for ($partNo = 0; $partNo < $partsCount; $partNo++) {
      $this->appendPart('body', $partNo);
    }
	}

	public function appendBack() {
	
    $partsCount = $this->api->getSectionPartItemCount('back');

    for ($partNo = 0; $partNo < $partsCount; $partNo++) {
      $this->appendPart('back', $partNo);
    }
    
	/*
		$this->output->startPageGroup();
		$this->output->setPrintHeader(true);
		$partsCount = $this->api->getSectionPartItemCount('back');
		//echo $partsCount;
		//die();
		for($partNo = 0; $partNo < $partsCount; $partNo++) {
			$this->appendPart('back', $partNo);
		}
		*/
	}

  public function appendPart($section, $partNo) {

    $titleNode = $this->api->getSectionPartTitle($section, $partNo, true);
    $title = isset( $titleNode->textContent ) ? $titleNode->textContent : '';
    
    $this->output .= "\n"; // Carriage return for code readability
    
    // Add page break (if user-option says so) 
    
		if ($partNo == 0 || $this->api->getProjectOutputParams('break-parts') == 'on') { 
		  $this->output .= $this->rtf['page_break']; 
	  }
	  
		// Add the header info
		
		$this->appendPartHead($section, $partNo);
		
		// Loop the items and append
		
    $itemsCount = $this->api->getSectionPartItemCount($section, $partNo);
    
    for ($itemNo = 0; $itemNo < $itemsCount; $itemNo++) {
      $this->appendItem($section, $partNo, $itemNo);
    }
	}


  public function appendPartHead($section, $partNo) {

    $titleNode = $this->api->getSectionPartTitle($section, $partNo, true);
    $title = isset( $titleNode->textContent ) ? $titleNode->textContent : '';
    
    $this->output .=  $this->rtf['part_head'][0]
                      . $title
                      . $this->rtf['part_head'][1];
  }

  public function appendItem($section, $partNo, $itemNo) {
    
    $titleNode = $this->api->getSectionPartItemTitle($section, $partNo, $itemNo, true);
    $title = isset( $titleNode->textContent ) ? $titleNode->textContent : '';
    // $this->set_header(array('string'=>$title)); WHAT IS THIS ?
    
    $this->output .= "\n"; // Carriage return for code readability
    
    // Page break
    
    if ( ($this->api->getProjectOutputParams('break-items') == 'on') && $itemNo != 0 ) {
      $this->output .= $this->rtf['page_break'];
    }
    
    if ($section == 'body') {
      // $this->output->Bookmark($title, 1);
      // MAY NEED THIS FOR TOC LATER
    }

    $this->output .= "\n";
    $this->appendItemHead($section, $partNo, $itemNo);
    
    // Append the item content
    
    $this->output .= $this->writeItemContent($section, $partNo, $itemNo);
	}

  protected function writeItemContent($section, $partNo, $itemNo) {
  
    $content = parent::writeItemContent($section, $partNo, $itemNo);
    return $this->convertHTMLtoRTF($content);
  }

  public function appendItemHead($section, $partNo, $itemNo) {
  
    $titleNode = $this->api->getSectionPartItemTitle($section, $partNo, $itemNo, true);
    $title = isset( $titleNode->textContent ) ? $titleNode->textContent : '';
    
    $this->output .= $this->rtf['item_head'][0] . $title . $this->rtf['item_head'][1];
	}

	public function finish() {
	
	  // Closing bracket for RTF header
	  
    $this->output .= "\n}";
	}

  // Main method for retrieving RTF output

	public function output() {
	
		$filename = $this->api->getFileName() . ".rtf";

    if ($this->HTML_PREVIEW == false) {
    
      header("Content-type: application/rtf");
      header("Content-Disposition: attachment; filename=" . $filename);
      header("Pragma: no-cache");
      header("Expires: 0");
      
		  echo $this->output;
		  
    } else {
      echo "<html><body><pre>";
  		echo $this->output;
  		echo "</pre></body></html>";
		}
	}

  // I don't think RTF will use this ...

	protected function set_header($array) {

		//get the current data. . .
		$newArray = $this->output->getHeaderData();
		//. . . and override with whatever is in the param . . .
		foreach($array as $prop=>$value) {
			$newArray[$prop] = $value;
		}
		//. . . and set it back in the TCPDF
		$this->output->setHeaderData($newArray['logo'], $newArray['logo_width'], $newArray['title'], $newArray['string']);
	}
	
	// SAX event handlers that map element names to RTF code
	
  protected function convertHTMLtoRTF($xhtml) {

    // Convert character encoding from UTF-8 to Latin-1
    // TODO: fix this (this is not satisfactory -- it 'straightens' quotes, etc.)
    /*
    if (DB_CHARSET == 'utf8') {
      //$xhtml = iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", $xhtml);
      $xhtml = iconv("UTF-8", "ISO-8859-1//IGNORE", $xhtml);
    }
    */
    // Set up SAX parser, and parse!

    $sax = xml_parser_create('UTF-8');
    xml_parser_set_option($sax, XML_OPTION_CASE_FOLDING, false);
    xml_parser_set_option($sax, XML_OPTION_SKIP_WHITE, true);
    xml_set_element_handler($sax, Array($this, 'saxHtmlElementStart'), Array($this, 'saxHtmlElementEnd'));
    xml_set_character_data_handler($sax, Array($this, 'saxCdata'));
    xml_parse($sax, $xhtml, true);
    xml_parser_free($sax);
  }
	
	protected function saxHtmlElementStart($sax, $tag, $attr) {

    if ($tag == 'a') { 
      $this->output .=  $this->rtf['a'][0] 
                        . $attr['href']
                        . $this->rtf['a'][1];
    }
    else if ($tag == 'img') {
      $this->output .= $this->getImageRTF($attr['src'], 0, 0);
    }
    else { // TODO: Test if translation exists
      if ( !empty( $this->rtf[$tag] ) ) {
        $this->output .= $this->rtf[$tag][0];
      }
    }
  }
  
  protected function saxHtmlElementEnd($sax, $tag) {

    if ($tag == 'a') { 
      $this->output .=  $this->rtf['a'][2];
    }
    else if ($tag == 'img') { }
    else {
      if ( !empty( $this->rtf[$tag] ) ) {
        $this->output .= $this->rtf[$tag][1];
      }
    }
  }
  
  // TODO: TEST ALL THIS
  
  protected function saxCdata($sax, $data) {

    $data = trim($data); // Trim off spaces at beginning/end
    $data = preg_replace('/\s+/u', ' ', $data); // Normalize spaces
    $data = preg_replace('/[\\\{\}]/u', '\${1}', $data);   // Escape special chars (backslash, curly braces)
    $data = preg_replace('/&nbsp;|&#160;/u', '\~', $data); // Non-breaking space
  
    // Convert text to ASCII + HTML entities
    
    // echo "{BEFORE{{{{\n\n" . $data . "\n}}}}}\n";
    $data = htmlentities ( $data, ENT_NOQUOTES | ENT_IGNORE, 'ISO-8859-1', false );
    // echo "{AFTER{{{{\n\n" . $data . "\n}}}}}\n";
    
    // Now replace HTML entities with RTF \uxxx notation
    // Code derived from http://php.net/manual/en/function.html-entity-decode.php
    
    $data = preg_replace('~&#x([0-9a-f]+);~ei', '"\\u" . hexdec("\\1")', $data);  // Hex
    $data = preg_replace('~&#([0-9]+);~e',      '"\\u\\1"',              $data);  // Dec
    
    // Replace literal entities
    
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    $data = strtr($data, $trans_tbl);
    
    // Scan text for non-ASCII characters - replace with unicode escape
    
    for ($i = 0; $i< mb_strlen($data, 'UTF-8'); $i++) {
  
      $char = mb_substr($data, $i, 1, 'UTF-8');
      $charNumbers = unpack('N', mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'));
      
      if ($charNumbers[1] < 32 || $charNumbers[1] > 127) {

        // \u + unicode number in decimal + replacement character
        $this->output .= '\u' . $charNumbers[1]
                              . iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", $char);
      }
      else { $this->output .= $char; }
    }
    
    /*
    for ($i = 0; $i< mb_strlen($data, 'UTF-8'); $i++) {
    
        $c = mb_substr($data, $i, 1, 'UTF-8');
        
        $o = unpack('N', mb_convert_encoding($c, 'UCS-4BE', 'UTF-8'));
        $h = strtoupper(dechex($o));
        $len = strlen($h);
        if ($len % 2 == 1) { $h = "0$h"; }
        
        $mb_hex = $this->hex_format($o[1]);
        print "{" . $c . "|" . $mb_hex . "|" . $o[1];
        if ($o[1] < 32 || $o[1] > 127) { print "*"; } 
        print "}\n";
    }
    */
    
    /* ATTEMPT ONE
    // $data = html_entity_decode($data, ENT_NOQUOTES);
    //preg_replace('/&#\d+;/', '\u${1}', $data);    // Numeric entities (e.g. &#065;)

    // if (preg_match('/[^\x20-\x7F]/u', $data)) {
    
      // $this->output .= 'HEY THERE, FOO!';
    
      // Go through each character
      // If it's outside of ASCII, replace it with the \uxxxx code
      
      for ($i = 0; $i < strlen($data); $i++) {
        print '{' . substr ( $data, $i, 1 ) . '}';
      
        if (preg_match('/[^(\x20-\x7F)]/', $data)) {
          print "*"; 
        }
      }
    
    // } // If non-ascii chars found ...

    // arrChars[intChar] = "\\u" + intCharCode.toString() + "  ";
    */
    
    //echo htmlspecialchars($data);
    
    // Normalize space
    // Convert special RTF chars
    // Convert HTML entities (like &amp;) into regular text
    
    // $this->output .= $data;
    
  }
  

  protected function hex_format($o) {
      $h = strtoupper(dechex($o));
      $len = strlen($h);
      if ($len % 2 == 1) { $h = "0$h"; }
      return $h;
  }
  
  // Get an image and return the (RTF-friendly) hex code
  
  protected function getImageHexCodeOld($filename) {

    return bin2hex(file_get_contents($filename));
  }
  
  // TODO: convert all images to PNG format
  // (this is the only format that seems to work consistently in Word)
  
  protected function getImageHexCode($filename) {

    $upload_dir_array = wp_upload_dir( null, false );
    $tempImageFilename = $upload_dir_array['basedir'] . DIRECTORY_SEPARATOR . 'anthologize-rtf-temp-img.png';
  
    // TODO: get image and save to temp file
    // (only if the fopen wrappers have NOT been enabled --
    // otherwise, will accept a URL)
    
    //$filename = urlencode($filename);
    list($width, $height, $image_type) = getimagesize($filename);

    if ($image_type == 3) { // Already a PNG
      return bin2hex(file_get_contents($filename));
    }
    else { // Not a PNG: convert
    
      switch ($image_type)
      {
          case 1: $imageObject = imagecreatefromgif($filename);   break;
          case 2: $imageObject = imagecreatefromjpeg($filename);  break;
          default: return '';                                     break;
      }
      
      imagepng($imageObject, $tempImageFilename);
      imagedestroy($imageObject);
      // echo "OKAY"; die();//
      return bin2hex(file_get_contents($tempImageFilename));
    }
  }
}

