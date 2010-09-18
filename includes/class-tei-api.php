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

	public function nodeToArray($node, $deep = true) {

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



		if($node->firstChild->nodeType == XML_TEXT_NODE || $node->firstChild->nodeType == XML_CDATA_SECTION_NODE) {
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

			if($childNode->hasAttribute('ref')) {
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

	public function getNodeDataByParams($params, $firstOnly = true) {
		extract($params);

		if( isset($id) ) {
			$queryString = "//*[@xml:id = '$id']";

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

		if($firstOnly) {
			$node = $nodeList->item(0);

			if($asNode) {
				return $node;
			}

			return $this->nodeToArray($node);

		}

		if($asNode) {
			return $nodeList;
		}

		$returnArray = array();
		foreach($nodeList as $node) {
			$returnArray[] = $this->nodeToArray($node);
		}

		return $returnArray;

	}

	public function getNodeTargetData($node) {
		//get id and a label/title
		$id = $node->getAttribute('xml:id');
		$titleNode = $this->getNodeListByXPath(array('xpath'=>"tei:head/tei:title", 'contextNode'=>$node), true);
		$title = $titleNode->firstChild->nodeValue;
		return array('id'=>$id, 'title'=>$title);
	}

	/**
	 * dump the node to a string
	 * @pararm DOMNode $node a node
	 * @return string
	 */

	public function getNodeXML($node, $atts = false) {
		if($atts) {
			foreach($atts as $att=>$val) {
				$node->setAttribute($att, $val);
			}
		}
		return $this->tei->dom->saveXML($node);
	}


	public function getParentItem($node, $asNode = false) {
		while( $node->getAttribute('type') != 'libraryItem') {
			$node = $node->parentNode;

		}
		if($asNode) {
			return $node;
		}
		return $this->nodeToArray($node, false);
	}

	public function getParentItemId($node) {
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

	public function getProjectTitle($valueOnly = false) {

		$queryString = "//tei:head[@type='titlePage']/tei:bibl/tei:title[@type='main']";
		$titleNode = $this->getNodeListByXPath($queryString, true);
		if($valueOnly) {
			return $titleNode->firstChild->nodeValue;
		}
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

	public function getProjectCreator($asStructured = false) {
		$queryString = "//tei:author[@role = 'projectCreator']";
		$creator = $this->getNodeListByXPath($queryString, true);

		if($asStructured) {
			$ref = $creator->getAttribute('ref');
			$structuredCreator = $this->tei->dom->getElementById($ref);
			return $this->nodeToArray($structuredCreator);
		}

		return $this->getNodeXML($creator->firstChild);
	}


	public function getProjectCopyright($asStructured = false) {

		if($asStructured) {
			$cr = $this->getNodeListByXPath("//tei:publicationStmt/tei:availability[@rend='structured']/tei:ab", true);

		} else {
			$cr = $this->getNodeListByXPath("//tei:publicationStmt/tei:availability[@rend='literal']/span", true);
		}
		return $this->getNodeXML($cr);
	}

	public function getProjectEdition($asStructured = false) {
		$edition = $this->getNodeListByXPath("//tei:editionStmt/tei:ab[@rend='literal']/span", true);
		return $this->getNodeXML($edition);
	}

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



	public function getProjectPublicationDate() {
		$query = "//tei:front/tei:head/tei:bibl/tei:data[@type = 'created']";
		$params = array('section'=>'front',
						'subPath'=>"tei:head/tei:bibl/tei:date[@type = 'created']",
						'asNode'=>false
					);
		$data = $this->getNodeDataByParams($params);
		return $data['value'];
	}


	public function getSectionPartCount($section = 'body') {
		$count = $this->xpath->evaluate("count(//tei:$section/tei:div[@type='part'])");
		return $count;
	}

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

	public function getSectionPartItemId($section, $partNumber, $itemNumber) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "@xml:id",
						'asNode'=>false
						);
		$data = $this->getNodeDataByParams($params);
		return $data['value'];
	}

	public function getSectionPartItemOriginalCreator($section, $partNumber, $itemNumber, $valueOnly = true, $asNode = false) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "tei:head/tei:bibl/tei:author[@role='originalCreator']",
						'asNode'=>$asNode
						);

		$data = $this->getNodeDataByParams($params);

		if($valueOnly) {
			return $data['spans'][0]['value'];
		}
		return $data;

	}

	public function getSectionPartItemCreator($section, $partNumber, $itemNumber, $valueOnly = true, $asNode = false) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "tei:head/tei:bibl/tei:author[@role='itemCreator']",
						'asNode'=>$asNode
						);

		$data = $this->getNodeDataByParams($params);
		if($valueOnly) {
			return $data['spans'][0]['value'];
		}
		return $data;

	}

	public function getSectionPartItemAnthAuthor($section, $partNumber, $itemNumber, $valueOnly = true, $asNode = false) {
		$params = array('section'=> $section,
						'partNumber' => $partNumber,
						'itemNumber' => $itemNumber,
						'subPath' => "tei:head/tei:bibl/tei:author[@role='anthologizeMeta']",
						'asNode'=>$asNode
						);

		$data = $this->getNodeDataByParams($params);
		if($valueOnly) {
			return $data['spans'][0]['value'];
		}
		return $data;

	}

	public function getSectionPartItemMetaEl($section, $partNumber, $itemNumber, $elName, $asNode = false) {

		$xpath = "//tei:body/tei:div[@n='$partNumber']/tei:div[@n='$itemNumber']/tei:head/$elName";

		echo $xpath;
		$nl = $this->getNodeListByXPath($xpath);

		$retArray = array();
		foreach($nl as $node) {
			$retArray[] = $this->nodeToArray($node);
		}

		return $retArray;


	}

	public function getSectionPartItemSubjects($section, $partNumber, $itemNumber, $asNode = false ) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>"tei:head/tei:list[@type='subjects']/tei:item/tei:rs",
		'asNode'=> $asNode);

		$data = $this->getNodeDataByParams($params, false);
		return $data;
	}

	public function getSectionPartItemContent($section, $partNumber, $itemNumber) {
		$params = array('section'=> $section,
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'subPath'=>'div',
		'asNode'=> true);
		$data = $this->getNodeDataByParams($params);

		if($asNode) {
			return $data;
		}

		if($data) {
			return $this->getNodeXML($data);
		}
	}

	public function getPersonByRef($ref, $asNode = false) {
		$params = array('id'=>$ref ,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getPersonMetaEl($authorId, $elName, $asNode = false) {
		$params = array('id'=>$authorId ,
		'asNode'=>$asNode,
		'elName'=>$elName);
		return $this->getNodeDataByParams($params);
	}

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
				return $personArray['figures'][0]['graphics'][0]['html:imgs'][0]['atts']['src'];
			break;

		}

	}

	public function getGravatar($authorId, $urlOnly = false ) {

	}

