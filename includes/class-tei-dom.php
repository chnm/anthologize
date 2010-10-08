<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', 'http://www.anthologize.org/ns');

class TeiDom {

	public $includeStructuredSubjects = true;
	public $includeItemSubjects = true;
	public $includeCreatorData = true;
	public $includeStructuredCreatorData = true;
	public $includeOriginalPostData = true;
	public $includeDeepDocumentData = true;
	public $avatarSize = '96';
	public $avatarDefault = "http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536";
	public $doShortcodes = true;
	public $checkImgSrcs = true;


	public $front1Title = "Dedication";
	public $front2Title = "Acknowledgements";

	public $dom;
	public $xpath;

	public $bodyNode;
	public $projectData;
	public $knownPersons = array();
	public $knownSubjects = array();


	function __construct($sessionArray, $ops = array()) {

		foreach($ops as $op=>$value) {
			$this->$op = $value;
		}
		$this->projectData = $sessionArray;

		if(isset($this->outputParams['gravatar-default'])) {
			$this->avatarDefault = $this->outputParams['gravatar-default'];
		}

		//projectMeta has subtitle
		$projectMeta = get_post_meta($this->projectData['project_id'], 'anthologize_meta', true );
		$this->projectData['subtitle'] = $projectMeta['subtitle'];

		$projectWPData = get_post($this->projectData['project_id']); // has date info
		$this->projectData['post_date'] = $projectWPData->post_date;
		$this->projectData['post_date_gmt'] = $projectWPData->post_date_gmt;
		$this->projectData['post_modified'] = $projectWPData->post_modified;
		$this->projectData['post_modified_gmt'] = $projectWPData->post_modified_gmt;
		$this->projectData['guid'] = $projectWPData->guid;


		if( isset($this->projectData['outputParams']['do-shortcodes']) && $this->projectData['outputParams']['do-shortcodes'] == false ) {
			$this->doShortcodes = false;
		}

		$this->dom = new DOMDocument('1.0', 'UTF-8');
		$templatePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" .
		DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tei' . DIRECTORY_SEPARATOR .'teiEmpty.xml';
		$this->dom->load($templatePath);
		$this->dom->preserveWhiteSpace = false;
		$this->setXPath();

		$this->buildProjectData();
		$this->addOutputDesc();
		$this->addPublicationStmt();
		$this->addFileDesc();
		$this->addTitlePageBibl();
		$this->addEncodingDesc();
		$this->addFrontMatter();

		$this->sanitizeContent();
		$this->addBackMatter();

	}
	public function setXPath() {
		$this->xpath = new DOMXPath($this->dom);
		$this->xpath->registerNamespace('tei', TEI);
		$this->xpath->registerNamespace('html', HTML);
		$this->xpath->registerNamespace('anth', ANTH);
		$this->bodyNode = $this->xpath->query("//tei:body")->item(0);
		$this->frontNode = $this->xpath->query("//tei:front")->item(0);
		$this->backNode = $this->xpath->query("//tei:back")->item(0);
		$this->structuredSubjectList = $this->xpath->query("//tei:list[@xml:id='subjects']")->item(0);
		$this->structuredPersonList = $this->xpath->query("//tei:sourceDesc/tei:listPerson")->item(0);

	}


