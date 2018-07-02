<?php

/**
 * DOMXPathPDF relies on DOMXPathPDFblocks class
 *
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMElement;
use \DOMNodeList;

/**
 * DOMXPathPDF allows complex operations on text contained within PDF files
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/DOMXPathPDF.php $
 */

class DOMXPathPDF extends DOMXPathPDFblocks
    {

	/**
	 * Sort lines and strings from pdf2xml limited by bounding box
	 *
	 * @param int  $left         Left boundary
	 * @param int  $right        Right boundary
	 * @param int  $top          Top boundary
	 * @param int  $bottom       Bottom boundary
	 * @param int  $page         Page of pdf2xml to process
	 * @param bool $verticalsort True if fields must be sorted vertically
	 *
	 * @return array
	 *
	 * @untranslatable top
	 * @untranslatable left
	 */

	public function getLines($left, $right, $top, $bottom, $page = 1, $verticalsort = false)
	    {
		$list = $this->_getElements($left, $right, $top, $bottom, $page);

		$elements = array();
		foreach ($list as $element)
		    {
			if (trim($element->nodeValue) !== "")
			    {
				if ($verticalsort === false)
				    {
					$elements[$element->getAttribute("top")][$element->getAttribute("left")] = trim($element->nodeValue);
				    }
				else
				    {
					$elements[$element->getAttribute("left")][$element->getAttribute("top")] = trim($element->nodeValue);
				    }
			    }
		    }

		ksort($elements);
		$lines = array();
		foreach ($elements as $row)
		    {
			ksort($row);
			$lines[] = array_values($row);
		    }

		return $lines;
	    } //end getLines()


	/**
	 * Get multiline text from pdf2xml limited by bounding box
	 *
	 * @param int $left   Left boundary
	 * @param int $right  Right boundary
	 * @param int $top    Top boundary
	 * @param int $bottom Bottom boundary
	 * @param int $page   Page of pdf2xml to process
	 *
	 * @return string
	 */

	public function getText($left, $right, $top, $bottom, $page = 1)
	    {
		$text = "";

		$lines = $this->getLines($left, $right, $top, $bottom, $page);
		foreach ($lines as $line)
		    {
			foreach ($line as $s)
			    {
				$text .= trim($s) . " ";
			    }
		    }

		return trim($text);
	    } //end getText()


	/**
	 * Get table from pdf2xml limited by bounding box
	 *
	 * @param int $left   Left boundary
	 * @param int $right  Right boundary
	 * @param int $top    Top boundary
	 * @param int $bottom Bottom boundary
	 * @param int $page   Page of pdf2xml to process
	 *
	 * @return array or false
	 *
	 * @untranslatable top
	 * @untranslatable left
	 */

	public function getTable($left, $right, $top, $bottom, $page = 1)
	    {
		$list = $this->_getElements($left, $right, $top, $bottom, $page);

		$columns = $this->_calculateColumns($list);
		if ($columns !== false)
		    {
			$elements = array();
			foreach ($list as $element)
			    {
				if (trim($element->nodeValue) !== "")
				    {
					$elements[$element->getAttribute("top")][$element->getAttribute("left")] = trim($element->nodeValue);
				    }
			    }

			ksort($elements);
			$lines = array();
			foreach ($elements as $row)
			    {
				ksort($row);
				$line = array();
				foreach ($columns as $column)
				    {
					$value = "";
					foreach ($row as $pos => $element)
					    {
						if ($this->_between($column["left"], $pos, $column["right"]) === true)
						    {
							$value = $element;
						    }
					    }

					$line[] = $value;
				    }

				$lines[] = $line;
			    }

			return $lines;
		    }
		else
		    {
			return false;
		    } //end if
	    } //end getTable()


	/**
	 * Get list of elements within boundaries on particular page
	 *
	 * @param int $left   Left boundary
	 * @param int $right  Right boundary
	 * @param int $top    Top boundary
	 * @param int $bottom Bottom boundary
	 * @param int $page   Page of pdf2xml document
	 *
	 * @return DOMNodeList containing all elements matching criteria
	 *
	 * @untranslatable true()
	 * @untranslatable and @left+@width>=
	 * @untranslatable and @left<=
	 * @untranslatable and @top+@height>=
	 * @untranslatable and @top<=
	 * @untranslatable /pdf2xml/page[@number=
	 * @untranslatable ]/text[
	 */

	private function _getElements($left, $right, $top, $bottom, $page)
	    {
		if (is_numeric($page) === true)
		    {
			$condition = "true()";

			if ((is_numeric($left) === true))
			    {
				$condition .= " and @left+@width>=" . $left;
			    }

			if ((is_numeric($right) === true))
			    {
				$condition .= " and @left<=" . $right;
			    }

			if ((is_numeric($top) === true))
			    {
				$condition .= " and @top+@height>=" . $top;
			    }

			if ((is_numeric($bottom) === true))
			    {
				$condition .= " and @top<=" . $bottom;
			    }

			$list = $this->query("/pdf2xml/page[@number=" . $page . "]/text[" . $condition . "]");
		    }
		else
		    {
			$list = new DOMNodeList();
		    } //end if

		return $list;
	    } //end _getElements()


	/**
	 * Calculate columns of table from list of elements
	 *
	 * @param DOMNodeList $list List of elements
	 *
	 * @return array or false
	 */

	private function _calculateColumns(DOMNodeList $list)
	    {
		$columns = array();
		foreach ($list as $element)
		    {
			$columns = $this->_accommodateElement($element, $columns);
			if ($columns === false)
			    {
				break;
			    }
		    } //end foreach

		return $columns;
	    } //end _calculateColumns()


	/**
	 * Try to accommodate the element within columns
	 *
	 * @param DOMElement $element Element to accommodate
	 * @param array      $columns Array of columns
	 *
	 * @return array or false
	 *
	 * @untranslatable left
	 * @untranslatable width
	 */

	private function _accommodateElement(DOMElement $element, array $columns)
	    {
		$current  = array(
			     "left"  => $element->getAttribute("left"),
			     "right" => ($element->getAttribute("left") + $element->getAttribute("width")),
			    );
		$consumed = false;
		$count    = count($columns);
		for ($i = 0; $i < $count; $i++)
		    {
			if ($consumed === false && $current["right"] < $columns[$i]["left"])
			    {
				array_splice($columns, $i, 0, array($current));
				$consumed = true;
				break;
			    }

			if ($this->_overlaps($columns[$i], $current) === true)
			    {
				$columns[$i] = $this->_join($columns[$i], $current);
				$consumed    = true;
			    }

			if ($i > 0 && $this->_overlaps($columns[($i - 1)], $columns[$i]) === true)
			    {
				$columns  = false;
				$consumed = true;
				break;
			    }
		    } //end for

		if ($consumed === false)
		    {
			$columns[] = $current;
		    }

		return $columns;
	    } //end _accommodateElement()


	/**
	 * Check if two ranges overlap
	 *
	 * @param array $a First range
	 * @param array $b Second range
	 *
	 * @return boolean true if input ranges overlap
	 */

	private function _overlaps(array $a, array $b)
	    {
		return (
			$this->_between($a["left"], $b["left"], $a["right"]) || $this->_between($a["left"], $b["right"], $a["right"]) ||
			$this->_between($b["left"], $a["left"], $b["right"]) || $this->_between($b["left"], $a["right"], $b["right"])
		       );
	    } //end _overlaps()


	/**
	 * Check if value is between other two values
	 *
	 * @param int $a Left value
	 * @param int $b Value to check
	 * @param int $c Right value
	 *
	 * @return boolean true if value is in between
	 */

	private function _between($a, $b, $c)
	    {
		return $a <= $b && $b <= $c;
	    } //end _between()


	/**
	 * Combine two ranges into one
	 *
	 * @param array $a First range
	 * @param array $b Second range
	 *
	 * @return array containing range which includes both input ranges
	 */

	private function _join(array $a, array $b)
	    {
		return array(
			"left"  => min($a["left"], $b["left"]),
			"right" => max($a["right"], $b["right"]),
		       );
	    } //end _join()


	/**
	 * Get field to the right of given label(s)
	 *
	 * @param string $labels       Label or array() of labels
	 * @param int    $page         Page of pdf2xml to process
	 * @param int    $skip         Number of fields to skip after label
	 * @param bool   $exact        Exact match of labels required
	 * @param bool   $verticalsort True if fields must be sorted vertically
	 *
	 * @return string or false
	 */

	public function getFieldToRightOf($labels, $page = 1, $skip = 0, $exact = false, $verticalsort = true)
	    {
		$top    = $this->topOf($labels, $page, $exact);
		$bottom = $this->bottomOf($labels, $page, $exact);
		$left   = $this->toRightOf($labels, $page, $exact);
		$lines  = $this->getLines($left, false, $top, $bottom, $page, $verticalsort);

		$fields = array();
		foreach ($lines as $line)
		    {
			$fields = array_merge($fields, array_values($line));
		    }

		return ((isset($fields[(0 + $skip)]) === true) ? $fields[(0 + $skip)] : false);
	    } //end getFieldToRightOf()


	/**
	 * Get field above given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 *
	 * @return string or false
	 */

	public function getFieldAbove($labels, $page = 1)
	    {
		$bottom = $this->above($labels);
		$left   = $this->leftOf($labels);
		$lines  = $this->getLines($left, false, false, $bottom, $page);
		return ((count($lines) > 0) ? $lines[(count($lines) - 1)][0] : false);
	    } //end getFieldAbove()


	/**
	 * Get field below given label(s)
	 *
	 * @param string $labels Label or array() of labels
	 * @param int    $page   Page of pdf2xml to process
	 *
	 * @return string or false
	 */

	public function getFieldBelow($labels, $page = 1)
	    {
		$top   = $this->below($labels);
		$left  = $this->leftOf($labels);
		$lines = $this->getLines($left, false, $top, false, $page);
		return ((count($lines) > 0) ? $lines[0][0] : false);
	    } //end getFieldBelow()


	/**
	 * Get next field if it is not on the same line as label
	 *
	 * @param string $label Label
	 * @param int    $page  Page of pdf2xml to process
	 *
	 * @return string or false
	 *
	 * @untranslatable /pdf2xml/page[@number=
	 * @untranslatable ]/text[starts-with(normalize-space(text()),'
	 * @untranslatable /following-sibling::text[1][@top!=
	 */

	public function getNextField($label, $page = 1)
	    {
		$list = $this->query(
		    "/pdf2xml/page[@number=" . $page . "]/text[starts-with(normalize-space(text()),'" . $label . "')]" .
		    "/following-sibling::text[1][@top!=" . $this->topOf($label) . "]"
		);
		return $this->getFirstItemValueFrom($list);
	    } //end getNextField()


	/**
	 * Get field which starts with given label
	 *
	 * @param string $label Label to look for
	 * @param int    $page  Page number of pdf2xml to process
	 *
	 * @return string or false
	 *
	 * @untranslatable /pdf2xml/page[@number=
	 * @untranslatable ]/text[starts-with(normalize-space(text()),'
	 * @untranslatable ]/text/b[starts-with(normalize-space(text()),'
	 * @untranslatable ]/text/i/b[starts-with(normalize-space(text()),'
	 * @untranslatable ]/text/i[starts-with(normalize-space(text()),'
	 */

	public function findStartsWith($label, $page = 1)
	    {
		$list = $this->query(
		    "/pdf2xml/page[@number=" . $page . "]/text[starts-with(normalize-space(text()),'" . $label . "')] | " .
		    "/pdf2xml/page[@number=" . $page . "]/text/b[starts-with(normalize-space(text()),'" . $label . "')]/.. | " .
		    "/pdf2xml/page[@number=" . $page . "]/text/i/b[starts-with(normalize-space(text()),'" . $label . "')]/../.. | " .
		    "/pdf2xml/page[@number=" . $page . "]/text/i[starts-with(normalize-space(text()),'" . $label . "')]/.."
		);
		return $this->getFirstItemValueFrom($list);
	    } //end findStartsWith()


    } //end class

?>
