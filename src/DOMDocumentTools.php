<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMElement;
use \DOMNode;
use \Exception;

/**
 * Trait for function, that extend current DOMDocument functionality.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/DOMDocumentTools.php $
 */

trait DOMDocumentTools
    {

	use XMLmapping;

	/**
	 * Perform XPath query on schema until any element is found with any of provided queries.
	 *
	 * @param array $queriesarray Array of queries
	 *
	 * @return DOMNodeList
	 */

	protected function queryMultiple(array $queriesarray)
	    {
		$result = null;
		foreach ($queriesarray as $query)
		    {
			$subresult = $this->schemaDocumentXPath->query($query);
			if ($subresult->length > 0)
			    {
				$result = $subresult;
				break;
			    }
		    } //end foreach

		return $result;
	    } //end queryMultiple()


	/**
	 * Returns only first-level DOMElement children.
	 *
	 * @param DOMNode $element DOM node
	 *
	 * @return array
	 */

	protected function getFirstLevelChildNodes(DOMNode $element)
	    {
		if ($element->childNodes->length === 0)
		    {
			return array();
		    }
		else
		    {
			$childrenarray  = array();
			$currentelement = $element->firstChild;
			while ($currentelement !== null)
			    {
				if ($currentelement instanceof DOMElement === true)
				    {
					$childrenarray[] = $currentelement;
				    } //end if

				$currentelement = $currentelement->nextSibling;
			    } //end while

			return $childrenarray;
		    } //end if
	    } //end getFirstLevelChildNodes()


	/**
	 * Returns only first-level children of any type.
	 *
	 * @param DOMNode $element DOM node
	 *
	 * @return array
	 */

	protected function getFirstLevelChildNodesAnyType(DOMNode $element)
	    {
		if ($element->childNodes->length === 0)
		    {
			return array();
		    }
		else
		    {
			$childrenarray  = array();
			$currentelement = $element->firstChild;
			while ($currentelement !== null)
			    {
				$childrenarray[] = $currentelement;
				$currentelement  = $currentelement->nextSibling;
			    } //end while

			return $childrenarray;
		    } //end if
	    } //end getFirstLevelChildNodesAnyType()


	/**
	 * Extracts and sets namespace of schema.
	 *
	 * @param DOMXPathExtended $xpath Xpath of the document whose prefix has to be extracted
	 *
	 * @return string
	 *
	 * @untranslatable namespace::*
	 */

	protected function getSchemaPrefix(DOMXPathExtended $xpath)
	    {
		$prefix = "";
		foreach ($xpath->query("namespace::*") as $node)
		    {
			if ($node->nodeValue === "http://www.w3.org/2001/XMLSchema")
			    {
				$prefix = str_replace("xmlns:", "", $node->nodeName) . ":";
			    } //end if
		    } //end foreach

		return $prefix;
	    } //end getSchemaPrefix()


	/**
	 * Validate provided schema.
	 *
	 * @param string $schemafilepath File path to the schema
	 *
	 * @return void
	 *
	 * @throws Exception Invalid schema
	 *
	 * @exceptioncode EXCEPTION_INVALID_SCHEMA
	 */

	protected function validateSchema($schemafilepath)
	    {
		$domdocument = new SerializableDOMDocument();
		$rootelement = $domdocument->createElement("rootelement");
		$domdocument->appendChild($rootelement);

		$this->_registerSchemaMappings();

		$this->prepareForXMLerrors();
		set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext)
		    {
			unset($errno);
			unset($errfile);
			unset($errline);
			unset($errcontext);
			if (preg_match("/DOMDocument::schemaValidate\(\): Invalid Schema/", $errstr) > 0)
			    {
				$exceptiontext = _("Invalid schema was provided");
				if ($this->getXMLerrors() !== false)
				    {
					$exceptiontext .= _(", encountered errors:") . " " . $this->getXMLerrors();
				    }

				$this->clearXMLerrors();
				restore_error_handler();
				throw new Exception($exceptiontext, EXCEPTION_INVALID_SCHEMA);
			    }
		    }, E_WARNING);

		$domdocument->schemaValidate($schemafilepath);
		restore_error_handler();

		$this->clearXMLerrors();
	    } //end validateSchema()


    } //end trait

?>
