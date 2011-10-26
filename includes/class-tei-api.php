<?php
class TeiApi {

	public $tei;
	public $xpath;
	public $fileName;

	public function __construct($tei) {
	    if($tei instanceof TeiDom) {
    	    $this->tei = $tei->dom;
    		$this->xpath = $tei->xpath;
	    } else {
	        throw new Exception('TeiApi must be passed a TeiDom object');
	    }

	    $text = strtolower($tei->projectData['post-title']);
        $fileName = preg_replace('/\s/', "_", $text);
        $fileName = mb_ereg_replace('/[^\w\-]/', '', $fileName);
        $fileName = trim($fileName, "_");

        $fileName = rtrim($fileName, ".");
	    $this->fileName = $fileName;
	}

	public function getFileName() {
        return $this->fileName;
	}

	/* Accessors for building output formats */

	/**
	* nodeToArray gives a 'flattened' array of the node and subnode values.
	* E.g. array('elName'=>'value', 'elName2'=>'value')
	* @param DOMNode
	* @return Array
	*/

	private function nodeToArray($node, $deep = true, $followRefs = false) {

		$retArray = array();

		if( ! $node ) {
			return $retArray;
		}

		if($node->nodeType == XML_ATTRIBUTE_NODE) {
			$retArray = array('attrName'=>$node->nodeName, 'value'=>$node->nodeValue);
			return $retArray;
		}

		$retArray['elName'] = $node->nodeName;

		$attNodes = $this->xpath->query("@*", $node);
		$retArray['atts'] = array();
		foreach($attNodes as $att) {
			$retArray['atts'][$att->nodeName] = $att->nodeValue;
		}


		if($node->nodeName == 'param') {
			$retArray[$node->getAttribute('name')] = $node->textContent;
		}

		if( !empty($node->firstChild) && ( $node->firstChild->nodeType == XML_TEXT_NODE || $node->firstChild->nodeType == XML_CDATA_SECTION_NODE ) ) {
			$retArray['value'] = $node->textContent;
			return $retArray;
		}

		if( ($node->nodeName == 'div') && ( in_array(HTML, $retArray['atts']) )  ) {
			$retArray['value'] = $this->getNodeXML($node);
		}

		if( ! $deep ) {
			return $retArray;
		}

		foreach($node->childNodes as $childNode) {

			if( $childNode->nodeType != XML_ELEMENT_NODE ){
				continue;
			}

			$plName = $childNode->nodeName . "s";
			if( ! isset($retArray[$plName])) {
				$retArray[$plName] = array();
			}
			$retArray[$plName][] = $this->nodeToArray($childNode, $deep);

			if($followRefs && $childNode->hasAttribute('ref')) {
				$ref = $childNode->getAttribute('ref');
				$nd = $this->getNodeDataByParams(array('id'=>$ref));
				$retArray[$plName][] = $nd;
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
	
//@TODO: this is an epic mess
	private function getNodeDataByParams($params, $firstOnly = true) {
		
		extract($params);
		$queryString = $this->buildQueryString($params);

		if(isset($contextNode) ) {
			$nodeList = $this->getNodeListByXPath(array('xpath'=>$queryString, 'contextNode'=>$contextNode) );
		} else {
			$nodeList = $this->getNodeListByXPath($queryString);
		}
		
		if(! $nodeList ) {
			return false;
		}

		// go through the possibilities of $asNode and $firstOnly
		// if $asNode return in each branch, else make it an array
		// first, the $firstOnly cases
		if($firstOnly) {
			$node = $nodeList->item(0);
			if($asNode) {
				return $node;
			}
			return $this->nodeToArray($node);
		}
        //now the asNode cases
		if($asNode) {
			return $nodeList;
		}
        //not asNode, so build and return the array
		$returnArray = array();
		foreach($nodeList as $node) {
			$returnArray[] = $this->nodeToArray($node);
		}
		return $returnArray;
	}

	private function getNodeTargetData($node) {
		//get id and a label/title
		$id = $node->getAttribute('xml:id');
		$titleNode = $this->getNodeListByXPath(array('xpath'=>"tei:head/tei:title", 'contextNode'=>$node), true);
		$title = $titleNode->firstChild->nodeValue;
		return array('id'=>$id, 'title'=>$title);
	}

	/**
	 * dump the node to a string
	 * @param DOMNode $node a node
	 * @return string
	 */

	private function getNodeXML($node, $atts = false) {
		if($atts) {
			foreach($atts as $att=>$val) {
				$node->setAttribute($att, $val);
			}
		}
		return $this->tei->saveXML($node);
	}


	private function getParentItem($node, $asNode = false) {
		while( $node->getAttribute('type') != 'libraryItem') {
			$node = $node->parentNode;

		}
		if($asNode) {
			return $node;
		}
		return $this->nodeToArray($node, false);
	}

	private function getParentItemId($node) {
		while( $node->getAttribute('type') != 'libraryItem') {
			$node = $node->parentNode;
		}
		return $node->getAttribute('xml:id');
	}

	/**
	 * get the node list for an XPath
	 * @param mixed $xpath as string, an xpath. as array: array('xpath'=>$xpath, 'contextNode'=> DOMNode)
	 * @param $firstOnly = false return only the first match
	 * @return mixed DOMNodeList or DOMNode
	 */

	private function getNodeListByXPath($xpath, $firstOnly = false) {

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
	 * @param $valueOnly = false whether to return the value only or wrap in a span
	 * @return string
	 */


//TODO: make option to return node like other methods
	public function getProjectTitle($valueOnly = false) {

		$queryString = "//tei:head[@type='titlePage']/tei:bibl/tei:title[@type='main']";
		$titleNode = $this->getNodeListByXPath($queryString, true);
		if($titleNode) {
    		if($valueOnly) {
    			return $titleNode->firstChild->nodeValue;
    		}
    		return $this->getNodeXML($titleNode->firstChild);
		}
        return false;
	}

	/**
	 * get the project subtitle
	 * @param $valueOnly = false whether to return the value only or wrap in a span
	 * @return string
	 */

	public function getProjectSubTitle($valueOnly = false) {

		$queryString = "//tei:head[@type='titlePage']/tei:bibl/tei:title[@type='sub']";
		$subTitleNode = $this->getNodeListByXPath($queryString, true);
		if($subTitleNode) {
    		if($valueOnly) {
    			return $subTitleNode->firstChild->nodeValue;
    		}
    		return $this->getNodeXML($subTitleNode->firstChild);
		}
		return false;
	}

	/**
	 * get info about the project creator, or just the display name
	 * @param $asStructured = false whether to return an array with complete data. requires option includeCreatorData = true when construction TeiDOM
	 * @return mixed array of data or string
	 */

	public function getProjectCreator($asStructured = false, $asNode = false) {
		$queryString = "//tei:author[@role = 'projectCreator']";
		$creator = $this->getNodeListByXPath($queryString, true);
        if($creator) {
    		if($asStructured) {
    			$ref = $creator->getAttribute('ref');
    			$structuredCreator = $this->tei->getElementById($ref);
    			if($asNode) {
    			    return $structuredCreator;
    			}
    			return $this->nodeToArray($structuredCreator);
    		}
    		if($asNode) {
    			return $creator->firstChild;
    		}
    		return $creator->firstChild->textContent;
        }
        
        return false;


	}

	/**
	 * get copyright/license information about the project
	 * @param $asStructured = false return as structured data or human-readable
	 * return string
	 */
//TODO: make it return a node,
	public function getProjectCopyright($asStructured = false, $html = true) {

		if($asStructured) {

			$cr = $this->getNodeListByXPath("//tei:publicationStmt/tei:availability[@rend='structured']/tei:ab", true);

			if($cr->parentNode->getAttribute('status') == 'c') {
				return "<span>Copyright</span>";
			}

		} else {
			$cr = $this->getNodeListByXPath("//tei:publicationStmt/tei:availability[@rend='literal']/span", true);

			if($cr->parentNode->getAttribute('status') == 'c') {
				return "<span>Copyright</span>";
			}
		}
		if($html) {
			return $this->getNodeXML($cr);
		}

		return $cr->firstChild->textContent;

	}

	/**
	 * get edition information about the project
	 * @param $asStructured = false return as structured data or human-readable
	 * return string
	 */

	public function getProjectEdition($asStructured = false) {
		$edition = $this->getNodeListByXPath("//tei:editionStmt/tei:ab[@rend='literal']/span", true);
		return $this->getNodeXML($edition);
	}

	/**
	 * get an output param set on the export screen
	 * @param $param = false a single param value to return
	 * @param $asNode = false whether to return as a node or a string
	 * @return mixed DOMNode or string or array
	 */

	public function getProjectOutputParams($param = false, $asNode = false) {
		$xpath = "//anth:outputDecl/anth:outputParams";
		if($param) {
			$xpath .= "/anth:param[@name='$param']";
		}
		$params = array('subPath'=>$xpath,
						'asNode'=>$asNode
						);
		$data = $this->getNodeDataByParams($params);
		if($param) {
			return $data[$param];
		}

		return $data;
	}

	/**
	 * return the publication date
	 * @return string
	 */

	public function getProjectPublicationDate() {
		$query = "//tei:front/tei:head/tei:bibl/tei:data[@type = 'created']";
		$params = array('section'=>'front',
						'subPath'=>"/tei:head/tei:bibl/tei:date[@type = 'created']",
						'asNode'=>false
					);
		$data = $this->getNodeDataByParams($params);
		return $data['value'];
	}

	/**
	 * get the number of parts in a section
	 * @param $section = 'body' the section, front, body, or back
	 * @return int
	 */

	public function getSectionPartCount($section = 'body') {
		$count = $this->xpath->evaluate("count(//tei:$section/tei:div[@type='part'])");
		return $count;
	}

	/**
	 * get the id set for a part within a section
	 * @param $section the section. front, body, or back
	 * @param $partNumber the number of the part within the section
	 * @return string
	 */

	public function getSectionPartId($section, $partNumber) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'subPath' => "@xml:id",
						'asNode'=>false
						);
		$data = $this->getNodeDataByParams($params);
		return $data['value'];
	}



	public function getSectionPartHead($section, $partNumber, $asNode = false) {
		$params = array('section' => $section,
		'partNumber'=>$partNumber,
		'subPath'=>'/tei:head',
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	/**
	 * get the title for a part within a section
	 * @param $section the section. front, body, or back
	 * @param $partNumer the number of the part within the section
	 * @param $asNode whether to return the DOMNode
	 * @return mixed string or DOMNode
	 */

	public function getSectionPartTitle($section, $partNumber, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'subPath'=>'/tei:head/tei:title',
		'asNode'=>true);

		$data = $this->getNodeDataByParams($params);
        if($data) {
            if($asNode) {
			    return $data;
		    }
		    return $this->getNodeXML($data->firstChild);
        }
        return false;
	}




	/**
	 * get the number of items in a part in a section
	 * @param $section = 'body' the section, front, body, or back
	 * @param $partNumber the number of the part within the section
	 * @return int
	 */

	public function getSectionPartItemCount($section, $partNumber = null) {
		switch($section) {
			case 'front':
				$count = $this->xpath->evaluate("count(//tei:$section/tei:div)");
			break;

			case 'body':
				$count = $this->xpath->evaluate("count(//tei:$section/tei:div[@type='part'][@n='$partNumber']/tei:div[@type='libraryItem'])");
			break;

			case 'back':
				$count = $this->xpath->evaluate("count(//tei:$section/tei:div)");
			break;

		}


		return $count;
	}

	public function getSectionPartItemHead($section, $partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	/**
	 * get the title for an item within a part within a section
	 * @param $section the section. front, body, or back
	 * @param $partNumer the number of the part within the section
	 * @param $itemNumber the number of the item within the part
	 * @param $asNode whether to return the DOMNode
	 * @return mixed string or DOMNode
	 */


	public function getSectionPartItemTitle($section, $partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=> $section,
    		'partNumber'=>$partNumber,
    		'itemNumber'=>$itemNumber,
    		'subPath'=>"/tei:head/tei:title",
    		'asNode'=>true);
		$data = $this->getNodeDataByParams($params);
		if($data) {
			if($asNode) {
    			return $data;
    		}
		    return $this->getNodeXML($data->firstChild);
		}
		return false;
	}

	/**
	 * get the id set for an item within a part within a section
	 * @param $section the section. front, body, or back
	 * @param $partNumber the number of the part within the section
	 * @param $itemNumber the number of the item within the part
	 * @return string
	 */

	public function getSectionPartItemId($section, $partNumber, $itemNumber) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "/@xml:id",
						'asNode'=>false
						);
		$data = $this->getNodeDataByParams($params);
		return $data['value'];
	}

	/**
	 * get info about the original author of the content anthologized. (that is the author of the post or page, not necessarily the creator of the item within the project)
	 * @param $section the section. front, body, or back
	 * @param $partNumer the number of the part within the section
	 * @param $itemNumber the number of the item within the part
	 * @param $valueOnly = true give just the display name
	 * @param $asNode whether to return the DOMNode
	 * @return mixed string or array
	 */

	public function getSectionPartItemOriginalAuthor($section, $partNumber, $itemNumber, $valueOnly = true, $asNode = false) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "/tei:head/tei:bibl/tei:author[@role='originalAuthor']",
						'asNode'=>$asNode
						);