	public function buildProjectData() {

		$partsData = new WP_Query(array('post_parent'=>$this->projectData['project_id'], 'post_type'=>'anth_part'));

		$partObjectsArray = $partsData->posts;
		usort($partObjectsArray, array('TeiDom', 'postSort'));


		$partNumber = 0;
		foreach($partObjectsArray as $partObject) {
			$newPart = $this->newPart($partObject);
			$newPart->setAttribute('n', $partNumber);
			$newPart->setAttribute('xml:id', "body-$partNumber");
			if($this->includeDeepDocumentData) {

			}
			//TODO: find a way to set no limit to post_per_page without the magic big number
			$libraryItemsData = new WP_Query(array('post_parent'=>$partObject->ID, 'post_type'=>'anth_library_item', 'posts_per_page'=>200));
			$libraryItemObjectsArray = $libraryItemsData->posts;



			//sort objects, by menu_order, then ID
			usort($libraryItemObjectsArray, array('TeiDom', 'postSort'));
			$itemNumber = 0;

			foreach($libraryItemObjectsArray as $libraryItemObject) {

				$origPostData = get_post_meta($libraryItemObject->ID, 'anthologize_meta', true );
				$libraryItemObject->original_post_id = $origPostData['original_post_id'];

				$newItem = $this->newItem($libraryItemObject);
				$newItem->setAttribute('xml:id', "body-$partNumber-$itemNumber");
				if($this->includeStructuredSubjects) {
					$this->addStructuredSubjects($libraryItemObject->original_post_id);
				}

				$newItem->setAttribute('n', $itemNumber);
				$newPart->appendChild($newItem);
				$itemNumber++;
			}
			$this->bodyNode->appendChild($newPart);
			$partNumber++;
		}
	}

	public function addOutputDesc() {

		$outParamsNode = $this->xpath->query("//anth:outputParams")->item(0);

		foreach($this->outputParams as $name=>$value) {
			$newParam = $this->dom->createElementNS(ANTH, 'param', $value);
			$newParam->setAttribute('name', $name);
			$outParamsNode->appendChild($newParam);
			if($name == 'page-size') {
				$widthParam = $this->dom->createElementNS(ANTH, 'param');
				$widthParam->setAttribute('name', 'page-width');
				$heightParam = $this->dom->createElementNS(ANTH, 'param');
				$heightParam->setAttribute('name', 'page-height');
				switch($value) {
					case 'A4':
						$widthParam->nodeValue = '210mm';
						$heightParam->nodeValue = '297mm';
					break;

					case 'letter':
						$widthParam->nodeValue = '8.5in';
						$heightParam->nodeValue = '11in';
					break;
				}
				$outParamsNode->appendChild($widthParam);
				$outParamsNode->appendChild($heightParam);
			}
		}
	}

	public function addPublicationStmt() {

		//cr
		$litAvailNode = $this->xpath->query("//tei:publicationStmt/tei:availability[@rend='literal']")->item(0);
		$litAvailNode->appendChild($this->sanitizeString("Creative Commons - " . strtoupper( $this->projectData['cctype'] )));

		$strAvailNode = $this->xpath->query("//tei:publicationStmt//tei:ab[@rend='structured']")->item(0);
		$strAvailNode->appendChild($this->dom->createTextNode("cc-" . $this->projectData['cctype']) );

		//date
		$pubDateNode = $this->xpath->query("//tei:publicationStmt/tei:date")->item(0);
		$pubDateNode->appendChild($this->dom->createTextNode($this->projectData['cyear']));

	}

	public function addFileDesc() {

		$titleNode = $this->xpath->query('/tei:TEI/tei:teiHeader/tei:fileDesc/tei:titleStmt/tei:title')->item(0);
		$titleNode->appendChild($this->sanitizeString($project->post_title));

		//edition
		$edNode = $this->xpath->query("//tei:editionStmt/tei:ab[@rend='literal']")->item(0);
		$edNode->appendChild($this->sanitizeString($this->projectData['edition']));

	}

