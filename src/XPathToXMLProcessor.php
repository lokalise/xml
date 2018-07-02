<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * XPathToXMLProcessor.
 * Transforms the set of XPath'es and correspondng values of leaf elements and values of attribites to XML document. It does
 * not check the validity of XML, it is assumed that set of XPath'es is valid. However, the SchemaNametraceDescriptor instance can be provided
 * for additional checks.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/XPathToXMLProcessor.php $
 */

class XPathToXMLProcessor
    {

	use SchemaNametrace;

	/**
	 * Schema nametrace descriptor
	 *
	 * @var SchemaNametraceDescription
         */
	private $_descriptor;

	/**
	 * Instanitaion of the XPathToXMLProcessor.
	 *
	 * @param mixed $nametracedescriptor Instance of the SchemaNametraceDescriptor
	 *
	 * @return void
	 *
	 * @untranslatable simple
	 */

	public function __construct($nametracedescriptor = false)
	    {
		$this->_descriptor = $nametracedescriptor;
		if ($nametracedescriptor !== false)
		    {
			$this->_descriptor->setDiscoveryMode("simple");
		    }
	    } //end __construct()


	/**
	 * Generated document based on input nametraces.
	 *
	 * @param array $dataarray Set of input items
	 *
	 * @return DOMDocument
	 *
	 * @untranslatable utf-8
	 */

	public function process(array $dataarray)
	    {
		$documentrepresentation = array();
		foreach ($dataarray as $datakey => $dataitem)
		    {
			$explodedfilteredkeyarray = array_filter(explode("/", $datakey));
			$this->_traceDown($documentrepresentation, $explodedfilteredkeyarray, $dataitem);
		    }

		$this->_document = new SerializableDOMDocument("1.0", "utf-8");
		$root            = $this->_getXMLNode(false, end($documentrepresentation));
		$this->_document->appendChild(end($root));

		return $this->_document;
	    } //end process()


	/**
	 * Returns XML node of the final document.
	 *
	 * @param string $nametrace            Current nametrace
	 * @param array  $documentlevelelement Document element description
	 *
	 * @return array
	 */

	private function _getXMLNode($nametrace, array $documentlevelelement)
	    {
		if (isset($documentlevelelement["parentfullnametrace"]) === true)
		    {
			$nametrace = $documentlevelelement["parentfullnametrace"] . "/" . $documentlevelelement["name"];
		    }
		else if ($nametrace === false)
		    {
			$nametrace = $documentlevelelement["name"];
		    }
		else
		    {
			$nametrace .= "/" . $documentlevelelement["name"];
		    }

		$elementname = preg_replace("/\[\d+\]$/", "", $documentlevelelement["name"]);

		if (isset($documentlevelelement["element"]["values"]) === true && count($documentlevelelement["children"]) === 0)
		    {
			$containednodes = $this->_constructLeafXMLNode($elementname, $documentlevelelement);
			return $containednodes;
		    }
		else
		    {
			$node = $this->_document->createElement($elementname);
			if (isset($documentlevelelement["element"]["attributes"]) === true)
			    {
				foreach ($documentlevelelement["element"]["attributes"] as $attribute)
				    {
					$node->setAttribute($attribute["name"], end($attribute["values"]));
				    }
			    }

			$sortedchildren = $this->_sortChildrenAccordingToSchemaDefinition($nametrace, $documentlevelelement["children"]);
			$sortedchildren = $this->_sortAccordingToIndexes($sortedchildren);
			foreach ($sortedchildren as $child)
			    {
				$underlyingnodes = $this->_getXMLNode($nametrace, $child);
				foreach ($underlyingnodes as $underlyingnode)
				    {
					$node->appendChild($underlyingnode);
				    }
			    }

			return array($node);
		    } //end if
	    } //end _getXMLNode()


	/**
	 * Sorting children of the element that is defined by nametrace.
	 *
	 * @param string $nametrace Current element nametrace
	 * @param array  $children  Provided children
	 *
	 * @return array Sorted children
	 */

	private function _sortChildrenAccordingToSchemaDefinition($nametrace, array $children)
	    {
		$sortedchildren = $children;
		if ($this->_descriptor !== false)
		    {
			$description = $this->_descriptor->describe($nametrace);

			$namesorder = $this->_fillRealChildrenNames($nametrace, $description["children"]);

			$newchildrenarray = array();
			foreach ($namesorder as $ordereditem)
			    {
				foreach ($children as $child)
				    {
					$childname = $this->_removeLastNametraceIndex($child["name"]);
					if ($childname === $ordereditem["name"])
					    {
						$child["parentfullnametrace"] = $ordereditem["parentfullnametrace"];
						$newchildrenarray[]           = $child;
					    }
				    }
			    }

			$sortedchildren = $newchildrenarray;
		    } //end if

		return $sortedchildren;
	    } //end _sortChildrenAccordingToSchemaDefinition()


	/**
	 * Sorting elements with same name, on the same level according to theirs
	 * indexes (at the same time keeping the initial order with other siblings with different names).
	 *
	 * @param array $sortedelements Set of sorted elements
	 *
	 * @return array
	 */

	private function _sortAccordingToIndexes(array $sortedelements)
	    {
		$orderedchildrenwithsimilarnames = array();
		foreach ($sortedelements as $child)
		    {
			if (substr_count($child["name"], "[") === 0)
			    {
				$orderedchildrenwithsimilarnames[$child["name"]] = $child;
			    }
			else
			    {
				$name  = $this->_deleteNametraceIndexes($child["name"]);
				$index = $this->_getLastNametraceIndex($child["name"]);
				if (isset($orderedchildrenwithsimilarnames[$name]) === false)
				    {
					$orderedchildrenwithsimilarnames[$name] = array();
				    }

				$orderedchildrenwithsimilarnames[$name][$index] = $child;
			    }
		    }

		$sortedelements = array();
		foreach ($orderedchildrenwithsimilarnames as $name => $element)
		    {
			if (isset($element["name"]) === true)
			    {
				$sortedelements[$name] = $element;
			    }
			else
			    {
				$sortedarray = $orderedchildrenwithsimilarnames[$name];
				ksort($sortedarray);
				foreach ($sortedarray as $sortedarrayitem)
				    {
					$sortedelements[$sortedarrayitem["name"]] = $sortedarrayitem;
				    }
			    }
		    }

		return $sortedelements;
	    } //end _sortAccordingToIndexes()


	/**
	 * Fills the list of all children except choices.
	 *
	 * @param string $nametrace Current nametrace
	 * @param array  $children  List of children
	 *
	 * @return array
	 */

	private function _fillRealChildrenNames($nametrace, array $children)
	    {
		$childrennamesarray = array();
		foreach ($children as $child)
		    {
			if ($this->_isChoiceNametraceItem($child["name"]) === true)
			    {
				$choicenametrace   = $nametrace . "/" . $child["name"];
				$choicedescription = $this->_descriptor->describe($choicenametrace);
				$numberofbranches  = count($choicedescription["children"]["branches"]);
				for ($i = 0; $i < $numberofbranches; $i++)
				    {
					$childrennamesarray = array_merge($childrennamesarray,
					$this->_fillRealChildrenNames($choicenametrace, $choicedescription["children"]["branches"][$i]));
				    }
			    }
			else
			    {
				$childrennamesarray[] = array(
							 "name"                => $child["name"],
							 "parentfullnametrace" => $nametrace,
							);
			    }
		    }

		return $childrennamesarray;
	    } //end _fillRealChildrenNames()


	/**
	 * Constructs XML node for leaf element.
	 *
	 * @param string $elementname          Name of the element
	 * @param array  $documentlevelelement Element description
	 *
	 * @return array Of DOMElements
	 */

	private function _constructLeafXMLNode($elementname, array $documentlevelelement)
	    {
		$containednodes = array();
		foreach ($documentlevelelement["element"]["values"] as $key => $value)
		    {
			$node      = $this->_document->createElement($elementname);
			$valuenode = $this->_document->createTextNode($value);
			$node->appendChild($valuenode);

			if (isset($documentlevelelement["element"]["attributes"]) === true)
			    {
				foreach ($documentlevelelement["element"]["attributes"] as $attribute)
				    {
					$node->setAttribute($attribute["name"], $attribute["values"][$key]);
				    }
			    }

			$containednodes[] = $node;
		    }

		return $containednodes;
	    } //end _constructLeafXMLNode()


	/**
	 * Tracing down the specified nametrace (that is splitted into pieces) and filling document representation.
	 *
	 * @param array $processedarray Reference to the processed element description that is the located in the overall array
	 * @param array $nametraceitems Items of the nametrace that left to process
	 * @param array $elementdata    Description of the element, its name and fullnametrace
	 *
	 * @return void
	 */

	private function _traceDown(array &$processedarray, array $nametraceitems, array $elementdata)
	    {
		$currentitem = array_shift($nametraceitems);

		$founditem = false;
		foreach (array_keys($processedarray) as $processedkey)
		    {
			if ($processedkey === $currentitem)
			    {
				$founditem = true;
				if (count($nametraceitems) === 0)
				    {
					$processedarray[$processedkey]["element"] = array();
					if (isset($elementdata["attributes"]) === true)
					    {
						$processedarray[$processedkey]["element"]["attributes"] = $elementdata["attributes"];
					    }
				    }
				else
				    {
					$this->_traceDown($processedarray[$processedkey]["children"], $nametraceitems, $elementdata);
				    }
			    }
		    }

		if ($founditem === false)
		    {
			if (count($nametraceitems) === 0)
			    {
				$processedarray[$currentitem] = array(
								 "name"     => $currentitem,
								 "element"  => $elementdata,
								 "children" => array()
								);
			    }
			else
			    {
				$processedarray[$currentitem] = array(
								 "name"     => $currentitem,
								 "element"  => false,
								 "children" => array()
								);

				$this->_traceDown($processedarray[$currentitem]["children"], $nametraceitems, $elementdata);
			    }
		    }
	    } //end _traceDown()


    } //end class

?>
