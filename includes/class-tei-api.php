<?php
class TeiApi {

	public $tei;
	public $xpath;

	public function __construct($tei) {
		$this->tei = $tei;
		$this->xpath = $this->tei->xpath;
	}

	public function getFileName() {

		$text = strtolower($this->tei->projectData['post-title']);
        $fileName = preg_replace('/\s/', "_", $text);
        $fileName = mb_ereg_replace('/[^\w\-]/', '', $fileName);
        $fileName = trim($fileName, "_");

        $fileName = rtrim($fileName, ".");

        return $fileName;
	}

	public function getErrors() {

	}


	/* Accessors for building output formats */

	/**
	* nodeToArray gives a 'flattened' array of the node and subnode values.
	* E.g. array('elName'=>'value', 'elName2'=>'value')
	* @param DOMNode
	* @return Array
	*/

	public function nodeToArray($node) {

		$retArray = array();

		if( ! $node ) {
			return $retArray;
		}

		$retArray[$node->nodeName] = array();
		//TODO: stuff additional info into this "top" array?
		$retArray[$node->nodeName]['childNodes'] = array();
		$retArray[$node->nodeName]['name'] = $node->nodeName;

		if($node->nodeName == 'body') {
			//value is wrapped in <body> in the TEI, so strip that out for return value
			$val = $this->getNodeXML($node);
			$val = str_replace('<body xmlns="http://www.w3.org/1999/xhtml">' , '' , $val);
			$val = str_replace('</body>' , '', $val);
			$retArray['value'] = $val;
			return $retArray;
		}

		if($node->nodeName == 'param') {
			$retArray[$node->getAttribute('name')] = $node->textContent;
		}

		$allMyChildren = $node->getElementsByTagname('*');

		if($allMyChildren->length == 0) {
			$retArray[$node->nodeName]['childNodes'] = false;
			$retArray[$node->nodeName]['value'] = $node->textContent;

			if($node->hasAttribute('ref')) {
				$ref = $node->getAttribute('ref');
				$refNode = $this->getNodeListByXPath("//*[@xml:id = '$ref']", true );
				$retArray[$node->nodeName]['value'] = $this->nodeToArray($refNode);
			}
		}

		//To handle multiple instances of same nodeName, we'll pluralize everything
		//in the array

		foreach($allMyChildren as $childNode) {
			$plName = $childNode->nodeName . "s";
			if( ! isset($retArray[$plName])) {
				$retArray[$plName] = array();
			}
			if($childNode->childNodes->length == 1
					&& ($childNode->firstChild->nodeName == "#text" )
					|| $childNode->firstChild->nodeName == "#cdata-section" ) {
				$elArray = array('value'=> $childNode->textContent,
				'name'=>$childNode->nodeName );

				if($childNode->nodeName == 'param') {
					$elArray['value'] = $childNode->textContent;
					$elArray['name'] = $childNode->getAttribute('name');
				}
					$retArray[$plName][] = $elArray;
					$retArray[$node->nodeName]['childNodes'][] = $plName;
			}

			if($childNode->hasAttribute('ref')) {
				$ref = $childNode->getAttribute('ref');

				$nd = $this->getNodeDataByParams(array('id'=>$ref));

				$retArray[$plName][] = $nd;
				$retArray[$node->nodeName]['childNodes'][] = $plName;
			}

			//if empty, get rid of it
			if ( count($retArray[$plName]) == 0 ) {
				unset($retArray[$plName]);
			}
		}
		return $retArray;
	}



	/**
	 * Retrive data in the node
	 * Returns either the node itself or a flat array containing data in the node and its children.
	 * Allowed params:
	 * array('id'=> , an element id
	 * 		'section'=> ,  'front', 'body', or 'back' of the tei:text
	 * 		'partNumber' => , part number within the section
	 * 		'itemNumber' => , item number within the part
	 * 		'subPath' => , additional path beyond the above parameters
	 * 		'contextNode' => , the DOMNode context of the query
	 * 		'asList'=>, boolean if you want the whole list
	 *
	 *
	 * )
	 *
	 * @param array $params
	 * @param boolean $asNode = false
	 * @return mixed DOMNode or array
	 */

	public function getNodeDataByParams($params, $asNode = false) {
		extract($params);

		if( isset($id) ) {
			$queryString = "//*[@id = '$id']";

		} else if ( isset($section)) {
			$queryString = "//tei:$section";
			if(isset($partNumber)) {
				if($section == 'body') {
					$queryString .= "/tei:div[@n='$partNumber']";
				}

				if(isset($itemNumber)) {
					$queryString .= "/tei:div[@n='$itemNumber']";
				}
			}
		}

		if(isset($subPath) ) {
			$queryString .= "/$subPath";
		}

		if(isset($contextNode) ) {
			$nodeList = $this->getNodeListByXPath(array('xpath'=>$queryString, 'contextNode'=>$contextNode) );
		} else {
			$nodeList = $this->getNodeListByXPath($queryString);
		}

		if(! $nodeList ) {
			return false;
		}

		if( $asList ) {
			$retArray = array();
			foreach($nodeList as $node) {
				if(! isset($retArray[$node->nodeName . 's'])) {
					$retArray[$node->nodeName . 's'] = array();
				}

				if($asNode) {
					$retArray[$node->nodeName . 's'][] = $node;
				} else {
					$retArray[$node->nodeName . 's'][] = $this->nodeToArray($node);
				}
			}

			return $retArray;
		}

		$node = $nodeList->item(0);

		if($asNode) {
			return $node;
		}

		return $this->nodeToArray($node);
	}