	public function addTitlePageBibl() {

		$projectBibl = $this->xpath->query("//tei:head[@type='titlePage']/tei:bibl")->item(0);

		$identNode = $this->xpath->query("tei:ident", $projectBibl)->item(0);
		$identNode->appendChild($this->dom->createCDATASection($this->projectData['guid']) );
		$titleNode = $this->xpath->query("tei:title[@type='main']", $projectBibl)->item(0);
		$titleNode->appendChild($this->sanitizeString($this->projectData['post-title']));

		$subTitleNode = $this->xpath->query("tei:title[@type='sub']", $projectBibl)->item(0);
		$subTitleNode->appendChild($this->sanitizeString($this->projectData['subtitle']));

		if($this->includeDeepDocumentData) {
			$projectPostData = get_post($this->projectData['project_id']);
			$userData = get_userdata($projectPostData->post_author);

			$projectBibl->appendChild($this->newAuthor($userData, 'projectCreator'));

			$createdNode = $this->dom->createElementNS(TEI, 'date');
			$createdNode->setAttribute('type', 'created');
			$projectBibl->appendChild($createdNode);
			$createdNode->appendChild($this->dom->createTextNode($this->projectData['post_date']));
		}


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
		$f1TitleNode->appendChild($this->sanitizeString(htmlspecialchars($this->front1Title)));
		$f1Node->appendChild($this->sanitizeString(htmlspecialchars($this->projectData['dedication']), true));

		//front2
		$f2Node = $this->xpath->query("//tei:front/tei:div[@n='1']")->item(0);
		$f2TitleNode = $this->xpath->query("tei:head/tei:title", $f2Node )->item(0);
		//TODO: change when UI changes currently hardcoded as acknowledgements
		$f2TitleNode->appendChild($this->sanitizeString(htmlspecialchars($this->front2Title)));


		$f2Node->appendChild($this->sanitizeString(htmlspecialchars($this->projectData['acknowledgements']), true));
	}

	public function addBackMatter() {
		$this->doIndexing();
	}
	public function sanitizeMedia() {
		$this->sanitizeImages();

		if($this->linkToEmbeddedObjects) {
			$this->sanitizeEmbeds();
		}
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
		//strip out blogger tracker
		$imgNodes = $this->xpath->query('//img[contains(@src, "blogger.googleusercontent.com/tracker")]');
		foreach($imgNodes as $imgNode) {
			$imgNode->parentNode->removeChild($imgNode);
		}

	}

	public function addStructuredPerson($wpUserObj) {

		$newPerson = $this->newStructuredPerson($wpUserObj);
		if($newPerson) {
			$this->structuredPersonList->appendChild($newPerson);
		}

	}

	public function newStructuredPerson($wpUserObj) {

		$id = $wpUserObj->user_login;

		if( array_key_exists($id, $this->knownPersons)) {
			$this->knownPersons[$id] = $this->knownPersons[$id] + 1;
			$personCountNode = $this->xpath->query("//tei:person[@xml:id = '$id']/tei:persName/tei:num")->item(0);
			$personCountNode->nodeValue = $this->knownPersons[$id];
			return false;
		}
		$this->knownPersons[$id] = 1;

		$person = $this->dom->createElementNS(TEI, 'person');
		$person->setAttribute('xml:id', $id );
		$persName = $this->dom->createElementNS(TEI, 'persName');
		$name = $this->dom->createElementNS(TEI, 'name');
		$name->appendChild($this->sanitizeString($wpUserObj->display_name));
		$firstname = $this->dom->createElementNS(TEI, 'firstname');
		$firstname->appendChild($this->sanitizeString($wpUserObj->first_name));
		$surname = $this->dom->createElementNS(TEI, 'surname');
		$surname->appendChild($this->sanitizeString($wpUserObj->last_name));
		$count = $this->dom->createElementNS(TEI, 'num');
		$count->setAttribute('type', 'count');
		$count->appendChild($this->dom->createTextNode('1'));


		$desc = $this->dom->createElementNS(TEI, 'note');
		$desc->setAttribute('type', 'description');
		$desc->appendChild($this->sanitizeString($wpUserObj->description, true));

		$email = $this->dom->createElementNS(TEI, 'email');
		$email->appendChild($this->sanitizeString($wpUserObj->user_email));

		//adding the nodes to the TEI as I build them because otherwise funky and wrong xmlns:defaults are added
		//don't get why, just that this works!

		$figure = $this->dom->createElementNS(TEI, 'figure');
		$person->appendChild($figure);
		$graphic = $this->dom->createElementNS(TEI, 'graphic');
		$graphic->setAttribute('type', 'gravatar');
		$graphic->setAttribute('url', $this->newGravatar($wpUserObj->user_email, true));
		$figure->appendChild($graphic);
		$graphic->appendChild($this->newGravatar($wpUserObj->user_email));

		$persName->appendChild($name);
		$persName->appendChild($firstname);
		$persName->appendChild($surname);
		$persName->appendChild($firstname);
		$persName->appendChild($email);
		$persName->appendChild($count);

		$person->appendChild($persName);

		$person->appendChild($desc);
		return $person;

	}

