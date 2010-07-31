<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', "http://www.anthologize.org/ns");

class TeiDom {

	public $dom;
	public $xpath;
	public $knownPersonArray = array();
	public $personMetaDataNode;
	public $bodyNode;
  public $userNiceNames = array();


	function __construct($postArray, $checkImgSrcs = true) {



		$this->dom = new DOMDocument();
    $templatePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" .
      DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tei' . DIRECTORY_SEPARATOR .'teiEmpty.xml';
		$this->dom->load($templatePath);
    $this->dom->preserveWhiteSpace = false;
    $this->setXPath();
		$this->buildProjectData($postArray['project_id']);
    if($checkImgSrcs) {
    	$this->checkImgSrcs();
    }

    $this->processPostArray($postArray);

	}

  public function processPostArray($postArray) {
    //process all the data and stuff it into the appropriate place in

    //copyright/license info (availability)
    $this->addLicense($postArray);
    //"editors" copyright and title page



    //date
    $pubDateNode = $this->xpath->query("//tei:publicationStmt/tei:date")->item(0);
    $pubDateNode->appendChild($this->dom->createTextNode($postArray['cyear']));


    //edition
    $edNode = $this->xpath->query("//tei:editionStmt/tei:edition")->item(0);
    $edNode->appendChild($this->dom->createTextNode($postArray['edition']));

    //front1
    $f1Node = $this->xpath->query("//tei:div[@xml:id='f1']")->item(0);
    $f1TitleNode = $this->xpath->query("tei:head/tei:title", $f1Node )->item(0);
    $f1TitleNode->appendChild($this->dom->createTextNode($postArray['front1-title']));
    //$htmlNode = $this->buildHtmlDom($postArray['front1']);
    //$f1Node->appendChild($htmlNode);

    //front2
    $f2Node = $this->xpath->query("//tei:div[@xml:id='f2']")->item(0);
    $f2TitleNode = $this->xpath->query("tei:head/tei:title", $f2Node )->item(0);
    $f2TitleNode->appendChild($this->dom->createTextNode($postArray['front2-title']));
    //$htmlNode = $this->buildHtmlDom($postArray['front2']);


    $outParamsNode = $this->xpath->query("//anth:outputParams")->item(0);
    //font-size
    $fontSizeNode = $this->xpath->query("anth:param[@name='font-size']", $outParamsNode)->item(0);
    $fontSizeNode->appendChild($this->dom->createTextNode($postArray['font-size']));

    //paper-type
    $paperTypeNode = $this->xpath->query("anth:param[@name='paper-type']", $outParamsNode)->item(0);
    $paperTypeNode->appendChild($this->dom->createTextNode($postArray['page-size']));
    //paper-size
    $pageHNode = $this->xpath->query("anth:param[@name='page-height']", $outParamsNode)->item(0);
    $pageWNode = $this->xpath->query("anth:param[@name='page-width']", $outParamsNode)->item(0);


    switch($postArray['page-size']) {
    	case 'A4':
        $pageHNode->appendChild($this->dom->createTextNode('297mm'));
        $pageWNode->appendChild($this->dom->createTextNode('210mm'));
      break;

      case 'letter':
        $pageHNode->appendChild($this->dom->createTextNode('11in'));
        $pageWNode->appendChild($this->dom->createTextNode('8.5in'));
      break;

    }
    //font-family
    $fontFamilyNode = $this->xpath->query("anth:param[@name='font-family']", $outParamsNode)->item(0);
    $fontFamilyNode->appendChild($this->dom->createTextNode($postArray['font-face']));


/*
  Array
(
    [post-title] => title
    [dedication] =>
    [acknowledgements] =>
    [filetype] => tei
    [page-size] => letter
    [font-size] => 9
    [font-face] => times
    [cyear] =>
    [cname] =>
    [ctype] =>
    [edition] =>
    [authors] =>
    [project_id] =>
    [export-step] => 2
    [submit] => Next
)

 */


  }

