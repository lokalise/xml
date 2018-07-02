<?php

/**
 * DOMXPathPDF relies on DOMXPathExtended class
 *
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;

/**
 * DOMXPathPDFblocks allows to operate on text blocks within PDF document
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/DOMXPathPDFblocks.php $
 */

class DOMXPathPDFblocks extends DOMXPathExtended
    {

	/**
	 * PDF to HTML utility
	 *
	 * @requiredcommand /usr/bin/pdftohtml poppler-utils
	 *
	 * @untranslatable /usr/bin/pdftohtml
	 */
	const PDFTOHTML = "/usr/bin/pdftohtml";

	/**
	 * Parse PDF and construct DOMXPathPDFblocks
	 *
	 * @param string $pdf Original PDF
	 *
	 * @return void
	 *
	 * @untranslatable .pdf
	 * @untranslatable -nodrm -xml -stdout
	 * @untranslatable .pdf 2> /dev/null
	 * @untranslatable utf-8
	 */

	public function __construct($pdf)
	    {
		$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(uniqid(mt_rand(), true));
		file_put_contents($file . ".pdf", $pdf);
		$pdf2xml = shell_exec(self::PDFTOHTML . " -nodrm -xml -stdout " . $file . ".pdf 2> /dev/null");
		unlink($file . ".pdf");

		$doc = new DOMDocument("1.0", "utf-8");
		$doc->loadXML($pdf2xml, LIBXML_PARSEHUGE);
		parent::__construct($doc);
	    } //end __construct()


	/**
	 * Get PDF canvas size
	 *
	 * @param int $page Page of pdf2xml to process
	 *
	 * @return array containing width and height of PDF canvas
	 *
	 * @untranslatable /pdf2xml/page[@number=
	 * @untranslatable width
	 * @untranslatable height
	 */

	public function getPageSize($page = 1)
	    {
		$list = $this->query("/pdf2xml/page[@number=" . $page . "]");
		if ($list->length === 1)
		    {
			return array(
				"width"  => $list->item(0)->getAttribute("width"),
				"height" => $list->item(0)->getAttribute("height"),
			       );
		    }
		else
		    {
			return false;
		    } //end if
	    } //end getPageSize()


	/**
	 * Find left coordinate of given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 *
	 * @untranslatable left
	 */

	public function leftOf($labels, $page = 1, $exact = false)
	    {
		$left = false;
		if (is_array($labels) === false)
		    {
			$labels = array($labels);
		    }

		foreach ($labels as $label)
		    {
			$list = $this->_getLabelList($label, $page, $exact);
			foreach ($list as $tag)
			    {
				if ($left !== false)
				    {
					if ($left > $tag->getAttribute("left"))
					    {
						$left = $tag->getAttribute("left");
					    }
				    }
				else
				    {
					$left = $tag->getAttribute("left");
				    }
			    }
		    } //end foreach

		return $left;
	    } //end leftOf()


	/**
	 * Find a coordinate to the left of given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 */

	public function toLeftOf($labels, $page = 1, $exact = false)
	    {
		$left = $this->leftOf($labels, $page, $exact);
		if ($left !== false && $left > 0)
		    {
			return ($left - 1);
		    }
		else
		    {
			return $left;
		    }
	    } //end toLeftOf()


	/**
	 * Find right coordinate of given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 *
	 * @untranslatable left
	 * @untranslatable width
	 */

	public function rightOf($labels, $page = 1, $exact = false)
	    {
		$right = false;
		if (is_array($labels) === false)
		    {
			$labels = array($labels);
		    }

		foreach ($labels as $label)
		    {
			$list = $this->_getLabelList($label, $page, $exact);
			foreach ($list as $tag)
			    {
				if ($right !== false)
				    {
					if ($right < ($tag->getAttribute("left") + $tag->getAttribute("width")))
					    {
						$right = ($tag->getAttribute("left") + $tag->getAttribute("width"));
					    }
				    }
				else
				    {
					$right = ($tag->getAttribute("left") + $tag->getAttribute("width"));
				    }
			    }
		    } //end foreach

		return $right;
	    } //end rightOf()


	/**
	 * Find a coordinate to the right of given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 */

	public function toRightOf($labels, $page = 1, $exact = false)
	    {
		$right = $this->rightOf($labels, $page, $exact);
		if ($right !== false)
		    {
			return ($right + 1);
		    }
		else
		    {
			return $right;
		    }
	    } //end toRightOf()


	/**
	 * Find top coordinate of given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 *
	 * @untranslatable top
	 */

	public function topOf($labels, $page = 1, $exact = false)
	    {
		$top = false;
		if (is_array($labels) === false)
		    {
			$labels = array($labels);
		    }

		foreach ($labels as $label)
		    {
			$list = $this->_getLabelList($label, $page, $exact);
			foreach ($list as $tag)
			    {
				if ($top !== false)
				    {
					if ($top > $tag->getAttribute("top"))
					    {
						$top = $tag->getAttribute("top");
					    }
				    }
				else
				    {
					$top = $tag->getAttribute("top");
				    }
			    }
		    } //end foreach

		return $top;
	    } //end topOf()


	/**
	 * Find a coordinate above given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 */

	public function above($labels, $page = 1, $exact = false)
	    {
		$top = $this->topOf($labels, $page, $exact);
		if ($top !== false && $top > 0)
		    {
			return ($top - 1);
		    }
		else
		    {
			return $top;
		    }
	    } //end above()


	/**
	 * Find bottom coordinate of given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 *
	 * @untranslatable top
	 * @untranslatable height
	 */

	public function bottomOf($labels, $page = 1, $exact = false)
	    {
		$bottom = false;
		if (is_array($labels) === false)
		    {
			$labels = array($labels);
		    }

		foreach ($labels as $label)
		    {
			$list = $this->_getLabelList($label, $page, $exact);
			foreach ($list as $tag)
			    {
				if ($bottom !== false)
				    {
					if ($bottom < ($tag->getAttribute("top") + $tag->getAttribute("height")))
					    {
						$bottom = ($tag->getAttribute("top") + $tag->getAttribute("height"));
					    }
				    }
				else
				    {
					$bottom = ($tag->getAttribute("top") + $tag->getAttribute("height"));
				    }
			    }
		    } //end foreach

		return $bottom;
	    } //end bottomOf()


	/**
	 * Find a coordinate below given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 * @param bool   $exact  Exact match of labels required
	 *
	 * @return string or false
	 */

	public function below($labels, $page = 1, $exact = false)
	    {
		$bottom = $this->bottomOf($labels, $page, $exact);
		if ($bottom !== false)
		    {
			return ($bottom + 1);
		    }
		else
		    {
			return $bottom;
		    }
	    } //end below()


	/**
	 * Find label on the page. We may look either for for complete label or only for beginning.
	 * Also we will try all combinations of normal/bold/italic text.
	 *
	 * @param string $label Label too look for
	 * @param int    $page  Page of pdf2xml to process
	 * @param bool   $exact Exact match of label required
	 *
	 * @return DOMNodeList
	 *
	 * @untranslatable /pdf2xml/page[@number=
	 * @untranslatable ]/text[normalize-space(text())='
	 * @untranslatable ]/text/b[normalize-space(text())='
	 * @untranslatable ]/text/i/b[normalize-space(text())='
	 * @untranslatable ]/text/i[normalize-space(text())='
	 * @untranslatable ]/text[starts-with(normalize-space(text()),'
	 * @untranslatable ]/text/b[starts-with(normalize-space(text()),'
	 * @untranslatable ]/text/i/b[starts-with(normalize-space(text()),'
	 * @untranslatable ]/text/i[starts-with(normalize-space(text()),'
	 */

	private function _getLabelList($label, $page, $exact)
	    {
		if ($exact === true)
		    {
			$list = $this->query(
			    "/pdf2xml/page[@number=" . $page . "]/text[normalize-space(text())='" . $label . "'] | " .
			    "/pdf2xml/page[@number=" . $page . "]/text/b[normalize-space(text())='" . $label . "']/.. | " .
			    "/pdf2xml/page[@number=" . $page . "]/text/i/b[normalize-space(text())='" . $label . "']/../.. | " .
			    "/pdf2xml/page[@number=" . $page . "]/text/i[normalize-space(text())='" . $label . "']/.."
			);
		    }
		else
		    {
			$list = $this->query(
			    "/pdf2xml/page[@number=" . $page . "]/text[starts-with(normalize-space(text()),'" . $label . "')] | " .
			    "/pdf2xml/page[@number=" . $page . "]/text/b[starts-with(normalize-space(text()),'" . $label . "')]/.. | " .
			    "/pdf2xml/page[@number=" . $page . "]/text/i/b[starts-with(normalize-space(text()),'" . $label . "')]/../.. | " .
			    "/pdf2xml/page[@number=" . $page . "]/text/i[starts-with(normalize-space(text()),'" . $label . "')]/.."
			);
		    }

		return $list;
	    } //end _getLabelList()


    } //end class

?>
