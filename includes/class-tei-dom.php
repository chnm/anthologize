<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');

class TeiDom {
	
	public $dom;
	public $xpath;
	public $knownPersonArray = array();
	public $personMetaDataNode;
	public $bodyNode;
	
	
	function __construct($bookID) {
		
		$this->dom = new DOMDocument();
		$this->dom->load('teiBase.xml');
		$this->xpath = new DOMXPath($this->dom);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->personMetaDataNode = $this->xpath->query("//tei:ab[@type = 'personMetadata']")->item(0);
		$this->bodyNode = $this->xpath->query("//tei:body")->item(0);
		//$this->buildBookData($wpContent);
	}
	
	public function getTeiString() {
		return $this->dom->saveXML();
	}
	
	public function getTeiDom() {
		return $this->dom;
	}
	
	public function addPerson($userObject) {
		
	}
	
  public function buildBookData($bookID) {
  	$book = new WP_Query(array('id'=>$bookID, 'post_type'=>'books'));
    $titleNode = $this->xpath->query('/TEI/teiHeader/fileDesc/titleStmt/title')->item(0);
    $titleNode->textContent = $book->post_title;
           
    $partObjectsArray = new WP_Query(array('post_parent'=>$bookID, 'post_type'=>'parts'));
    //sort objects, by menu_order, then post_date
    foreach($partObjectsArray as $partObject) {
    	$newPart = $this->newPart($partObject);
      $libraryItemObjectsArray = new WP_Query(array('post_parent'=>$partObject->ID, 'post_type'=>'library_items'));
      //sort objects, by menu_order, then post_date
      foreach($libraryItemObjectsArray as $libraryItemObject) {
      	$newItemContent = $this->newItemContent($libraryItemObject);
        $newPart->appendChild($newItemContent);
      }
      $this->body->appendChild($newPart);
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
		$tmpHTML->loadHTML($libraryItemObject);
		
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
}


$tei = new TeiDom(867);
/*
$np = $tei->newPart('dummy');
$nh = $tei->newPartHead(array('title'=>'Test Title' , 'authorRefs'=>array('boonebgorges')) );
$np->appendChild($nh);

$postString = "<p>I'll be some post data someday!</p><p>And I need to check the namespacing!</p>";
$postContent = $tei->newPostContent($postString);


$np->appendChild($postContent);

$tei->bodyNode->appendChild($np);


$test = $tei->dom->getElementsByTagnameNS(HTML, 'p');
echo $test->length;
*/