	/**
	 * dump the node to a string
	 * @pararm DOMNode $node a node
	 * @return string
	 */

	public function getNodeXML($node) {
		return $this->tei->dom->saveXML($node);
	}

	/**
	 * get the node list for an XPath
	 * @param mixed $xpath as string, an xpath. as array: array('xpath'=>$xpath, 'contextNode'=> DOMNode)
	 * @param $firstOnly = false return only the first match
	 * @return mixed DOMNodeList or DOMNode
	 */

	public function getNodeListByXPath($xpath, $firstOnly = false) {

		if(is_array($xpath)) {
			$nodeList = $this->xpath->query($xpath['xpath'], $xpath['contextNode']);
		} else {
			$nodeList = $this->xpath->query($xpath);
		}

		if($nodeList->length == 0 ) {
			return false;
		}
		if($firstOnly) {
			return $nodeList->item(0);
		}
		return $nodeList;
	}

	/**
	 * get the project title
	 * @return string <html:span>
	 */

	public function getProjectTitle() {
		$queryString = "//tei:head[@type='titlePage']/tei:bibl/tei:title[@type='main']";
		$titleNode = $this->getNodeListByXPath($queryString, true);

		return $this->getNodeXML($titleNode->firstChild);
	}

	/**
	 * get the project subtitle
	 * @return string <html:span>
	 */
	public function getProjectSubTitle() {

		$queryString = "//tei:head[@type='titlePage']/tei:bibl/tei:title[@type='sub']";
		$subTitleNode = $this->getNodeListByXPath($queryString, true);
		return $this->getNodeXML($subTitleNode->firstChild);
	}

	public function getProjectCopyRight($asStructured = false) {

		if($asStructured) {
			$rend = 'structured';
			$cr = $this->xpath->query("//tei:publicationStmt/tei:availability[@rend='$rend']/tei:ab/text()")->item(0)->textContent;
		} else {
			$rend = 'literal';
			$cr = $this->xpath->query("//tei:publicationStmt/tei:availability[@rend='$rend']/html:body")->item(0)->nodeValue;
		}
		return $cr->nodeValue;
	}

	public function getProjectEdition($asStructured = false) {
		if($asStructured) {
			$rend = 'structured';
			$edition = $this->xpath->query("//tei:editionStmt/tei:ab[@rend='$rend']/text()")->item(0);
		} else {
			$rend = 'literal';
			$edition = $this->xpath->query("//tei:editionStmt/tei:ab[@rend='$rend']/body/text()")->item(0);
		}
		return $edition;
	}

	public function getProjectOutputParams($param = false, $asNode = false) {
		$xpath = "//anth:outputDecl";
		if($param) {
			$xpath .= "/anth:outputParams/anth:param[@name='$param']";
		}
		$params = array('subPath'=>$xpath,
		'asNode'=>$asNode
		);
		return $this->getNodeDataByParams($params);
	}

	public function getSectionPartCount($section = 'body') {
		$count = $this->xpath->evaluate("count(//tei:$section/tei:div[@type='part'])");
		return $count;
	}

	public function getSectionPartHead($section, $partNumber, $asNode = false) {
		$params = array('section' => $section,
		'partNumber'=>$partNumber,
		'subPath'=>'tei:head',
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getSectionPartTitle($section, $partNumber, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'subPath'=>'tei:head/tei:title',
		'asNode'=>true);

		$data = $this->getNodeDataByParams($params);

		if($asNode) {
			return $data;
		}
		if($data) {
			return $this->getNodeXML($data->firstChild);
		}
	}

	public function getSectionPartMetaEl($section, $partNumber, $elName, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'subPath'=>"tei:$elName",
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);

	}

	public function getSectionPartItemCount($section, $partNumber) {
		$count = $this->xpath->evaluate("count(//tei:$section/tei:div[@type='part'][@n='$partNumber']/tei:div[@type='libraryItem'])");
		return $count;
	}

	public function getSectionPartItemHead($section, $partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getSectionPartItemTitle($section, $partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>"tei:head/tei:title",
		'asNode'=>true);

		$data = $this->getNodeDataByParams($params);

		if($asNode) {
			return $data;
		}
		if($data) {
			return $this->getNodeXML($data->firstChild);
		}
	}

	public function getSectionPartItemMetaEl($section, $partNumber, $itemNumber, $elName, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>"tei:$elName",
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getSectionPartItemContent($section, $partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>'div',
		'asNode'=>true);
		$data = $this->getNodeDataByParams($params);

		if($asNode) {
			return $data;
		}
		if($data) {
			return $this->getNodeXML($data);
		}
	}

	public function getAuthorHead($authorId, $asNode = false) {
		$params = array('id'=>$authorId ,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getAuthorMetaEl($authorId, $elName, $asNode = false) {
		$params = array('id'=>$authorId ,
		'asNode'=>$asNode,
		'elName'=>$elName);
		return $this->getNodeDataByParams($params);
	}

	public function getGravatar($user_login, $asNode = false ) {

	}

	public function getGravatarURL($user_login, $size = false) {

	}
}
?>
