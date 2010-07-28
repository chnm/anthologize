<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');

class TeiDom {
	
	public $dom;
	public $xpath;
	public $personArray = array();
	public $personMetaDataNode;
	public $bodyNode;
	
	
	function __construct($wpContent = null) {
		
		$this->dom = new DOMDocument();
		$this->dom->load('teiBase.xml');
		$this->xpath = new DOMXPath($this->dom);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->personMetaDataNode = $this->xpath->query("//tei:ab[@type = 'personMetadata']")->item(0);
		$this->bodyNode = $this->xpath->query("//tei:body")->item(0);
		//$this->buildDom($wpContent);
	}
	
	public function getTeiString() {
		return $this->dom->saveXML();
	}
	
	public function getTeiDom() {
		return $this->dom;
	}
	
	public function addPerson($username) {
		
	}
	
	public function newPart($partData) {
		$newPart = $this->dom->createElement('div');
		$newPart->setAttribute('type', 'part');
		
		return $newPart;
	}
	
	public function newPostContent($wpPostContent) {
		$newPostContent = $this->dom->createElement('div');
		$newPostContent->setAttribute('type', 'post');
		$newPostContent->setAttribute('subtype', 'html');
		$tmpHTML = new DOMDocument();
		//using loadHTML because it is more forgiving than loadXML
		$tmpHTML->loadHTML($wpPostContent);
		
		$body = $tmpHTML->getElementsByTagName('body')->item(0);
		$body->setAttribute('xmlns', HTML);
		
		$imported = $this->dom->importNode($body, true);
		$newPostContent->appendChild($imported);
		
		return $newPostContent;
		
	}
	
	public function newPartHead($headData) {
		$newHead = $this->dom->createElement('head');
		$title = $this->dom->createElement('title', $headData['title']);
		$newHead->appendChild($title);
		
		
		if($headData['authorRefs']) {
			$bibl = $this->dom->createElement('bibl');	
			foreach($headData['authorRefs'] as $authorRef) {
				$author = $this->dom->createElement('author');
				$author->setAttribute('ref', $authorRef);
				$bibl->appendChild($author);
			}
			$newHead->appendChild($bibl);
		}
			
		
		return $newHead;
	}
}


$tei = new TeiDom();
$np = $tei->newPart('dummy');
$nh = $tei->newPartHead(array('title'=>'Test Title' , 'authorRefs'=>array('boonebgorges')) );
$np->appendChild($nh);

$postString = "<p>I'll be some post data someday!</p><p>And I need to check the namespacing!</p>";
$postContent = $tei->newPostContent($postString);


$np->appendChild($postContent);

$tei->bodyNode->appendChild($np);


$test = $tei->dom->getElementsByTagnameNS(HTML, 'p');
echo $test->length;