	public function newAuthor($userData, $role='') {
		$author = $this->dom->createElementNS(TEI, 'author');
		$author->setAttribute('role', $role);

		if(is_string($userData)) {
			$author->appendChild($this->sanitizeString($userData));
			return $author;
		}
		$author->appendChild($this->sanitizeString($userData->display_name));
		$author->setAttribute('ref', $userData->user_login);
		return $author;
	}

	public function newSubjectStructuredItem($subject) {

		//if a tag and category have same slug, differentiate in id
		$id = $subject->taxonomy . '-' . $subject->slug;
		if( array_key_exists($id, $this->knownSubjects) ) {
			$this->knownSubjects[$id] = $this->knownSubjects[$id] + 1;
			$subjectCountNode = $this->xpath->query("//tei:item[@xml:id = '$id']/tei:num")->item(0);
			$subjectCountNode->nodeValue = $this->knownSubjects[$id];
			return false;
		}
		$this->knownSubjects[$id] = 1;
		$item = $this->dom->createElementNS(TEI, 'item');
		$item->setAttribute('xml:id', $id );
		$item->setAttribute('type', $subject->taxonomy);

		$ident = $this->dom->createElementNS(TEI, 'ident');
		$ident->setAttribute('type', 'guid');
		$ident->appendChild($this->dom->createCDATASection($subject->guid));

		$desc = $this->dom->createElementNS(TEI, 'desc');
		$desc->appendChild($this->sanitizeString($subject->description, true));

		$num = $this->dom->createElementNS(TEI, 'num');
		$num->setAttribute('type', 'count');
		$num->appendChild($this->dom->createTextNode('1'));

		$item->appendChild($ident);
		$item->appendChild($desc);
		$item->appendChild($num);
		$item->appendChild($this->sanitizeString($subject->name));

		return $item;
	}

	public function newSubjectRefString($subject) {
		$rs = $this->dom->createElementNS(TEI, 'rs');
		$rs->setAttribute('ref', $subject->taxonomy . '-' . $subject->slug);
		$rs->setAttribute('type', $subject->taxonomy);
		$rs->appendChild($this->sanitizeString($subject->name));
		return $rs;
	}

	public function addStructuredSubjects($postID) {

		$subjects = $this->fetchPostSubjects($postID);
		foreach($subjects as $subject) {
			$newSubject = $this->newSubjectStructuredItem($subject);
			if($newSubject) {
				$this->structuredSubjectList->appendChild($newSubject);
			}

		}
	}

	public function addItemSubjects($postID, $node) {
		$subjects = $this->fetchPostSubjects($postID);
		$list = $this->dom->createElementNS(TEI, 'list');
		$list->setAttribute('type', 'subjects');
		foreach($subjects as $subject) {
			$item = $this->dom->createElementNS(TEI, 'item');
			$item->appendChild($this->newSubjectRefString($subject));
			$list->appendChild($item);
		}
		$node->appendChild($list);
	}

	public function fetchPostSubjects($postID) {
		$subjects = wp_get_post_tags($postID);

		$catIds = wp_get_post_categories($postID); //srsly, WordPress?
		foreach($catIds as $catId) {
			$cat = get_category($catId); //srsly?
			//category and term data structures don't align, so duplicate category data so I can use same code later
			$cat->description = $cat->category_description;
			$subjects[] = $cat;
		}

		//add in the links here to keep this sort of processing in one place
		foreach($subjects as $subject) {
			switch ($subject->taxonomy) {

				case 'post_tag':
					$subject->guid = get_tag_link($subject->term_id);
					$subject->taxonomy = "tag";
				break;

				case 'category':
					$subject->guid = get_category_link($subject->term_id);
				break;
			}
		}

		return $subjects;
	}