		$data = $this->getNodeDataByParams($params);

		if($valueOnly) {
			return $data['spans'][0]['value'];
		}
		return $data;

	}

	/**
	 * get info about anthologizer of the content. that is, who added it to the project
	 * @param $section the section. front, body, or back
	 * @param $partNumer the number of the part within the section
	 * @param $itemNumber the number of the item within the part
	 * @param $asNode whether to return the DOMNode
	 * @param $valueOnly = true give just the display name
	 * @return mixed string or array
	 */

	public function getSectionPartItemAnthologizer($section, $partNumber, $itemNumber, $asNode = false, $valueOnly = true) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "/tei:head/tei:bibl/tei:author[@role='anthologizer']",
						'asNode'=>$asNode
						);

		$data = $this->getNodeDataByParams($params);
		if($valueOnly) {
			return $data['spans'][0]['value'];
		}
		return $data;

	}
	/**
	 * get info about the author, as set in the Anthologize project administration pages
	 * @param $section the section. front, body, or back
	 * @param $partNumer the number of the part within the section
	 * @param $itemNumber the number of the item within the part
	 * @param $asNode whether to return the DOMNode
	 * @param $valueOnly = true give just the display name
	 * @return mixed string or array
	 */
	public function getSectionPartItemAssertedAuthor($section, $partNumber, $itemNumber, $asNode = false, $valueOnly = true) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "/tei:head/tei:bibl/tei:author[@role='assertedAuthor']",
						'asNode'=>$asNode
						);

		$data = $this->getNodeDataByParams($params);
		if($asNode) {
		    if($valueOnly) {
		        return $data->textContent;
		    }
		    return $data;
		}
		if($valueOnly) {
			return $data['spans'][0]['value'];
		}
		return $data;

	}

	/**
	 * get the tags and categories for the item
	 * Opinionated comment: the distinction is usually irrelevant across more than one user and blog, so -Subjects covers both, -Tags and -Categories make the distinction
	 * @param $section the section. front, body, or back
	 * @param $partNumer the number of the part within the section
	 * @param $itemNumber the number of the item within the part
	 * @param $asNode whether to return the DOMNode
	 * @return mixed array or DOMNode
	 */

	public function getSectionPartItemSubjects($section, $partNumber, $itemNumber, $asNode = false ) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>"/tei:head/tei:list[@type='subjects']/tei:item/tei:rs",
		'asNode'=> $asNode);
		$data = $this->getNodeDataByParams($params, false);
		return $data;
	}

	public function getSectionPartItemTags($section, $partNumber, $itemNumber, $asNode = false ) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>"/tei:head/tei:list[@type='subjects']/tei:item/tei:rs[@type='tag']",
		'asNode'=> $asNode);
		$data = $this->getNodeDataByParams($params, false);
		return $data;
	}

	public function getSectionPartItemCategories($section, $partNumber, $itemNumber, $asNode = false ) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>"/tei:head/tei:list[@type='subjects']/tei:item/tei:rs[@type='category']",
		'asNode'=> $asNode);
		$data = $this->getNodeDataByParams($params, false);
		return $data;
	}

	/**
	 * get the content for an item within a part within a section
	 * @param $section the section. front, body, or back
	 * @param $partNumer the number of the part within the section
	 * @param $itemNumber the number of the item within the part
	 * @param $asNode whether to return the DOMNode
	 * @return mixed string or DOMNode
	 */


	public function getSectionPartItemContent($section, $partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>'/div',
		'asNode'=> true);
		$data = $this->getNodeDataByParams($params);

		if($data) {
    		if ($data->childNodes->length == 0) {
    			return false;
    		}
    		if($asNode) {
    			return $data;
    		}
    		return $this->getNodeXML($data);
		}
        return false;
	}


	public function getIndex($indexName, $asNode = false) {
		$params = array('index'=>$indexName,
						'asNode'=>$asNode,
						);

		return $this->getNodeDataByParams($params);
	}

	public function getIndexItemCount($index) {

		if( is_array($index)) {
			return count($index['lists'][0]['items']);
		} else if ( is_a($index, 'DOMElement') ) {
			$xpath = "list/item";
			return $this->xpath->evaluate("count($xpath)", $index);
		} else {
			throw new Exception('index must be node or array');
		}

	}

	public function getIndexItem($index, $itemNumber) {
		if (is_array($index)) {
			return $index['lists'][0]['items'][$itemNumber];
		} else if (is_a($index, 'DOMElement')) {
			$params = array('contextNode'=>$index,
							'asNode'=>true,
							'subPath'=>"list/item[@n='$itemNumber']"
							);
			return $this->getNodeDataByParams($params);

		} else {
			throw new Exception('index must be node or array');
		}
	}

	public function getIndexItemLabel($item, $asNode = false) {
 		if (is_array($item)) {
			return $item['rs'][0]['spans'][0]['value'];
		} else if (is_a($item, 'DOMElement')) {
			$params = array('contextNode'=>$item,
							'asNode'=>true,
							'subPath'=>"rs"
							);

			$data =  $this->getNodeDataByParams($params);

			if($asNode) {
				return $data;
			}
			return $this->getNodeXML($data);

		} else {
			throw new Exception('item must be node or array');
		}
	}


	public function getIndexItemRef($item, $asNode = false) {
 		if (is_array($item)) {
			$ref = $item['rs'][0]['atts']['ref'];
		} else if (is_a($item, 'DOMElement')) {
			$ref = $item->firstChild->getAttribute('ref');
		} else {
			throw new Exception('index must be node or array');
		}
		return $this->getDetailsByRef($ref);
	}


	public function getIndexItemTargetCount($item) {
		if( is_array($item)) {
			return count($item['listRefs'][0]['rs']);
		} else if ( is_a($item, 'DOMElement') ) {
			$xpath = "listRef/rs"; //looks like when giving a context node, evaluate doesn't want prefixes
			return $this->xpath->evaluate("count($xpath)", $item);
		} else {
			throw new Exception('index must be node or array');
		}
	}

	public function getIndexItemTarget($item, $targetNumber) {
 		if (is_array($item)) {
			return $item['listRefs'][0]['rs'][$targetNumber];
		} else if (is_a($item, 'DOMElement')) {
			$params = array('contextNode'=>$item,
							'asNode'=>true,
							'subPath'=>"listRef/rs[@n='$targetNumber']"
							);

			return $this->getNodeDataByParams($params);
		} else {
			throw new Exception('item must be node or array');
		}
	}

	/**
	 * dig up a detail about the target
	 *
	 * @param DOMElement $target
	 * @param string $detail this is the info you are looking for: ref, role, label
	 */

	public function getIndexItemTargetDetail($target, $detail, $asNode = false) {
 		if (is_array($target)) {

			switch ($detail) {
				case 'ref':
					return $target['atts']['ref'];
				break;

				case 'role':
					return $target['atts']['role'];
				break;

				case 'label':
					return $target['spans'][0]['value'];
				break;

			}
		} else if (is_a($target, 'DOMElement')) {
			switch ($detail) {
				case 'ref':
					return $target->getAttribute('ref');
				break;

				case 'role':
					return $target->getAttribute('role');
				break;

				case 'label':
					if($asNode) {
						return $target->firstChild;
					}
					return $this->getNodeXML($target->firstChild);

				break;
			}
		} else {
			throw new Exception('index must be node or array');
		}
	}


	/**
	 * get the structured data about a person or subject based on their id/username
	 * @param $ref the id/username of the person. (it's a ref attribute in the TEI)
	 * @param $asNode whether to return the DOMNode
	 * @return array
	 */

	public function getDetailsByRef($ref, $asNode = false) {
		$params = array('id'=>$ref ,
						'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}


	/**
	 * dig up a particular piece of data out of the structured array for a user. Basically a helper to sort through the array
	 * @param array $personArray the structured array representing the person
	 * @param string $element the name of the data you want
	 * @return string
	 */

	public function getPersonDetail($personArray, $element) {
		switch ($element) {
			case 'name':
				return $personArray['persNames'][0]['names'][0]['spans'][0]['value'];
			break;

			case 'firstname':
				return $personArray['persNames'][0]['firstnames'][0]['spans'][0]['value'];
			break;

			case 'surname':
				return $personArray['persNames'][0]['surnames'][0]['spans'][0]['value'];
			break;

			case 'bio':
				return $personArray['notes'][0]['divs'][0]['value'];
			break;

			case 'email':
				return $personArray['persNames'][0]['emails'][0]['spans'][0]['value'];
			break;

			case 'count':
				return $personArray['persNames'][0]['nums'][0]['value'];
			break;

			case 'gravatarUrl':
				return isset( $personArray['figures'][0]['graphics'][0]['imgs'][0]['atts']['src'] ) ? $personArray['figures'][0]['graphics'][0]['imgs'][0]['atts']['src'] : '';
			break;

		}
	}
	
	public function buildQueryString($params) {
		//id, section, and index all start a new queryString
		$queryString = '';
		if(isset($params['id'])) {
		    $this->_filterQueryStringById(&$queryString, $params['id']);
		}
		
        if(isset($params['section'])) {
		    $this->_filterQueryStringBySection(&$queryString, $params['section']);
        }
		
	    if(isset($params['index'])) {
		    $this->_filterQueryStringByIndex(&$queryString, $params['index']);
        }
		
		
		//these three add onto the queryString, in order
		//only the body should filter by part numbers
	    if(isset($params['partNumber']) && isset($params['section']) && $params['section'] == 'body') {
		    $this->_filterQueryStringByPartNumber(&$queryString, $params['partNumber']);
        }
		
		if(isset($params['itemNumber'])) {
		    $this->_filterQueryStringByItemNumber(&$queryString, $params['itemNumber']);
        }
		
		if(isset($params['subPath'])) {
		    $this->_filterQueryStringBySubPath(&$queryString, $params['subPath']);
        }
        return $queryString;
	}
    	
    private function _filterQueryStringById($queryString, $id) {
        if(!empty($id)) {
            $queryString = "//*[@xml:id = '$id']";
        }
        
    }
    
    private function _filterQueryStringByIndex($queryString, $index) {
        if(!empty($index)) {
            $queryString = "//tei:div[@type='index'][@subtype='$index']";
        }
    
    }
        
    private function _filterQueryStringBySection($queryString, $section) {
        if(!empty($section)) {
            $queryString = "//tei:$section";
        }
        
    }
    
    //@TODO: it'd be sweet of me to throw a warning for non-integer
    private function _filterQueryStringByItemNumber($queryString, $itemNumber) {
        $queryString .= "/tei:div[@n='$itemNumber']";
    }

    
    private function _filterQueryStringByPartNumber($queryString, $partNumber) {
        $queryString .= "/tei:div[@n='$partNumber']";
    }
    
    private function _filterQueryStringBySubPath($queryString, $subPath) {
        if(!empty($subPath)) {
            $queryString .= "$subPath";
        }
        
    }

	
	
}
