<?php

/**
 * DOMrunner relies on XMLerrors trait
 *
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;
use \DOMNode;
use \Exception;

/**
 * Class for iteration through DOM
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/DOMrunner.php $
 */

class DOMrunner extends DOMDocument
    {
	use XMLerrors;

	/**
	 * Current path
	 *
	 * @var string
	 */
	private $_path;

	/**
	 * Construct DOM from XML document
	 *
	 * @param string $document XML document to iterate through
	 *
	 * @return void
	 *
	 * @throws Exception Source document is not valid XML
	 *
	 * @exceptioncode EXCEPTION_XML_PARSING_ERROR
	 */

	public function __construct($document)
	    {
		parent::__construct("1.0");

		$this->prepareForXMLerrors();
		$this->loadXML($document, LIBXML_PARSEHUGE);
		$error = $this->getXMLerrors();
		$this->clearXMLerrors();
		if ($error !== false)
		    {
			throw new Exception($error, EXCEPTION_XML_PARSING_ERROR);
		    }

		$this->_path = array(0);
	    } //end __construct()


	/**
	 * Move onto next DOM leaf
	 *
	 * @return mixed path of current DOM leaf, false if no leafs left
	 *
	 * @untranslatable #text
	 */

	public function next()
	    {
		do
		    {
			if ($this->_advance() === true)
			    {
				$path = "";
				foreach ($this->_path as $depth => $item)
				    {
					if ($depth === 0)
					    {
						$node = $this;
					    }
					else
					    {
						$node  = $node->childNodes->item($item);
						$path .= "/" . $node->nodeName;
					    }
				    }
			    }
			else
			    {
				$path = false;
				break;
			    }
		    } while ($node->nodeName === "#text");

		return $path;
	    } //end next()


	/**
	 * Advance onto next DOM leaf
	 *
	 * @return boolean true if successfully advanced to the next leaf, false if no leafs left
	 */

	private function _advance()
	    {
		foreach ($this->_path as $depth => $item)
		    {
			$node = (($depth === 0) ? $this : $node->childNodes->item($item));
			if (($depth + 1) >= count($this->_path))
			    {
				if (isset($node->childNodes) === true && $node->childNodes->length > 0)
				    {
					$this->_path[] = 0;
				    }
				else
				    {
					if ($node->parentNode->childNodes->length > ($this->_path[$depth] + 1))
					    {
						$this->_path[$depth]++;
					    }
					else
					    {
						list($node, $depth) = $this->_retract($node, $depth);
						if (count($this->_path) > 1)
						    {
							$this->_path[$depth]++;
						    }
					    }
				    } //end if
			    } //end if
		    } //end foreach

		return (count($this->_path) > 1);
	    } //end _advance()


	/**
	 * Retract to next DOM leaf. May retract several depths in one go
	 *
	 * @param DOMNode $node  Current node
	 * @param int     $depth Current node depth
	 *
	 * @return array containing next $node and its $depth
	 */

	private function _retract(DOMNode $node, $depth)
	    {
		$pathlength = count($this->_path);
		while ($pathlength > 1 && $node->parentNode->childNodes->length <= ($this->_path[$depth] + 1))
		    {
			array_pop($this->_path);
			$pathlength = count($this->_path);

			$node = $node->parentNode;
			$depth--;
		    }

		return array(
			$node,
			$depth,
		       );
	    } //end _retract()


	/**
	 * Returns current leaf in XML form
	 *
	 * @return string containing current leaf
	 */

	public function leaf()
	    {
		$node = $this;
		foreach ($this->_path as $depth => $item)
		    {
			$node = (($depth === 0) ? $node : $node->childNodes->item($item));
		    }

		return $this->saveXML($node);
	    } //end leaf()


	/**
	 * Returns value of current node
	 *
	 * @return string containing value of current node
	 */

	public function value()
	    {
		$node = $this;
		foreach ($this->_path as $depth => $item)
		    {
			$node = (($depth === 0) ? $node : $node->childNodes->item($item));
		    }

		return $node->textContent;
	    } //end value()


	/**
	 * Returns list of attributes and attributes values of current leaf
	 *
	 * @return array containing names of attributes as keys and attributes values as values
	 */

	public function attributes()
	    {
		$node = $this;
		foreach ($this->_path as $depth => $item)
		    {
			$node = (($depth === 0) ? $node : $node->childNodes->item($item));
		    }

		$attributes = array();
		if ($node->hasAttributes() === true)
		    {
			foreach ($node->attributes as $attribute)
			    {
				$attributes[$attribute->nodeName] = $attribute->nodeValue;
			    }

			ksort($attributes);
		    }

		return $attributes;
	    } //end attributes()


    } //end class

?>