//below should be in a theming function, or some other place, maybe an Indexing extension?

	public function indexAuthorsSimple() {
		$authorNodes = $this->getNodeListByXPath("//tei:body//tei:author");
		$authorsArray = array();

		foreach($authorNodes as $author) {

			$itemNode = $this->getParentItem($author, true);
			$itemData = $this->getNodeTargetData($itemNode);
			$authorArray = $this->nodeToArray($author);

			$itemData['authorRole'] = $authorArray['atts']['role'];

			if(array_key_exists($authorArray['spans'][0]['value'], $authorsArray )  ) {
				$authorsArray[$authorArray['spans'][0]['value']]['items'][] = $itemData;

			} else {
				$authorArray['items'][] = $itemData;
				$authorsArray[$authorArray['spans'][0]['value']] = $authorArray;
			}

		}

		ksort($authorsArray, SORT_STRING);

		$html = "<ul class='anth-index authors'>";
		foreach($authorsArray as $author) {

			$html .= "<li>" . $author['spans'][0]['value'] ;
			$html .= "<ul class='anth-index links'>";
			foreach($author['items'] as $item) {
				switch($item['authorRole']) {
					case 'itemCreator':
						$roleText = "Anthologizer";
					break;

					case 'originalCreator':
						$roleText = "Original Author";
					break;

					case 'anthologizeMeta':
						$roleText = "Attributed Author";
					break;
				}

				$id = $item['id'];
				$title = $item['title'];
				$html .= "<li><a href='#$id'>$title</a> ($roleText)</li>";

			}
			$html .= "</ul>";
			$html .= "</li>";
		}
		$html .= "</ul>";

		return $html;
	}

	public function indexSubjects() {
		$subjects = $this->getNodeListByXPath("//tei:list[@type='subjects']/tei:item/tei:rs/span");
		$subjectsArray = array();
		foreach($subjects as $subject) {

			$subjectLabel = $subject->nodeValue;
			$lowerLabel = strtolower($subjectLabel);
			$parentItem = $this->getParentItem($subject, true);
			if( array_key_exists($lowerLabel, $subjectsArray) ) {
				$subjectsArray[$lowerLabel]['items'][] = $this->getNodeTargetData($parentItem);
			} else {
				$subjectsArray[$lowerLabel] = array();
				$subjectsArray[$lowerLabel]['label'] = $subjectLabel;
				$subjectsArray[$lowerLabel]['items'] = array();
				$subjectsArray[$lowerLabel]['items'][] = $this->getNodeTargetData($parentItem);
			}
		}
		ksort($subjectsArray, SORT_STRING);
		$html = "<ul class='anth-index subjects'>";
		foreach($subjectsArray as $subject) {
			$subjectLabel = $subject['label'];
			$html .= "<li>$subjectLabel";
			$html .= "<ul>";
			foreach($subject['items'] as $item) {
				$id = $item['id'];
				$title = $item['title'];
				$html .= "<li><a href='#$id'>$title</a></li>";
			}
			$html .= "</ul>";

		}
		$html .= "</ul>";
		return $html;

	}

	public function indexImages() {
		$imgNodes = $this->getNodeListByXPath("//tei:body//img");
		$html = "<ul id='anth-image-index' class='anth-index>'";
		foreach($imgNodes as $imgNode) {
			//add 'src' to id to differentiate ids from index and body/src
			$imgIdBase = $this->mintId($imgNode);
			$imgId = $imgIdBase . '-src';


			$imgNode->setAttribute('id', $imgId);
			$parentItem = $this->getParentItem($imgNode, true);
			$itemTargetData = $this->getNodeTargetData($parentItem);
			$targetId = $itemTargetData['id'];
			$title = $itemTargetData['title'];
			$html .= "<li class='anth-index-item'><div>";
			$imgNodeClone = $imgNode->cloneNode(true);
			$imgNodeClone->setAttribute('id', $imgIdBase . '-img-index');
			$html .= $this->getNodeXML($imgNodeClone);
			$html .= "<p>Item: <a href='#$targetId'>$title</a></p>";
			$html .= "<p><a href='#$imgId'>Direct link</a></p>";
			$html .= "</div></li>";
		}
		$html .= "</ul>";
		return $html;

	}

	private function mintID($node) {
		$parentItem = $this->getParentItem($node, true);
		$itemId = $parentItem->getAttribute('xml:id');
		$nameMatches = $parentItem->getElementsByTagname($node->nodeName);
		$index = 0;
		foreach($nameMatches as $nameMatch) {
			if ($node === $nameMatch) {
				return $itemId . "-$node->nodeName-$index";
			}
			$index ++;
		}
	}
}
?>
