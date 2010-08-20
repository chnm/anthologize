<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', "http://www.anthologize.org/ns");

class TeiDom2 {

	public $dom;
	public $xpath;
	public $knownPersonArray = array();
	public $personMetaDataNode;
	public $bodyNode;
	public $postArray;
	public $userNiceNames = array();
	public $doShortcodes = true;
	public $checkImgSrcs;


	function __construct($postArray, $checkImgSrcs = true) {

		$this->postArray = $postArray;
		$this->checkImgSrcs = $checkImgSrcs;

		if( isset($this->postArray['do-shortcodes']) && $this->postArray['do-shortcodes'] == false ) {
		$this->doShortcodes = false;
		}

		$this->dom = new DOMDocument('1.0', 'UTF-8');
		$templatePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" .
		DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tei' . DIRECTORY_SEPARATOR .'teiEmpty2.xml';
		$this->dom->load($templatePath);
		$this->dom->preserveWhiteSpace = false;
		$this->setXPath();
		$this->buildProjectData($this->postArray['project_id']);
		$this->addOutputDesc();
		$this->addPublicationStmt();
		$this->addFileDesc();
		$this->addSourceDesc();
		$this->addEncodingDesc();
		$this->addFrontMatter();
		$this->addTitlePage();

		$this->sanitizeContent();

	}
	public function setXPath() {
		$this->xpath = new DOMXPath($this->dom);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->xpath->registerNamespace('anth', ANTH);
		$authorAB = $this->xpath->query("//tei:ab[@type = 'metadata']")->item(0);
		$this->personMetaDataNode = $this->xpath->query("tei:listPerson", $authorAB)->item(0);
		$this->bodyNode = $this->xpath->query("//tei:body")->item(0);
	}

	public function buildProjectData($projectID) {

		$projectData = new WP_Query(array('post__in'=>array($projectID), 'post_type'=>'anth_project'));
		$project = $projectData->post;

		$partsData = new WP_Query(array('post_parent'=>$projectID, 'post_type'=>'anth_part'));
		$partObjectsArray = $partsData->posts;
		usort($partObjectsArray, array('TeiDom2', 'postSort'));

		$partNumber = 0;
		foreach($partObjectsArray as $partObject) {
			$newPart = $this->newPart($partObject);
			$newPart->setAttribute('n', $partNumber);
			//TODO: find a way to set no limit to post_per_page
			$libraryItemsData = new WP_Query(array('post_parent'=>$partObject->ID, 'post_type'=>'anth_library_item', 'posts_per_page'=>200));
			$libraryItemObjectsArray = $libraryItemsData->posts;
			//sort objects, by menu_order, then ID
			usort($libraryItemObjectsArray, array('TeiDom2', 'postSort'));
			$itemNumber = 0;
			foreach($libraryItemObjectsArray as $libraryItemObject) {
				$newItemContent = $this->newItemContent($libraryItemObject);
				$newItemContent->setAttribute('n', $itemNumber);
				$newPart->appendChild($newItemContent);
				$itemNumber++;
			}
			$this->bodyNode->appendChild($newPart);
			$partNumber++;
		}
	}

