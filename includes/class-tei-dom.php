<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');


class TeiDom {
	
	public $dom;
	public $xpath;
	public $knownPersonArray = array();
	public $personMetaDataNode;
	public $bodyNode;
	
	
	function __construct($projectID) {
		
		$this->dom = new DOMDocument();
    $templatePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . 
      DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tei' . DIRECTORY_SEPARATOR .'teiEmpty.xml';
		$this->dom->load($templatePath);
		$this->xpath = new DOMXPath($this->dom);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->personMetaDataNode = $this->xpath->query("//tei:ab[@type = 'personMetadata']")->item(0);
		$this->bodyNode = $this->xpath->query("//tei:body")->item(0);
		$this->buildProjectData($projectID);
	}
	
	public function getTeiString() {
		return $this->dom->saveXML();
	}
	
	public function getTeiDom() {
		return $this->dom;
	}
	
	public function addPerson($userObject) {
		
	}
	
  public function buildProjectData($projectID) {
  	$book = new WP_Query(array('id'=>$projectID, 'post_type'=>'projects'));
    $titleNode = $this->xpath->query('/TEI/teiHeader/fileDesc/titleStmt/title')->item(0);
    $titleNode->textContent = $book->post_title;
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
		$newPart = $this->dom->createElement('div');
		$newPart->setAttribute('type', 'part');		
    $newPart->appendChild($this->newHead($partObject));     
		return $newPart;
	}
	
	public function newItemContent($libraryItemObject) {

		$newPostContent = $this->dom->createElement('div');
		$newPostContent->setAttribute('type', 'libraryItem');
		$newPostContent->setAttribute('subtype', 'html');
		$tmpHTML = new DOMDocument();
		//using loadHTML because it is more forgiving than loadXML
		$tmpHTML->loadHTML($libraryItemObject->post_content);
		$body = $tmpHTML->getElementsByTagName('body')->item(0);
		$body->setAttribute('xmlns', HTML);
		
		$imported = $this->dom->importNode($body, true);
		$newPostContent->appendChild($imported);
		
		return $newPostContent;
		
	}
	
	public function newHead($postObject) {
		$newHead = $this->dom->createElement('head');
		$title = $this->dom->createElement('title', $postObject->post_title);
		$newHead->appendChild($title);
		$authorObject = get_userdata($postObject->ID);
		
    $this->addPerson($authorObject);
        
		if($authorObject) {
			$bibl = $this->dom->createElement('bibl');	
			foreach($postObject['authorRefs'] as $authorRef) {
				$author = $this->dom->createElement('author');
				$author->setAttribute('ref', $authorObject->user_login);
				$bibl->appendChild($author);
			}
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
}


