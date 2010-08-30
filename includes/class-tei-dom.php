<?php

define('TEI', 'http://www.tei-c.org/ns/1.0');
define('HTML', 'http://www.w3.org/1999/xhtml');
define('ANTH', 'http://www.anthologize.org/ns');

class TeiDom {

	public $dom;
	public $xpath;

	public $bodyNode;
	public $projectData;
	public $userNiceNames = array();
	public $doShortcodes = true;
	public $checkImgSrcs;

	public $front1Title = "Dedication";
	public $front2Title = "Acknowledgements";

	function __construct($sessionArray, $checkImgSrcs = true) {

		$this->projectData = $sessionArray;

		//projectMeta has subtitle
		$projectMeta = get_post_meta($this->projectData['project_id'], 'anthologize_meta', true );
		$this->projectData['subtitle'] = $projectMeta['subtitle'];

		$projectWPData = get_post($this->projectData['project_id']); // has date info
		$this->projectData['post_date'] = $projectWPData->post_date;
		$this->projectData['post_date_gmt'] = $projectWPData->post_date_gmt;
		$this->projectData['post_modified'] = $projectWPData->post_modified;
		$this->projectData['post_modified_gmt'] = $projectWPData->post_modified_gmt;
		$this->projectData['guid'] = $projectWPData->guid;

		$this->checkImgSrcs = $checkImgSrcs;

		if( isset($this->projectData['do-shortcodes']) && $this->projectData['do-shortcodes'] == false ) {
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


	public function buildProjectData() {

		$partsData = new WP_Query(array('post_parent'=>$this->projectData['project_id'], 'post_type'=>'anth_part'));

		$partObjectsArray = $partsData->posts;
		usort($partObjectsArray, array('TeiDom', 'postSort'));

		$partNumber = 0;
		foreach($partObjectsArray as $partObject) {
			$newPart = $this->newPart($partObject);
			$newPart->setAttribute('n', $partNumber);
			//TODO: find a way to set no limit to post_per_page
			$libraryItemsData = new WP_Query(array('post_parent'=>$partObject->ID, 'post_type'=>'anth_library_item', 'posts_per_page'=>200));
			$libraryItemObjectsArray = $libraryItemsData->posts;
			//sort objects, by menu_order, then ID
			usort($libraryItemObjectsArray, array('TeiDom', 'postSort'));
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

		$outParamsNode = $this->xpath->query("//anth:outputParams")->item(0);
		//font-size
		$fontSizeNode = $this->xpath->query("anth:param[@name='font-size']", $outParamsNode)->item(0);
		$fontSizeNode->appendChild($this->dom->createTextNode($this->projectData['font-size']));

		//paper-type
		$paperTypeNode = $this->xpath->query("anth:param[@name='paper-type']", $outParamsNode)->item(0);
		$paperTypeNode->appendChild($this->dom->createTextNode($this->projectData['page-size']));
		//paper-size
		$pageHNode = $this->xpath->query("anth:param[@name='page-height']", $outParamsNode)->item(0);
		$pageWNode = $this->xpath->query("anth:param[@name='page-width']", $outParamsNode)->item(0);


		switch($this->projectData['page-size']) {
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
		$fontFamilyNode->appendChild($this->dom->createTextNode($this->projectData['font-face']));

	}

	public function addPublicationStmt() {

		//cr
		$litAvailNode = $this->xpath->query("//tei:publicationStmt/tei:availability[@rend='literal']")->item(0);
		$litAvailNode->appendChild($this->sanitizeString("Creative Commons " . $this->projectData['cctype']));

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

	public function addSourceDesc() {

		$projectBibl = $this->xpath->query("//tei:sourceDesc/tei:listBibl/tei:bibl[@type='project']")->item(0);

		$identNode = $this->xpath->query("tei:ident", $projectBibl)->item(0);
		$identNode->appendChild($this->dom->createCDATASection($this->projectData->guid) );
		$titleNode = $this->xpath->query("tei:title[@type='main']", $projectBibl)->item(0);
		$titleNode->appendChild($this->sanitizeString($this->projectData['post-title']));

		$subTitleNode = $this->xpath->query("tei:title[@type='sub']", $projectBibl)->item(0);
		$subTitleNode->appendChild($this->sanitizeString($this->projectData['subtitle']));

		$createdNode = $this->xpath->query("tei:date[@type='created']", $projectBibl)->item(0);
		$createdNode->appendChild($this->dom->createTextNode($this->projectData->post_date));

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

	public function addTitlePage() {

		//"editors" copyright and title page

		$authorsNode = $this->xpath->query("//tei:docAuthor")->item(0);
		$authorsNode->appendChild($this->sanitizeString($this->projectData['cname'] . ', ' . $this->projectData['authors']));

		$docEditionNode = $this->xpath->query("//tei:docEdition")->item(0);
		$docEditionNode->appendChild($this->sanitizeString($this->projectData['edition']));


		//TODO: also slap title into titlePage

		$frontPageNode = $this->xpath->query("//tei:titlePage")->item(0);

		$mainTitle = $this->xpath->query("//tei:titlePart[@type='main']", $frontPageNode)->item(0);
		$mainTitle->appendChild($this->sanitizeString($this->projectData['post-title']));

		$subTitle = $this->xpath->query("//tei:titlePart[@type='sub']", $frontPageNode)->item(0);
		$subTitle->appendChild($this->sanitizeString($this->projectMeta['subtitle']));





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
		//strip out blogger tracker
		$imgNodes = $this->xpath->query('//img[contains(@src, "blogger.googleusercontent.com/tracker")]');
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
		$avlPNode->appendChild($this->dom->createTextNode('Copyright ' . $this->projectData['cyear'] . ', ' . $this->projectData['cname']));
		if($this->projectData['ctype'] == 'c') {
			return;
		}

		$ccNode = $this->dom->createElementNS(TEI, 'p');

		switch($this->projectData['cctype']) {
			case 'by':
				$ccNode->appendChild($this->dom->createTextNode('cc-by'));
			break;

			case 'by-sa':
				$ccNode->appendChild($this->dom->createTextNode('cc-by-sa'));
			break;

			case 'by-nd':
				$ccNode->appendChild($this->dom->createTextNode('cc-by-nd'));
			break;

			case 'by-nc':
				$ccNode->appendChild($this->dom->createTextNode('cc-by-nc'));
			break;

			case 'by-nc-sa':
				$ccNode->appendChild($this->dom->createTextNode('cc-by-nc-sa'));
			break;

			case 'by-nc-nd':
				$ccNode->appendChild($this->dom->createTextNode('cc-by-nc-nd'));
			break;

			default:

			break;
		}
		$avlPNode->parentNode->appendChild($ccNode);
	}

	public function newAuthor($user) {

		if(is_integer($user)) {
			$user = get_userdata($user);
		}

		$newAuthor = $this->dom->createElementNS(TEI, 'author');
		$newAuthor->setAttribute('rend', 'structured');
		$newPersName = $this->dom->createElementNS(TEI, 'persName');
		$newPersName->appendChild($this->dom->createElementNS(TEI, 'forename', $user->first_name));
		$newPersName->appendChild($this->dom->createElementNS(TEI, 'surname', $user->last_name) );
		$ident = $this->dom->createElementNS(TEI, 'ident');
		$ident->appendChild($this->dom->createCDataSection($user->user_url));
		$ident->setAttribute('type', 'url');
		$newPersName->appendChild($ident);
		$this->userNiceNames[] = $user->user_nicename;
	}


//TODO: blow this up into newAuthor
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
			$newPersName->appendChild($this->dom->createElementNS(TEI, 'forename', $userObject->first_name));
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

		if($this->doShortcodes) {
			$content = do_shortcode($content);
		} else {
			$content = $this->sanitizeShortCodes($content);
		}

		$newPostContent->appendChild($this->sanitizeString($libraryItemObject->post_content, true));
/*

		$content = wpautop($content);


*/

/*

$meta = get_post_meta($libraryItemObject->ID, 'anthologize_meta', true );

print_r($meta);
print_r(wp_get_post_terms($meta['original_post_id']));
print_r(get_post($meta['original_post_id']));
print_r(get_userdata(1));
die();
*/

/*
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
*/
		return $newPostContent;
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
		$bibl = $this->dom->createElementNS(TEI, 'bibl');
		//TODO: check if content is native, based on the GUID. if content native, dig up author info
		//from userID. Otherwise/and, go with info from boones
		// $author_name = get_post_meta( $item_id, 'author_name', true );

		//TODO: above might be old. Check nativeness by looking at whether dissplay name is set for username

		$authorObject = get_userdata($postObject->post_author);
		//$this->addPerson($authorObject);

		if($authorObject) {

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

		$content = $this->sanitizeEntities($content);
		if ($isMultiline) {
			$content = wpautop($content);
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

	     $pattern = get_shortcode_regex();

	     return preg_replace_callback('/'.$pattern.'/s', array('TeiDom', 'sanitizeShortCode'), $content);
	     //TODO: go to town on additional shortcodes not being expanded
	}

	private function sanitizeEntities($content) {
		//TODO: sort out the best order to convert characters and sanitizing stuff.

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

	/* Accessor Methods */


	public function getTeiString() {
		return $this->dom->saveXML();
	}

	public function getTeiDom() {
		return $this->dom;
	}



	public static function getFileName($sessionArray) {


        $text = strtolower($sessionArray['post-title']);
        $fileName = preg_replace('/\s/', "_", $text);
        $fileName = mb_ereg_replace('/[^\w\-]/', '', $fileName);
        $fileName = trim($fileName, "_");
        $fileName = rtrim($fileName, ".");

        return $fileName;
	}



}



?>