  public function addLicense($postArray) {
  	$avlPNode = $this->xpath->query("//tei:availability/tei:p")->item(0);
    $avlPNode->appendChild($this->dom->createTextNode('Copyright ' . $postArray['cyear']));
    if($postArray['ctype'] == 'c') {
      return;
    }

    $ccNode = $this->dom->createElementNS(TEI, 'p');

    switch($postArray['cctype']) {
    	case 'by':
        $ccNode->appendChild($this->dom->createTextNode('Creative Commons By'));
      break;

      case 'by-sa':
        $ccNode->appendChild($this->dom->createTextNode('Creative Commons By ShareAlike'));
      break;

      case 'by-nd':
        $ccNode->appendChild($this->dom->createTextNode('Creative Commons By No Derivatives'));
      break;

      case 'by-nc':
        $ccNode->appendChild($this->dom->createTextNode('Creative Commons By Non-Commercial'));
      break;

      case 'by-nc-sa':
        $ccNode->appendChild($this->dom->createTextNode('Creative Commons By Non-Commercial ShareAlike'));
      break;

      case 'by-nc-nd':
        $ccNode->appendChild($this->dom->createTextNode('Creative Commons By Non-Commercial No Derivatives'));
      break;

      default:

      break;
    }
    $avlNodeP->parentNode->appendChild($ccNode);
  }

  public function setXPath() {
    $this->xpath = new DOMXPath($this->dom);
    $this->xpath->registerNamespace('tei', TEI);
    $this->xpath->registerNamespace('html', HTML);
    $this->xpath->registerNamespace('anth', ANTH);
    $authorAB =  $this->xpath->query("//tei:ab[@type = 'metadata']")->item(0);
    $this->personMetaDataNode = $this->xpath->query("tei:listPerson", $authorAB)->item(0);
    $this->bodyNode = $this->xpath->query("//tei:body")->item(0);
  }

	public function getTeiString() {
		return $this->dom->saveXML();
	}

	public function getTeiDom() {
		return $this->dom;
	}

	public function addPerson($userObject) {

    if(! in_array($userObject->user_nicename, $this->userNiceNames)) {
       $newPerson = $this->dom->createElementNS(TEI, 'person');
       $newPerson->setAttribute('xml:id', $userObject->user_nicename );
       foreach($userObject->wp_capabilities as $role=>$wtf) {
        $roleStr .= $role . " ";
       }
       $newPerson->setAttribute('role', $roleStr);
       $newPersName = $this->dom->createElement('persName');
       $newPersName->appendChild($this->dom->createElementNS(TEI, 'tei:forename', $userObject->first_name));
       $newPersName->appendChild($this->dom->createElementNS(TEI, 'surname', $userObject->last_name) );
       $ident = $this->dom->createElementNS(TEI, 'ident');
       $ident->appendChild($this->dom->createCDataSection($userObject->user_url));
       $ident->setAttribute('type', 'url');
       $newPersName->appendChild($ident);
       //boones fancy thing
       //$author_name_array = get_post_meta( $item_id, 'author_name_array' )
       //$outputNames = $this->dom->createElement('addName', $userObject->user_first_name) );

       $newPerson->appendChild($newPersName);
       $this->personMetaDataNode->appendChild($newPerson);
       $this->userNiceNames[] = $userObject->user_nicename;
		}

	}

  public function buildProjectData($projectID) {

  	$projectData = new WP_Query(array('ID'=>$projectID, 'post_type'=>'projects'));
    $project = $projectData->posts[0];

    $titleNode = $this->xpath->query('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title')->item(0);
    //yes, I tried $titleNode->textContent=$project->post_title. No, it didn't work. No, I don't know why
    $titleNode->appendChild($this->dom->createTextNode($project->post_title));

    //TODO: also slap title into titlePage



    $identNode = $this->xpath->query('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl/tei:ident')->item(0);

    $identNode->appendChild($this->dom->createCDataSection($project->guid));

    $partsData =  new WP_Query(array('post_parent'=>$projectID, 'post_type'=>'parts'));

    $partObjectsArray = $partsData->posts;

    usort($partObjectsArray, array('TeiDom', 'postSort'));


    foreach($partObjectsArray as $partObject) {
    	$newPart = $this->newPart($partObject);
      $libraryItemsData = new WP_Query(array('post_parent'=>$partObject->ID, 'post_type'=>'library_items'));
      $libraryItemObjectsArray = $libraryItemsData->posts;
      //sort objects, by menu_order, then ID
      usort($libraryItemObjectsArray, array('TeiDom', 'postSort'));
      foreach($libraryItemObjectsArray as $libraryItemObject) {
      	$newItemContent = $this->newItemContent($libraryItemObject);
        $newPart->appendChild($newItemContent);
      }
      $this->bodyNode->appendChild($newPart);
    }
  }

