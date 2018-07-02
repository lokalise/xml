<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMElement;
use \DOMText;
use \Logics\Foundation\SQL\SQLdatabase;

/**
 * SchemaFlatten class for flattening routine
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaFlatten.php $
 */

class SchemaFlatten
    {

	use XMLfailure;

	use XMLerrors;

	use SchemaNametrace;

	use DOMDocumentTools;

	/**
	 * Flattened document array
	 *
	 * @var array
	 */
	private $_flattenedDocumentArray;

	/**
	 * Schema flatten element array filter
	 *
	 * @var SchemaFlattenElementArrayFilter
	 */
	private $_elementsArrayFilter;

	/**
	 * Schema file path
	 *
	 * @var string
	 */
	private $_schemafilepath;

	/**
	 * Database connection
	 *
	 * @var SQLdatabase
	 */
	private $_db;

	/**
	 * Instantiate this class
	 *
	 * @param string      $schemafilepath Filepath to the schema file
	 * @param SQLdatabase $db             Database connection
	 *
	 * @return void
	 */

	public function __construct($schemafilepath, SQLdatabase $db)
	    {
		$this->_schemafilepath = $schemafilepath;
		$this->_db             = $db;

		$this->_elementsArrayFilter = new SchemaFlattenElementsArrayFilter($schemafilepath);
	    } //end __construct()


	/**
	 * Returns the array, containing flattened XML document
	 *
	 * @param DOMElement $rootelement        Root node of the document which is being flattened
	 * @param bool       $donotvalidateinput Specifies if the validation should be perfromed for the input document
	 *
	 * @return array
	 *
	 * @untranslatable utf-8
	 */

	public function getFlattenedElementsArray(DOMElement $rootelement, $donotvalidateinput = false)
	    {
		$this->_flattenedDocumentArray = array();
		$this->_appendFlattenedChildrenToArray($rootelement);

		$this->_flattenedDocumentArray = $this->_elementsArrayFilter->filter($this->_flattenedDocumentArray);

		$testvaliditydocument     = new SerializableDOMDocument("1.0", "utf-8");
		$testvaliditydocumentroot = $testvaliditydocument->importNode($rootelement, true);
		$testvaliditydocument->appendChild($testvaliditydocumentroot);

		$this->prepareForXMLerrors();
		if ($testvaliditydocument->schemaValidate($this->_schemafilepath) === true || $donotvalidateinput === true)
		    {
			$this->clearXMLerrors();
			return $this->_flattenedDocumentArray;
		    }
		else
		    {
			$this->logFailure(_("Validation failure:") . " " . $this->getXMLerrors(), $testvaliditydocument->saveXML());
		    } //end if
	    } //end getFlattenedElementsArray()


	/**
	 * Appends value and attributes data to the node record in _flattenedDocumentArray
	 *
	 * @param DOMElement $element   Current element of the parsed document
	 * @param string     $nametrace Nametrace of the child
	 *
	 * @return bool
	 */

	private function _appendNodeValueAndAttributes(DOMElement $element, $nametrace)
	    {
		$valueappended = false;
		foreach ($this->_flattenedDocumentArray[$element->nodeName] as $key => $subelement)
		    {
			if ($nametrace === $subelement["nametrace"])
			    {
				$this->_flattenedDocumentArray[$element->nodeName][$key]["values"][] = $element->nodeValue;
				$valueappended = true;

				$attributes = $this->_getAttributesArray($element);
				foreach ($attributes as $attributename => $attributevalue)
				    {
					$this->_flattenedDocumentArray[$element->nodeName][$key]["attributes"][$attributename][] = array_pop($attributevalue);
				    } //end foreach
			    } //end if
		    } //end foreach

		return $valueappended;
	    } //end _appendNodeValueAndAttributes()


	/**
	 * Looking for the unique name to populate the internal array
	 *
	 * @param DOMElement $element Current element of the parsed document
	 *
	 * @return void
	 */

	private function _lookForUniqueName(DOMElement $element)
	    {
		$uniquenamesnotfound = true;

		$i = 1;
		while ($uniquenamesnotfound)
		    {
			$currentnames = array();
			foreach (array_keys($this->_flattenedDocumentArray[$element->nodeName]) as $key)
			    {
				$newname = $this->_getLastNametraceItems($this->_flattenedDocumentArray[$element->nodeName][$key]["nametrace"], $i);
				$this->_flattenedDocumentArray[$element->nodeName][$key]["name"] = $newname;
				$currentnames[] = $newname;
			    }

			$totalelementsinarray       = count($currentnames);
			$totaluniqueelementsinarray = count(array_unique($currentnames));

			if ($totaluniqueelementsinarray === $totalelementsinarray)
			    {
				$uniquenamesnotfound = false;
			    }
			else
			    {
				$i++;
			    }
		    } //end while
	    } //end _lookForUniqueName()


	/**
	 * Appending flattened elements to the temporary array
	 *
	 * @param DOMElement $element         Current element of the parsed document
	 * @param string     $parentnametrace Nametrace of the parent
	 * @param int        $elementindex    Index of the parent element
	 *
	 * @return void
	 */

	private function _appendFlattenedChildrenToArray(DOMElement $element, $parentnametrace = "", $elementindex = 1)
	    {
		if ($element instanceof DOMElement)
		    {
			$nodename  = $element->nodeName;
			$nametrace = $parentnametrace . "/" . $nodename;

			if (array_key_exists($element->nodeName, $this->_flattenedDocumentArray) === true)
			    {
				$valueappended = $this->_appendNodeValueAndAttributes($element, $nametrace);
				$attributes    = $this->_getAttributesArray($element);
				if ($valueappended === false)
				    {
					$nodevalue      = array($element->nodeValue);
					$children       = $element->childNodes;
					$firstchildnode = $children->item(0);

					if (($children->length > 1) && ($firstchildnode instanceof DOMElement))
					    {
						$nodevalue = array();
					    }

					$elementdata = array(
							"name"       => $element->nodeName,
							"values"     => $nodevalue,
							"nametrace"  => $nametrace,
							"attributes" => $attributes,
						       );

					$children = $this->getFirstLevelChildNodes($element);
					if (count($children) !== 0)
					    {
						unset($elementdata["values"]);
					    }

					$this->_flattenedDocumentArray[$element->nodeName][] = $elementdata;
					$this->_lookForUniqueName($element);
				    } //end if
			    }
			else
			    {
				$this->_appendNewFlattenedChildToArray($element, $parentnametrace);
			    } //end if

			$this->_processChildren($element, $nametrace, $elementindex);
		    } //end if
	    } //end _appendFlattenedChildrenToArray()


	/**
	 * Append new element to the flattened children array.
	 *
	 * @param DOMElement $element         Processed element
	 * @param string     $parentnametrace Nametrace of the parent element
	 *
	 * @return void
	 */

	private function _appendNewFlattenedChildToArray(DOMElement $element, $parentnametrace)
	    {
		$childnodes = $element->childNodes;
		$nametrace  = $parentnametrace . "/" . $element->nodeName;

		$attributes = $this->_getAttributesArray($element);
		if (($childnodes->length === 1) && (($childnodes->item(0) instanceof DOMText) === true))
		    {
			$nodevalue   = array();
			$textnode    = $childnodes->item(0);
			$nodevalue[] = $textnode->nodeValue;

			$this->_flattenedDocumentArray[$element->nodeName][] = array(
										"name"       => $element->nodeName,
										"values"     => $nodevalue,
										"attributes" => $attributes,
										"nametrace"  => $nametrace,
									       );
		    }
		else if (empty($attributes) === false)
		    {
			$this->_flattenedDocumentArray[$element->nodeName][] = array(
										"name"       => $element->nodeName,
										"attributes" => $attributes,
										"nametrace"  => $nametrace,
									       );
		    } //end if
	    } //end _appendNewFlattenedChildToArray()


	/**
	 * Processing children for the expanded document node.
	 *
	 * @param DOMElement $element      Current element of the parsed document
	 * @param string     $nametrace    Nametrace of the parent
	 * @param int        $elementindex Index of the parent element
	 *
	 * @return void
	 */

	private function _processChildren(DOMElement $element, $nametrace, $elementindex)
	    {
		$totaloccurence     = 0;
		$parentnodechildren = $this->getFirstLevelChildNodes($element->parentNode);
		foreach ($parentnodechildren as $parentchild)
		    {
			if ($parentchild->nodeName === $element->nodeName)
			    {
				$totaloccurence++;
			    } //end if
		    } //end foreach

		$hierarchyelement = true;
		if (($element->childNodes->length === 1) && (($element->childNodes->item(0) instanceof DOMText) === true))
		    {
			$hierarchyelement = false;
		    }

		$occurencearray       = array();
		$firstlevelchildnodes = $this->getFirstLevelChildNodes($element);
		foreach ($firstlevelchildnodes as $childnode)
		    {
			$childname = $childnode->nodeName;
			if (isset($occurencearray[$childname]) === true)
			    {
				$occurencearray[$childname]++;
			    }
			else
			    {
				$occurencearray[$childname] = 1;
			    } //end if

			$currentnametrace = $nametrace;
			if ($totaloccurence > 1 && $hierarchyelement === true)
			    {
				$currentnametrace .= "[" . $elementindex . "]";
			    } //end if

			$this->_appendFlattenedChildrenToArray($childnode, $currentnametrace, $occurencearray[$childname]);
		    } //end foreach
	    } //end _processChildren()


	/**
	 * Extracts attributes from the given element
	 *
	 * @param DOMElement $element Given element
	 *
	 * @return array
	 */

	private function _getAttributesArray(DOMElement $element)
	    {
		$attributesarray = array();
		if ($element->hasAttributes() === true)
		    {
			$attributes      = $element->attributes;
			$totalattributes = $attributes->length;
			for ($i = 0; $i < $totalattributes; $i++)
			    {
				$attr = $attributes->item($i);

				$attributesarray[$attr->nodeName][] = $attr->nodeValue;
			    }
		    } //end if

		return $attributesarray;
	    } //end _getAttributesArray()


    } //end class

?>