	public function sanitizeEmbeds() {
		$objects = $this->xpath->query("//object");

		foreach($objects as $object) {
			$link = $this->xpath->evaluate("embed/@src", $object)->item(0)->nodeValue;
			$aNode = $this->dom->createElement('a', "[Link to embedded object]");
			$aNode->setAttribute('href', $link);
			$object->parentNode->replaceChild($aNode, $object);
		}
	}


	public function newPart($partObject) {
		$newPart = $this->dom->createElementNS(TEI, 'div');
		$newPart->setAttribute('type', 'part');
		$newPart->appendChild($this->newHead($partObject));
		return $newPart;
	}

	public function newItem($libraryItemObject) {
		$newItem = $this->dom->createElementNS(TEI, 'div');
		$newItem->setAttribute('type', 'libraryItem');
		$newItem->setAttribute('subtype', 'html');
		$newItem->appendChild($this->newHead($libraryItemObject));

		$content = $libraryItemObject->post_content;

		if($this->doShortcodes) {
			$content = do_shortcode($content);
		} else {
			$content = $this->sanitizeShortCodes($content);
		}

		$newItem->appendChild($this->sanitizeString($libraryItemObject->post_content, true));

		return $newItem;
	}

	public function newHead($postObject) {

		$newHead = $this->dom->createElementNS(TEI, 'head');
		$title = $this->dom->createElementNS(TEI, 'title');
		$title->appendChild($this->sanitizeString($postObject->post_title));

		$guid = $this->dom->createElementNS(TEI, 'ident');
		$guid->appendChild($this->dom->createCDataSection($postObject->guid));
		$guid->setAttribute('type', 'guid');
		$newHead->appendChild($title);
		$newHead->appendChild($guid);

		//TODO: check if content is native, based on the GUID. if content native, dig up author info
		//from userID. Otherwise/and, go with info from boones
		// $author_name = get_post_meta( $item_id, 'author_name', true );

		//TODO: above might be old. Check nativeness by looking at whether dissplay name is set for username

		switch($postObject->post_type) {
			case 'anth_part':

			break;

			case 'anth_library_item':

				//gets the wordpress author info
				$itemCreatorObject = get_userdata($postObject->post_author);
				$bibl = $this->dom->createElementNS(TEI, 'bibl');
				$newHead->appendChild($bibl);
				if($itemCreatorObject) {
					$bibl->appendChild($this->newAuthor($itemCreatorObject, 'itemCreator'));
				}


				//work with the anthologize data
				$meta = get_post_meta($postObject->ID, 'anthologize_meta', true );
				if($meta && isset($meta['author_name_array'])) {
					foreach ($meta['author_name_array'] as $authorName) {
						//originalCreator might not be correct, but until more data comes from the UI, it's the best guess
						$bibl->appendChild($this->newAuthor($authorName, 'anthologizeMeta'));
					}
				}

				if($this->includeItemSubjects) {
					$this->addItemSubjects($postObject->original_post_id, $newHead);
				}

				if($this->includeOriginalPostData) {
					$origPostData = get_post($postObject->original_post_id);
					$origCreator = get_userdata($origPostData->post_author);
					$bibl->appendChild($this->newAuthor($origCreator, 'originalCreator') );
					if($this->includeStructuredCreatorData) {
						$this->addStructuredPerson($origCreator);
					}
				}
			break;


		}


		//$this->addPerson($authorObject);






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

		$content = $this->sanitizeEntities($content);
		if ($isMultiline) {
			$content = wpautop($content);
			$content = $this->sanitizeShortCodes($content);


			$element = "div";
		} else {
			$element = "span";
		}



		//using loadHTML because it is more forgiving than loadXML
		$tmpHTML = new DOMDocument('1.0', 'UTF-8');
		//conceal the Warning about bad html with @
		//loadHTML adds head and body tags silently
		@$tmpHTML->loadHTML("<?xml version='1.0' encoding='UTF-8' ?><$element xmlns='http://www.w3.org/1999/xhtml'>$content</$element>" );
		if($this->checkImgSrcs) {
			$this->checkImgSrcs($tmpHTML);

		}

		$contentDiv = $tmpHTML->getElementsByTagName($element)->item(0);
		//$contentDiv->setAttribute('xmlns', HTML);
		$imported = $this->dom->importNode($contentDiv, true);
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

		$content = do_shortcode($content);
		if($this->doShortcodes) {
		    $pattern = get_shortcode_regex();
			return preg_replace_callback('/'.$pattern.'/s', array('TeiDom', 'sanitizeShortCode'), $content);
		} else {
			return strip_shortcodes($content);
		}

	}