	public function addOutputDesc() {
		//TODO: add outputDesc element to teiEmpty.xml
		$outParamsNode = $this->xpath->query("//anth:outputParams")->item(0);
		//font-size
		$fontSizeNode = $this->xpath->query("anth:param[@name='font-size']", $outParamsNode)->item(0);
		$fontSizeNode->appendChild($this->dom->createTextNode($this->postArray['font-size']));

		//paper-type
		$paperTypeNode = $this->xpath->query("anth:param[@name='paper-type']", $outParamsNode)->item(0);
		$paperTypeNode->appendChild($this->dom->createTextNode($this->postArray['page-size']));
		//paper-size
		$pageHNode = $this->xpath->query("anth:param[@name='page-height']", $outParamsNode)->item(0);
		$pageWNode = $this->xpath->query("anth:param[@name='page-width']", $outParamsNode)->item(0);


		switch($this->postArray['page-size']) {
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
		$fontFamilyNode->appendChild($this->dom->createTextNode($this->postArray['font-face']));

	}

	public function addPublicationStmt() {

		//date
		$pubDateNode = $this->xpath->query("//tei:publicationStmt/tei:date")->item(0);
		$pubDateNode->appendChild($this->dom->createTextNode($this->postArray['cyear']));

	}

	public function addFileDesc() {

		$titleNode = $this->xpath->query('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title')->item(0);
		//yes, I tried $titleNode->textContent=$project->post_title. No, it didn't work. No, I don't know why
		$titleNode->appendChild($this->dom->createTextNode($project->post_title));

		//edition
		$edNode = $this->xpath->query("//tei:editionStmt/tei:edition")->item(0);
		$edNode->appendChild($this->dom->createTextNode($this->postArray['edition']));

		$identNode = $this->xpath->query('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:sourceDesc/tei:bibl/tei:ident')->item(0);

		$identNode->appendChild($this->dom->createCDataSection($project->guid));

	}

	public function addSourceDesc() {

	}

	public function addEncodingDesc() {

	}

	public function addFrontMatter() {
		//TODO: sanitize content and regularize the mode of adding.
		//TODO: reconcile with UX team.

		//front1
		$f1Node = $this->xpath->query("//tei:front/tei:div[@n='0']")->item(0);

		$f1TitleNode = $this->xpath->query("tei:head/tei:title", $f1Node )->item(0);
		//currently, f1 is hardcoded to be Dedication
		//TODO: change this when UI changes
		$f1TitleNode->appendChild($this->dom->createTextNode('Dedication'));
		$f1Html = $this->xpath->query("html:body", $f1Node)->item(0);
		$frag = $this->dom->createDocumentFragment();
		if($this->postArray['dedication'] == '') {
			$this->postArray['dedication'] = "<p></p>";
		}

		$f1Content = htmlentities($this->postArray['dedication']);
		$frag->appendXML($f1Content);
		$f1Html->appendChild($frag);

		//front2
		$f2Node = $this->xpath->query("//tei:front/tei:div[@n='1']")->item(0);
		$f2TitleNode = $this->xpath->query("tei:head/tei:title", $f2Node )->item(0);
		//TODO: change when UI changes currently hardcoded as acknowledgements
		$f2TitleNode->appendChild($this->dom->createTextNode('Acknowledgements'));
		$f2Html = $this->xpath->query('html:body', $f2Node)->item(0);
		$frag = $this->dom->createDocumentFragment();
		if($this->postArray['acknowledgements'] == '') {
			$this->postArray['acknowledgements'] = "<p></p>";
		}

		$f2Content = htmlentities($this->postArray['acknowledgements']);
		$frag->appendXML($this->postArray['acknowledgements']);
		$f2Html->appendChild($frag);

	}

	public function addTitlePage() {

		//"editors" copyright and title page

		$authorsNode = $this->xpath->query("//tei:docAuthor")->item(0);
		$authorsNode->appendChild($this->dom->createTextNode($this->postArray['cname'] . ', ' . $this->postArray['authors']));

		$docEditionNode = $this->xpath->query("//tei:docEdition")->item(0);
		$docEditionNode->appendChild($this->dom->createTextNode($this->postArray['edition']));


		//TODO: also slap title into titlePage

		$frontPageNode = $this->xpath->query("//tei:titlePage")->item(0);

		$mainTitle = $this->xpath->query("//tei:titlePart[@type='main']", $frontPageNode)->item(0);
		$mainTitle->appendChild($this->dom->createTextNode($project->post_title));

		$fpDateNode = $this->xpath->query("tei:docDate", $frontPageNode)->item(0);
		$fpDateNode->appendChild($this->dom->createTextNode($project->post_date));


	}
	public function sanitizeMedia() {
		$this->sanitizeImages();
		$this->sanitizeEmbeds(); //TODO
		$this->sanitizeHTML5(); //TODO
	}

	public function sanitizeImages() {
		 //TODO: check connectivity
		//strip out <a rel="nofollow"> (wordpress feeds)
		$aNoFollowNodes = $this->xpath->query('//a[@rel="nofollow"]');
		foreach($aNoFollowNodes as $aNode) {
			$aNode->parentNode->removeChild($aNode);
		}

		//strip out feedburner links
		$aFeedBurnerLinkNodes = $this->xpath->query('//a[contains(@href, "http://feeds.feedburner.com")]');
		foreach($aFeedBurnerLinkNodes as $aNode) {
			$aNode->parentNode->removeChild($aNode);
		}

		//strip out feedburner invisible images
		$imgNodes = $this->xpath->query('//img[contains(@src, "http://feeds.feedburner.com")]');
		foreach($imgNodes as $imgNode) {
			$imgNode->parentNode->removeChild($imgNode);
		}

		//strip out wordpress stats invisible images
		$imgNodes = $this->xpath->query('//img[contains(@src, "http://stats.wordpress.com")]');
		foreach($imgNodes as $imgNode) {
			$imgNode->parentNode->removeChild($imgNode);
		}
	}

	public function sanitizeEmbeds() {

	}

	public function sanitizeHTML5() {

	}

	public function addAvailability() {
		$avlPNode = $this->xpath->query("//tei:availability/tei:p")->item(0);
		$avlPNode->appendChild($this->dom->createTextNode('Copyright ' . $this->postArray['cyear'] . ', ' . $this->postArray['cname']));
		if($this->postArray['ctype'] == 'c') {
			return;
		}

		$ccNode = $this->dom->createElementNS(TEI, 'p');

		switch($this->postArray['cctype']) {
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
		$avlPNode->parentNode->appendChild($ccNode);
	}

	public function addPerson($userObject) {

		if(! in_array($userObject->user_nicename, $this->userNiceNames)) {
			$newPerson = $this->dom->createElementNS(TEI, 'person');
			$newPerson->setAttribute('xml:id', $userObject->user_nicename );
			if(is_array($userObject->wp_capabilities)) {
				$roleStr = "";
				foreach($userObject->wp_capabilities as $role=>$capabilities) {
					$roleStr .= $role . " ";
				}
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
		$content = $libraryItemObject->post_content;
		$content = wpautop($content);
		if($this->doShortcodes) {
			$content = do_shortcode($content);
		} else {
			$content = $this->sanitizeShortCodes($content);
		}

		$content = $this->sanitizeEntities($content);


		//using loadHTML because it is more forgiving than loadXML
		$tmpHTML = new DOMDocument('1.0', 'UTF-8');
		//conceal the Warning about bad html with @
		//loadHTML adds head and body tags silently
		@$tmpHTML->loadHTML("<?xml version='1.0' encoding='UTF-8' ?><body>$content</body>" );
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

		//TODO: above might be old. Check nativeness by looking at whether dissplay name is set for username

		$authorObject = get_userdata($postObject->post_author);
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

	private function sanitizeString($content, $isMultiline = false) {
		if ($isMultiline) {
			$content = wpautop($content);
		}

		$content = $this->sanitizeEntities($content);
		//using loadHTML because it is more forgiving than loadXML
		$tmpHTML = new DOMDocument('1.0', 'UTF-8');
		//conceal the Warning about bad html with @
		//loadHTML adds head and body tags silently
		@$tmpHTML->loadHTML("<?xml version='1.0' encoding='UTF-8' ?><body>$content</body>" );
		if($this->checkImgSrcs) {
			$this->checkImgSrcs($tmpHTML);

		}

		$body = $tmpHTML->getElementsByTagName('body')->item(0);
		$body->setAttribute('xmlns', HTML);
		$imported = $this->dom->importNode($body, true);
		return $imported;
	}

	private function sanitizeContent() {


		$this->sanitizeMedia();

		//TODO: strip out any empty containers
		if($this->checkImgSrcs) {
			$this->checkImgSrcs();
		}
	}

	private function sanitizeShortCodes($content) {

	     $pattern = get_shortcode_regex();

	     return preg_replace_callback('/'.$pattern.'/s', array('TeiDom', 'sanitizeShortCode'), $content);
	     //TODO: go to town on additional shortcodes not being expanded
	}

	private function sanitizeEntities($content) {
		//TODO: sort out the best order to convert characters and sanitizing stuff.
		//don't want to do html_entity_decode or specialchar_decode in case we need to leave those in place
		return str_replace("&nbsp;", " ", $content);
	}

	private function sanitizeShortCode($m) {
		//modified from WP do_shortcode_tag() wp_includes/shorcodes.php

		$tag = $m[2];
		$html = "<span class='anthologize-shortcode'>***";
		$html .= "Anthologize warning: This section contains a WordPress 'shortcode', which can result in errors in some output formats.";
		$html .= "The shortcode [$tag] has been removed to prevent such errors. You can rectify this by editing the library item in the HTML view, ";
		$html .= "look for the [$tag] in the HTML, and replacing it with the proper HTML. You can find the proper HTML by viewing the item in your browser, ";
		$html .= "and viewing the source. More help will be posted to the Anthologize forums in the future.";
		$html .= "***</span>";
		return $html;
	}

	private function checkImgSrcs() {
		//TODO: check for net connectivity
		//TODO: improve pseudo-error message and feedback
		$imgs = $this->dom->getElementsByTagName('img');
		for($i = $imgs->length; $i>0; $i--) {
			$imgNode = $imgs->item(0);
			$src = $imgNode->getAttribute('src');
			//TODO: check to see if the src is http:// or a relative path
			// if relative path, convert it into an http://
			//first clobber any annoying img links to Reddit, delicious, etc.
			//that might have been inserted.

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $src);
			//curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if($code == 404) {
				$noImgSpan = $this->dom->createElementNS(HTML, 'p', 'Image not found');
				$noImgSpan->setAttribute('class', 'anthologize-error');
				$imgNode->parentNode->replaceChild($noImgSpan, $imgNode);
			}
		}
	}

	/**
	* nodeToArray gives a 'flattened' array of the node and subnode values.
	* E.g. array('elName'=>'value', 'elName2'=>'value')
	* @param DOMNode
	* @return Array
	*/

	private function nodeToArray($node) {
		$retArray = array();
		$retArray[$node->nodeName] = array();
		//TODO: stuff additional info into this "top" array?
		$retArray[$node->nodeName]['childNodes'] = array();
		$retArray[$node->nodeName]['name'] = $node->nodeName;

		if($node->nodeName == 'body') {
			//value is wrapped in <body> in the TEI, so strip that out for return value
			$val = $this->dom->saveXML($node);
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
				$retArray[$plName][] = $this->getNodeDataByParams(array('id'=>$ref));
				$retArray[$node->nodeName]['childNodes'][] = $plName;
			}

			//if empty, get rid of it
			if ( count($retArray[$plName]) == 0 ) {
				unset($retArray[$plName]);
			}
		}

		return $retArray;
	}

	/* Accessor Methods */


	public function getTeiString() {
		return $this->dom->saveXML();
	}

	public function getTeiDom() {
		return $this->dom;
	}

	public function getFileName() {

		$text = strtolower($this->postArray['post-title']);
        $fileName = preg_replace('/\s/', "_", $text);
        $fileName = preg_replace('/[^\w\-]/', '', $fileName);
        $fileName = rawurlencode($fileName); //via Tai for japanese filenames
        $fileName = trim($fileName, "_");
        $fileName = rtrim($fileName, ".");
        return $fileName;
	}

	public static function getFileNameStatic($postArray) {


		$text = strtolower($postArray['post-title']);
        $fileName = preg_replace('/\s/', "_", $text);
        $fileName = preg_replace('/[^\w\-]/', '', $fileName);
        $fileName = rawurlencode($fileName); //via Tai for japanese filenames
        $fileName = trim($fileName, "_");
        $fileName = rtrim($fileName, ".");
        return $fileName;
	}


	public function getErrors() {

	}


	/* Accessors for building documents */


	/**
	 * Retrive data in the node
	 * Returns either the node itself or a flat array containing data in the node and its children.
	 * Use $params['asNode'] = true; to retrieve the node itself
	 *
	 * @param array $params
	 * @return mixed DOMNode or array
	 */

	public function getNodeDataByParams($params) {

		if(isset($params['id'])) {
			$id = $params['id'];
			$queryString = "//*[@xml:id = '$id']";
			if(isset($params['elName'])) {
				$elName = $params['elName'];
				$queryString = $queryString .= "//tei:$elName";
			}
			$node = $this->getNodeListByXPath($queryString, true );

			if($params['asNode']) {
				return $node;
			}
			return $this->nodeToArray($node);
		}

		switch ($params['section']) {
			case 'front':
				$queryString = "//tei:front";
			break;

			case 'body':
				$queryString = "//tei:body";
			break;

			case 'back':
				$queryString = "//tei:back";
			break;

			case 'outputDesc':
				$queryString = "//anth:outputDesc";
				if(isset($params['paramName'])) {
					$paramName = $params['paramName'];
					$queryString .= "//*[@name='$paramName']";
				}
				$node = $this->getNodeListByXPath($queryString, true);

				if($params['asNode']) {
					return $node;
				}
				return $this->nodeToArray($node);
			break;
		}

		if(isset($params['partNumber'])) {
			$partNumber = $params['partNumber'];
			$queryString .= "/tei:div[@n='$partNumber']";
		}

		if(isset($params['itemNumber'])) {
			$itemNumber = $params['itemNumber'];
			$queryString .= "/tei:div[@n='$itemNumber']";
		}

		if(isset($params['isMeta']) && $params['isMeta'] === true) {
			$queryString .= "/tei:head";
			if(isset($params['elName'])) {
				$elName = $params['elName'];
				$queryString .= "//tei:$elName";
			}
		} else {
			$queryString .= "/body";
		}

		$node = $this->getNodeListByXPath($queryString, true);

		if($params['asNode']) {
			return $node;
		}
		return $this->nodeToArray($node);


	}



	public function getNodeListByXPath($xpath, $firstOnly = false) {
		$nodeList = $this->xpath->query($xpath);
		if($firstOnly) {
			return $nodeList->item(0);
		}
		return $nodeList;
	}

	public function getProjectTitle() {

	}

	public function getProjectCopyRight() {


	}

	public function getProjectEdition() {

	}

	public function getProjectOutputParams($ops = array(), $asNode = false) {
		$params = $ops;
		$params['section']= 'outputDesc';
		$params['asNode'] = $asNode;

		return $this->getNodeDataByParams($params);
	}

	public function getBodyPartCount() {
		$count = $this->xpath->evaluate("count(//tei:body/tei:div[@type='part'])");
		return $count;
	}

	public function getBodyPartMeta($partNumber, $asNode = false) {
		$params = array('section'=>'body',
		'partNumber'=>$partNumber,
		'isMeta'=>true,
		'asNode'=>$asNode);

		return $this->getNodeDataByParams($params);

	}

	public function getBodyPartMetaEl($partNumber, $elName, $asNode = false) {
		$params = array('section'=>'body',
		'partNumber'=>$partNumber,
		'elName'=>$elName,
		'isMeta'=>true,
		'asNode'=>$asNode);


		return $this->getNodeDataByParams($params);

	}

	public function getBodyPartItemCount($partNumber) {
		$count = $this->xpath->evaluate("count(//tei:body/tei:div[@type='part'][@n='$partNumber']/tei:div[@type='libraryItem'])");
		return $count;
	}

	public function getBodyPartItemMeta($partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=>'body',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>true,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getBodyPartItemMetaEl($partNumber, $itemNumber, $elName, $asNode = false) {
		$params = array('section'=>'body',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>true,
		'elName'=>$elName,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getBodyPartItemContent($partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=>'body',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>false,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}



	public function getFrontPartCount() {
		$count = $this->xpath->evaluate("count(//tei:front/tei:div[@type='part'])");
		return $count;
	}

	public function getFrontPartMeta($partNumber, $asNode = false) {
		$params = array('section'=>'front',
		'partNumber'=>$partNumber,
		'isMeta'=>true,
		'asNode'=>$asNode);

		return $this->getNodeDataByParams($params);

	}

	public function getFrontPartMetaEl($partNumber, $elName, $asNode = false) {
		$params = array('section'=>'front',
		'partNumber'=>$partNumber,
		'elName'=>$elName,
		'isMeta'=>true,
		'asNode'=>$asNode);

		return $this->getNodeDataByParams($params);

	}

	public function getFrontPartItemCount($partNumber) {
		$count = $this->xpath->evaluate("count(//tei:front/tei:div[@type='part'][@n='$partNumber']/tei:div[@type='libraryItem'])");
		return $count;
	}

	public function getFrontPartItemMeta($partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=>'front',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>true,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getFrontPartItemMetaEl($partNumber, $itemNumber, $elName, $asNode = false) {
		$params = array('section'=>'front',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>true,
		'elName'=>$elName,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getFrontPartItemContent($partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=>'front',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>false,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);

	}


	public function getBackPartCount() {
		$count = $this->xpath->evaluate("count(//tei:back/tei:div[@type='part'])");
		return $count;
	}

	public function getBackPartMeta($partNumber, $asNode = false) {
		$params = array('section'=>'back',
		'partNumber'=>$partNumber,
		'isMeta'=>true,
		'asNode'=>$asNode);

		return $this->getNodeDataByParams($params);

	}

	public function getBackPartMetaEl($partNumber, $elName, $asNode = false) {
		$params = array('section'=>'back',
		'partNumber'=>$partNumber,
		'elName'=>$elName,
		'isMeta'=>true,
		'asNode'=>$asNode);


		return $this->getNodeDataByParams($params);

	}

	public function getBackPartItemCount($partNumber) {
		$count = $this->xpath->evaluate("count(//tei:back/tei:div[@type='part'][@n='$partNumber']/tei:div[@type='libraryItem'])");
		return $count;
	}

	public function getBackPartItemMeta($partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=>'back',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>true,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getBackPartItemMetaEl($partNumber, $itemNumber, $elName, $asNode = false) {
		$params = array('section'=>'back',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>true,
		'elName'=>$elName,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);
	}

	public function getBackPartItemContent($partNumber, $itemNumber, $asNode = false) {
		$params = array('section'=>'back',
		'partNumber'=>$partNumber,
		'itemNumber'=>$itemNumber,
		'isMeta'=>false,
		'asNode'=>$asNode);
		return $this->getNodeDataByParams($params);

	}



	public function getAuthorMeta($authorId, $asNode = false) {
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

}



?>

