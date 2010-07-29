<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('XML', 'http://www.w3.org/2001/XMLSchema#');

class TeiDom {
	
	public $dom;
	public $xpath;
	public $knownPersonArray = array();
	public $personMetaDataNode;
	public $bodyNode;
  public $userNiceNames = array();
	
	
	function __construct($projectID) {
		
		$this->dom = new DOMDocument();
    $templatePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . 
      DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tei' . DIRECTORY_SEPARATOR .'teiEmpty.xml';
		$this->dom->load($templatePath);
		$this->xpath = new DOMXPath($this->dom);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$authorAB =  $this->xpath->query("//tei:ab[@type = 'metadata']")->item(0);    
    $this->personMetaDataNode = $this->xpath->query("tei:listPerson", $authorAB)->item(0);    
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

    if(! in_array($userObject->user_nicename, $this->userNiceNames)) {
       $newPerson = $this->dom->createElement('person');       
       $newPerson->setAttribute('xml:id', $userObject->user_nicename );
       $newPersName = $this->dom->createElement('persName');
       $newPersName->appendChild($this->dom->createElement('forename', $userObject->user_first_name));
       $newPersName->appendChild($this->dom->createElement('surname', $userObject->user_last_name) );
       //$newPersName->appendChild($this->dom->createElement('forename', $userObject->user_url) );
       //boones fancy thing
       //$newPersName->appendChild($this->dom->createElement('forename', $userObject->user_first_name) );
       
       $newPerson->appendChild($newPersName);
       $this->personMetaDataNode->appendChild($newPerson);
       $this->userNiceNames[] = $userObject->user_nicename;
       
		}

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
    $newPostContent->appendChild($this->newHead($libraryItemObject));
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
    
    //TODO: check if content is native, based on the GUID. if content native, dig up author info
    //from userID. Otherwise/and, go with info from boones 
    // $author_name = get_post_meta( $item_id, 'author_name', true );
    
		$authorObject = get_userdata($postObject->post_author);
		//print_r($authorObject);
    $this->addPerson($authorObject);
        
		if($authorObject) {
			$bibl = $this->dom->createElement('bibl');
      $author = $this->dom->createElement('author');
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
}