	public function newPart($partObject) {
    $newPart = $this->dom->createElementNS(TEI, 'div');
    $newPart->setAttribute('type', 'part');
    $newPart->appendChild($this->newHead($partObject));
    return $newPart;
	}

	public function newItemContent($libraryItemObject) {

    $newPostContent = $this->dom->createElementNS(TEI, 'div');
    $newPostContent->setAttribute('type', 'libraryItem');
    $newPostContent->setAttribute('subtype', 'html');
    $newPostContent->appendChild($this->newHead($libraryItemObject));
    $tmpHTML = new DOMDocument();
    //TODO: do_shortcode produces HTML that doesn't work, so shortcodes are coming through as is
    //$content = do_shortcode($libraryItemObject->post_content);
    $content = $libraryItemObject->post_content;
    //using loadHTML because it is more forgiving than loadXML

    $tmpHTML->loadHTML($content);
    if($this->checkImgSrcs) {
      $this->checkImgSrcs($tmpHTML);

    }
    $body = $tmpHTML->getElementsByTagName('body')->item(0);
    $body->setAttribute('xmlns', HTML);

    $imported = $this->dom->importNode($body, true);
    $newPostContent->appendChild($imported);

    return $newPostContent;

	}

	public function newHead($postObject) {
		$newHead = $this->dom->createElementNS(TEI, 'head');
		$title = $this->dom->createElementNS(TEI, 'title', $postObject->post_title);
    $guid = $this->dom->createElementNS(TEI, 'ident');
    $guid->appendChild($this->dom->createCDataSection($postObject->guid));
    $guid->setAttribute('type', 'guid');
		$newHead->appendChild($title);
    $newHead->appendChild($guid);

    //TODO: check if content is native, based on the GUID. if content native, dig up author info
    //from userID. Otherwise/and, go with info from boones
    // $author_name = get_post_meta( $item_id, 'author_name', true );

		$authorObject = get_userdata($postObject->post_author);
		//print_r($authorObject);
    $this->addPerson($authorObject);

		if($authorObject) {
        $bibl = $this->dom->createElementNS(TEI, 'bibl');
        $author = $this->dom->createElementNS(TEI, 'author');
        $author->setAttribute('ref', $authorObject->user_nicename);
        $bibl->appendChild($author);
        $newHead->appendChild($bibl);
		}
		return $newHead;
	}

  private function postSort($a, $b) {
      if($a->menu_order > $b->menu_order) {
        return 1;
      } else if ($a->menu_order < $b->menu_order) {
        return -1;
      } else if ($a->menu_order == $b->menu_order) {
          return $a->ID - $b->ID;
      }
  }



  private function checkImgSrcs() {
    $imgs = $this->dom->getElementsByTagName('img');
    for($i = $imgs->length; $i>0; $i--) {
        $imgNode = $imgs->item(0);
        $src =  $imgNode->getAttribute('src');
        //TODO: check to see if the src is http:// or a relative path
        // if relative path, convert it into an http://
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $src);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($code == 404) {
          $noImgSpan = $this->dom->createElementNS(HTML, 'span', 'Image not found');
          $noImgSpan->setAttribute('class', 'anthologize-error');
          $imgNode->parentNode->replaceChild($noImgSpan, $imgNode);
        }
    }
  }

  public static function getFileName($postArray) {
        $text = strtolower($postArray['post-title']);
        $fileName = preg_replace('/\s/', "_", $text);
        $fileName = preg_replace('/[^\w\-]/', '', $fileName);
        $fileName = trim($fileName, "_");
        $fileName = rtrim($fileName, ".");
        return $fileName;
  }
}


