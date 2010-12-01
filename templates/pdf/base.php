<?php
/**
* base.php - Controller file for PDF generator.
*
* This file is part of Anthologize {@link http://anthologize.org}.
*
* @author One Week | One Tool {@link http://oneweekonetool.org/people/}
*
* Last Modified: Fri Aug 06 15:54:55 CDT 2010
*
* @copyright Copyright (c) 2010 Center for History and New Media,
* George Mason University.
*
* Anthologize is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3, or (at your option) any
* later version.
*
* Anthologize is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
* for more details.
*
* You should have received a copy of the GNU General Public License
* along with Anthologize; see the file license.txt.  If not see
* @link http://www.gnu.org/licenses/.
*
* @package anthologize
*/


//error_reporting(0);
ini_set('max_execution_time', 90);
ini_set('memory_limit', '32M');


include_once(ANTHOLOGIZE_TEIDOM_PATH);
include_once(ANTHOLOGIZE_TEIDOMAPI_PATH);





function main() {

$ops = array('includeStructuredSubjects' => false, //Include structured data about tags and categories
		'includeItemSubjects' => false, // Include basic data about tags and categories
		'includeCreatorData' => false, // Include basic data about creators
		'includeStructuredCreatorData' => false, //include structured data about creators
		'includeOriginalPostData' => true, //include data about the original post (true to use tags and categories)
		'checkImgSrcs' => true, //whether to check availability of image sources
		'linkToEmbeddedObjects' => true,
		'indexSubjects' => false,
		'indexCategories' => false,
		'indexTags' => false,
		'indexAuthors' => false,
		'indexImages' => false,
		);


$ops['outputParams'] = $_SESSION['outputParams'];
$ops['break-items'] = true;
$ops['break-parts'] = true;

	$tei = new TeiDom($_SESSION, $ops);


	$api = new TeiApi($tei);

//testing the new setup
$tcpdf = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'anthologize' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php';
require_once($tcpdf);

//need to override addTOC() to adjust for printed page numbering and the TOC page numbering.


class AnthologizeTCPDF extends TCPDF {


	/**
	 * Output a Table of Content Index (TOC).
	 *
	 * Overriding to dig up the print page numbers, instead of PDF page numbers, for TOC
	 *
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * You can override this method to achieve different styles.
	 * @param int $page page number where this TOC should be inserted (leave empty for current page).
	 * @param string $numbersfont set the font for page numbers (please use monospaced font for better alignment).
	 * @param string $filler string used to fill the space between text and page number.
	 * @param string $toc_name name to use for TOC bookmark.
	 * @access public
	 * @author Nicola Asuni
	 * @since 4.5.000 (2009-01-02)
	 * @see addTOCPage(), endTOCPage(), addHTMLTOC()
	 */
	public function addTOC($page='', $numbersfont='', $filler='.', $toc_name='TOC') {
		$fontsize = $this->FontSizePt;
		$fontfamily = $this->FontFamily;
		$fontstyle = $this->FontStyle;
		$w = $this->w - $this->lMargin - $this->rMargin;
		$spacer = $this->GetStringWidth(chr(32)) * 4;
		$page_first = $this->getPage();
		$lmargin = $this->lMargin;
		$rmargin = $this->rMargin;
		$x_start = $this->GetX();
		$current_page = $this->page;
		$current_column = $this->current_column;
		if ($this->empty_string($numbersfont)) {
			$numbersfont = $this->default_monospaced_font;
		}
		if ($this->empty_string($filler)) {
			$filler = ' ';
		}
		if ($this->empty_string($page)) {
			$gap = ' ';
		} else {
			$gap = '';
			if ($page < 1) {
				$page = 1;
			}
		}


		//ADDED
		$this->Write(0, 'Table of Contents', '', false, 'C', true);

		foreach ($this->outlines as $key => $outline) {
			if ($this->rtl) {
				$aligntext = 'R';
				$alignnum = 'L';
			} else {
				$aligntext = 'L';
				$alignnum = 'R';
			}
			if ($outline['l'] == 0) {
				$this->SetFont($fontfamily, $fontstyle.'B', $fontsize);
			} else {
				$this->SetFont($fontfamily, $fontstyle, $fontsize - $outline['l']);
			}
			// check for page break
			$this->checkPageBreak(($this->FontSize * $this->cell_height_ratio));
			// set margins and X position
			if (($this->page == $current_page) AND ($this->current_column == $current_column)) {
				$this->lMargin = $lmargin;
				$this->rMargin = $rmargin;
			} else {
				if ($this->current_column != $current_column) {
					if ($this->rtl) {
						$x_start = $this->w - $this->columns[$this->current_column]['x'];
					} else {
						$x_start = $this->columns[$this->current_column]['x'];
					}
				}
				$lmargin = $this->lMargin;
				$rmargin = $this->rMargin;
				$current_page = $this->page;
				$current_column = $this->current_column;
			}
			$this->SetX($x_start);
			$indent = ($spacer * $outline['l']);
			if ($this->rtl) {
				$this->rMargin += $indent;
				$this->x -= $indent;
			} else {
				$this->lMargin += $indent;
				$this->x += $indent;
			}
			$link = $this->AddLink();
			$this->SetLink($link, 0, $outline['p']);
			// write the text

			$this->Write(0, $outline['t'], $link, 0, $aligntext, false, 0, false, false, 0);
			$this->SetFont($numbersfont, $fontstyle, $fontsize);
			if ($this->empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($this->isUnicodeFont()) {
					$pagenum = '{'.$pagenum.'}';
				}
			}
			$numwidth = $this->GetStringWidth($pagenum);
			if ($this->rtl) {
				$tw = $this->x - $this->lMargin;
			} else {
				$tw = $this->w - $this->rMargin - $this->x;
			}
			$fw = $tw - $numwidth - $this->GetStringWidth(chr(32));
			$numfills = floor($fw / $this->GetStringWidth($filler));
			if ($numfills > 0) {
				$rowfill = str_repeat($filler, $numfills);
			} else {
				$rowfill = '';
			}
			if ($this->rtl) {
				$pagenum = $pagenum.$gap.$rowfill.' ';
			} else {
				$pagenum = ' '.$rowfill.$gap.$pagenum;
			}
			// write the number
			$this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0);
		}
		$page_last = $this->getPage();
		$numpages = $page_last - $page_first + 1;
		if (!$this->empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);



				for ($n = 1; $n <= $this->numpages; ++$n) {
					// update page numbers
					$k = '{#'.$n.'}';
					$ku = '{'.$k.'}';
					$alias_a = $this->_escape($k);
					$alias_au = $this->_escape($ku);
					if ($this->isunicode) {
						$alias_b = $this->_escape($this->UTF8ToLatin1($k));
						$alias_bu = $this->_escape($this->UTF8ToLatin1($ku));
						$alias_c = $this->_escape($this->utf8StrRev($k, false, $this->tmprtl));
						$alias_cu = $this->_escape($this->utf8StrRev($ku, false, $this->tmprtl));
					}
					if ($n >= $page) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}

//only change to original method is here
//since $page is the page where the TOC is being inserted, subtracting it again from $np gets the printed page
//numbers to match up with the numbering in the TOC

					$ns = $this->formatTOCPageNumber($np - $page );
					$nu = $ns;
					$sdiff = strlen($k) - strlen($ns) - 1;
					$sdiffu = strlen($ku) - strlen($ns) - 1;
					$sfill = str_repeat($filler, $sdiff);
					$sfillu = str_repeat($filler, $sdiffu);
					if ($this->rtl) {
						$ns = $ns.' '.$sfill;
						$nu = $nu.' '.$sfillu;
					} else {
						$ns = $sfill.' '.$ns;
						$nu = $sfillu.' '.$nu;
					}
					$nu = $this->UTF8ToUTF16BE($nu, false);
					$temppage = str_replace($alias_au, $nu, $temppage);
					if ($this->isunicode) {
						$temppage = str_replace($alias_bu, $nu, $temppage);
						$temppage = str_replace($alias_cu, $nu, $temppage);
						$temppage = str_replace($alias_b, $ns, $temppage);
						$temppage = str_replace($alias_c, $ns, $temppage);
					}
					$temppage = str_replace($alias_a, $ns, $temppage);
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first);
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	public function Footer() {
		$cur_y = $this->GetY();
		$ormargins = $this->getOriginalMargins();
		$this->SetTextColor(0, 0, 0);
		//set style for cell border
		$line_width = 0.85 / $this->getScaleFactor();
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->getPageWidth() - $ormargins['left'] - $ormargins['right']) / 3);
			$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
			);
			$this->write1DBarcode($barcode, 'C128B', '', $cur_y + $line_width, '', (($this->getFooterMargin() / 3) - $line_width), 0.3, $style, '');
		}

		//Anthologize change: remove /totalpages
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->l['w_page'].' '.$this->getAliasNumPage();
		} else {
			$pagenumtxt = $this->l['w_page'].' '.$this->getPageNumGroupAlias();
		}
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($ormargins['right']);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->SetX($ormargins['left']);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'R');
		}
	}

}


//$start = time();
	require_once(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-anthologizer.php');
	$pdfer = new PdfAnthologizer($api, $ops);
	$pdfer->output();

//$class_pdf = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "anthologize" . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR .  'pdf' . DIRECTORY_SEPARATOR . 'class-pdf.php';
//require_once($class_pdf);
//	$pdf = new TeiPdf($api);
//	header('Content-type: application/pdf');
//	$pdf->write_pdf();
//$end = time();
//echo $end-$start;
}

main();
die();
?>