	private function sanitizeEntities($content) {
		//TODO: sort out the best order to convert characters and sanitizing stuff.

		return str_replace("&nbsp;", " ", $content);
	}

	private function sanitizeShortCode($m) {
		//modified from WP do_shortcode_tag() wp_includes/shortcodes.php

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

	private function getID($node) {
		//if its in the HTML, check if it already has an id
		$existingID = $node->getAttribute('id');
		if($node->getAttribute('id')) {
			return $node->getAttribute('id');
		}
		if($node->getAttribute('xml:id')) {
			return $node->getAttribute('xml:id');
		}
		$parentItem = $this->getParentItem($node);
		$itemId = $parentItem->getAttribute('xml:id');
		$nameMatches = $parentItem->getElementsByTagname($node->nodeName);
		$index = 0;
		foreach($nameMatches as $nameMatch) {
			if ($node === $nameMatch) {
				$id =  $itemId . "-$node->nodeName-$index";
				$node->setAttribute('xml:id', $id);
				return $id;
			}
			$index ++;
		}
	}

	public function newGravatar($email, $urlOnly = false, $size = false) {
		if( ! $size) {
			$size = isset($this->outputParams['avatar-size']) ? $this->outputParams['avatar-size'] : $this->avatarSize;
		}

		$grav_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $this->avatarDefault );
		if($urlOnly) {
			return $grav_url;
		}
		//building it myself rather using WP's function so I build a node in the right document
		$grav = $this->dom->createElementNS(HTML, 'img');
		$src = $grav->setAttribute('src', $grav_url . "%26s=" . $size);
		return $grav;
	}

	public function doIndexing() {
		if($this->indexSubjects) {
			$this->backNode->appendChild($this->indexSubjects());
		}

		if($this->indexCategories) {
			$this->backNode->appendChild($this->indexCategories());
		}

		if($this->indexTags) {
			$this->backNode->appendChild($this->indexTags());
		}

		if($this->indexAuthors) {
			$this->backNode->appendChild($this->indexAuthors());
		}

		if($this->indexImages) {
			$this->backNode->appendChild($this->indexImages());
		}
	}

	public function newIndex($type, $ops = false) {
		$indexDiv = $this->dom->createElementNS(TEI, 'div');
		$indexDiv->setAttribute('type', 'index');
		$indexDiv->setAttribute('subtype', $type);
		$indexCount = $this->xpath->evaluate("count(//div[@type = 'index'])");
		$indexDiv->setAttribute('n', $indexCount);
		return $indexDiv;

	}

	public function newIndexListItem($labelNode, $targetNodeData) {

		$item = $this->dom->createElement('item');
		$teiLabelNode = $this->dom->createElement('label');
		$item->appendChild($teiLabelNode);
		$teiLabelNode->appendChild($labelNode);

		foreach($targetNodeData as $type=>$targetNodes) {
			if( ($type == 'node') || ($type == 'label') ) {
				continue;
			}

			$listRef = $this->dom->createElement('listRef');
			$item->appendChild($listRef);
			$listRef->setAttribute('type', $type);


			foreach($targetNodes as $type=>$itemNode) {
				$targetData = $this->getNodeTargetData($itemNode);
				$rs = $this->dom->createElement('rs');
				$listRef->appendChild($rs);
				$rs->setAttribute('ref', $targetData['id']);
				$rs->appendChild($this->sanitizeString($targetData['title']));
			}
		}

		return $item;
	}

	public function indexNodeList($nodes, $indexTypes) {
		$nodesArray = array();



		foreach($nodes as $node) {

			$key = $this->getNodeKeyForIndex($node);
			$labelNode = $this->getNodeLabelForIndex($node);
			foreach($indexTypes as $indexType) {
				switch($indexType) {
					case 'items':
						$targetItem = $this->getParentItem($node);
					break;

					case 'direct':


						$targetItem = $node;
					break;
				}

				if(array_key_exists($key, $nodesArray)) {
					$nodesArray[$key][$indexType][] = $targetItem;
				} else {
					$nodesArray[$key] = array('label'=>$labelNode, 'node'=>$node, $indexType=>array($targetItem));
				}
			}

		}


		ksort($nodesArray, SORT_STRING);
		$list = $this->dom->createElement('list');

		foreach($nodesArray as $key=>$data) {
			$list->appendChild($this->newIndexListItem($data['label'], $data));
		}

		return $list;
	}

	public function indexAuthors() {
		$nodes = $this->xpath->query("//tei:body//tei:author");
		$list = $this->indexNodeList($nodes, array('items'));
		$index = $this->newIndex('authors');
		$index->appendChild($list);
		return $index;

	}

	public function indexCategories() {
		$nodes = $this->xpath->query("//tei:list[@type='subjects']/tei:item/tei:rs[@type='category']/span");
		$list = $this->indexNodeList($nodes, array('items'));
		$index = $this->newIndex('categories');
		$index->appendChild($list);
		return $index;
	}

	public function indexTags() {
		$nodes = $this->xpath->query("//tei:list[@type='subjects']/tei:item/tei:rs[@type='tag']/span");
		$list = $this->indexNodeList($nodes, array('items'));
		$index = $this->newIndex('tags');
		$index->appendChild($list);
		return $index;
	}

	public function indexSubjects() {
		$nodes = $this->xpath->query("//tei:list[@type='subjects']/tei:item/tei:rs/span");
		$list = $this->indexNodeList($nodes, array('items'));
		$index = $this->newIndex('subjects');
		$index->appendChild($list);
		return $index;
	}

	public function indexImages() {
		$nodes = $this->xpath->query("//tei:body//img");
		$list = $this->indexNodeList($nodes, array('items', 'direct') );
		$index = $this->newIndex('images');
		$index->appendChild($list);
		return $index;
	}


	/* Accessor Methods */


	public function getTeiString() {
		return $this->dom->saveXML();
	}

	public function getTeiDom() {
		return $this->dom;
	}

	public function getFileName($sessionArray) {

        $text = strtolower($sessionArray['post-title']);
        $fileName = preg_replace('/\s/', "_", $text);
        $fileName = mb_ereg_replace('/[^\w\-]/', '', $fileName);
        $fileName = trim($fileName, "_");
        $fileName = rtrim($fileName, ".");

        return $fileName;
	}

	public function getNodeTargetData($node) {
		//get id and a label/title

		//make sure that HTML targets have an ID
		$id = $this->getID($node);

		//TODO: switch approach to finding labels based on node type
		$titleNodes = $this->xpath->query("tei:head/tei:title", $node);
		if($titleNodes->length == 0) {
			$title = $node->nodeName;
		} else {
			$title = $titleNodes->item(0)->firstChild->nodeValue;
		}

		return array('id'=>$id, 'title'=>$title);
	}

	public function getParentItem($node) {
		while( $node->getAttribute('type') != 'libraryItem') {
			$node = $node->parentNode;

		}
		return $node;
	}

	public function getNodeLabelForIndex($node) {

		if ($node->firstChild->nodeName == 'span') {
			return $node->firstChild;
		}

		return $node->cloneNode();

	}

	public function getNodeKeyForIndex($node, $keyBy = false) {

		switch($node->nodeName) {
			case 'img':
				$keyBy = 'titleAtt';
			break;
		}

		switch ($keyBy) {
			case 'titleAtt':
				return $node->getAttribute('title');
			break;


			default:
				return strtolower($node->firstChild->nodeValue);
			break;
		}


	}


}


