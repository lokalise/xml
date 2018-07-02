<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 *
 * Nesting level should be limited
 *
 * @untranslatable xdebug.max_nesting_level
 */

namespace Logics\Foundation\XML;

ini_set("xdebug.max_nesting_level", 200);

use \DOMElement;

/**
 * SchemaProcessorIterator class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/schemaprocessor/SchemaProcessorIterator.php $
 */

class SchemaProcessorIterator
    {

	use SchemaNametrace;

	use DOMDocumentTools;

	/**
	 * Place to keep compiled schema
	 *
	 * @var array
	 */
	protected $schemaKeeper;

	/**
	 * Schema docuement XPath object
	 *
	 * @var DOMXPathExtended
	 */
	protected $schemaDocumentXPath;

	/**
	 * Schema prefix
	 *
	 * @var string
	 */
	protected $schemaPrefix;

	/**
	 * Base types
	 *
	 * @var array
	 */
	protected $baseTypes;

	/**
	 * Invokes current instance from session
	 *
	 * @return SchemaProcessorIterator
	 */

	public function __wakeup()
	    {
		$this->schemaDocumentXPath = new DOMXPathExtended($this->schemaDocument);

		return $this;
	    } //end __wakeup()


	/**
	 * Prepares instance for serialization.
	 *
	 * @return array
	 *
	 * @untranslatable schemaKeeper
	 * @untranslatable schemaPrefix
	 * @untranslatable baseTypes
	 */

	public function __sleep()
	    {
		return array(
			"schemaKeeper",
			"schemaPrefix",
			"baseTypes",
		       );
	    } //end __sleep()


	/**
	 * Iteration routine.
	 *
	 * @param DOMXPathExtended $schemadocumentxpath XPath of the schema
	 * @param array            $basetypes           Base types list
	 * @param array            $schemakeeper        Predefined schema keeper array
	 *
	 * @return array
	 *
	 * @untranslatable schema
	 * @untranslatable schema/
	 * @untranslatable element
	 */

	public function iterate(DOMXPathExtended $schemadocumentxpath, array $basetypes, array $schemakeeper)
	    {
		$this->baseTypes = $basetypes;

		$this->schemaKeeper = $schemakeeper;

		$this->schemaDocumentXPath = $schemadocumentxpath;
		$this->schemaPrefix        = $this->getSchemaPrefix($this->schemaDocumentXPath);

		$this->filler = new SchemaProcessorFiller($this->schemaPrefix);

		$schemaelements = $this->schemaDocumentXPath->query("/" . $this->schemaPrefix . "schema");
		$schemaelement  = $schemaelements->item(0);
		$key            = "schema" . md5($schemaelement->getNodePath());

		$this->schemaKeeper[$this->schemaPrefix . "schema"]                = array();
		$this->schemaKeeper[$this->schemaPrefix . "schema"][$key]          = array();
		$this->schemaKeeper[$this->schemaPrefix . "schema"][$key]["xpath"] = $schemaelement->getNodePath();
		$this->schemaKeeper[$this->schemaPrefix . "schema"][$key]["type"]  = $this->schemaPrefix . "schema";

		$childrenarray = array();
		$rootelements  = $this->schemaDocumentXPath->query("/" . $this->schemaPrefix . "schema/" . $this->schemaPrefix . "element");
		foreach ($rootelements as $rootelement)
		    {
			$currentchildrenarray = $this->_iterate($rootelement);

			$customattributes = array("root" => true);

			$filleddata = $this->filler->pushToElementsArray($this->schemaKeeper, $rootelement, $currentchildrenarray, $customattributes);

			$this->schemaKeeper = $filleddata["schemakeeper"];
			$childrenarray[]    = $filleddata["schemakeeperlocation"];
		    } //end foreach

		$this->schemaKeeper[$this->schemaPrefix . "schema"][$key]["children"] = $childrenarray;

		$schemakeeperlocation = array(
					 "type"  => $this->schemaPrefix . "schema",
					 "xpath" => $schemaelement->getNodePath(),
					 "id"    => $key,
					);

		$this->schemaKeeper[$this->schemaPrefix . "schema"][$key]["schemakeeperlocation"] = $schemakeeperlocation;
		return $this->schemaKeeper;
	    } //end iterate()


	/**
	 * Recursive routine for walking down the schema DOMDocument.
	 *
	 * @param DOMElement $parentschemaelement Parent schema element
	 *
	 * @return array
	 *
	 * @untranslatable type
	 * @untranslatable ref
	 * @untranslatable base
	 * @untranslatable //*[@name=\"
	 */

	private function _iterate(DOMElement $parentschemaelement)
	    {
		$childrenindexesarray = array();

		$typeattribute = $parentschemaelement->getAttribute("type");
		$refattribute  = $parentschemaelement->getAttribute("ref");
		$baseattribute = $parentschemaelement->getAttribute("base");
		if (($typeattribute !== "" && in_array($typeattribute, $this->baseTypes) === false) || ($refattribute !== "") ||
		(in_array($baseattribute, $this->baseTypes) === false && $baseattribute !== ""))
		    {
			if (empty($baseattribute) === false)
			    {
				$searchedname = $baseattribute;
			    }
			else
			    {
				$searchedname = ((empty($refattribute) === false) ? $refattribute : $typeattribute);
			    }

			$typedefinitionelements = $this->schemaDocumentXPath->query("//*[@name=\"" . $searchedname . "\"]");
			$typedfinitionelement   = $typedefinitionelements->item(0);
			if ($typedfinitionelement === null)
			    {
				if (substr_count($searchedname, ":") === 1)
				    {
					$explodedname           = explode(":", $searchedname);
					$typedefinitionelements = $this->schemaDocumentXPath->query("//*[@name=\"" . $explodedname[1] . "\"]");
					$typedfinitionelement   = $typedefinitionelements->item(0);
				    }
			    }

			$childrenindexesarray[] = $this->_iterateSingle($typedfinitionelement);
		    } //end if

		$currentlyanalyzedelements = $this->getFirstLevelChildNodes($parentschemaelement);
		foreach ($currentlyanalyzedelements as $currentlyanalyzedelement)
		    {
			$childrenindexesarray[] = $this->_iterateSingle($currentlyanalyzedelement);
		    } //end foreach

		return $childrenindexesarray;
	    } //end _iterate()


	/**
	 * Iterates over single element.
	 *
	 * @param DOMElement $schemaelement Current schema node
	 *
	 * @return array
	 *
	 * @untranslatable element
	 * @untranslatable choice
	 * @untranslatable group
	 * @untranslatable annotation
	 */

	private function _iterateSingle(DOMElement $schemaelement)
	    {
		$indexedata = array();
		if ($schemaelement === null)
		    {
			return $indexedata;
		    }
		else
		    {
			$elementnodename = $schemaelement->nodeName;
			if (isset($this->schemaKeeper[$elementnodename]) === false)
			    {
				$this->schemaKeeper[$elementnodename] = array();
			    } //end if

			$currentlyanalyzedelementtype = $schemaelement->nodeName;
			switch ($currentlyanalyzedelementtype)
			    {
				case ($this->schemaPrefix . "element"):
					$indexedata = $this->_iterateOverElement($schemaelement);
				    break;
				case ($this->schemaPrefix . "choice"):
					$indexedata = $this->_iterateOverChoice($schemaelement);
				    break;
				case ($this->schemaPrefix . "group"):
					$indexedata = $this->_iterateOverGroup($schemaelement);
				    break;
				case ($this->schemaPrefix . "annotation"):
					$indexedata = $this->_iterateOverNode($schemaelement, false);
				    break;
				default:
					$indexedata = $this->_iterateOverNode($schemaelement);
				    break;
			    } //end switch

			return $indexedata;
		    } //end if
	    } //end _iterateSingle()


	/**
	 * Iterating over regular node.
	 *
	 * @param DOMElement $schemaelement  Current schema node
	 * @param bool       $iteratefurther Specifies if further iteration is required
	 *
	 * @return array
	 *
	 * @untranslatable schema/*[@name=\"
	 */

	private function _iterateOverNode(DOMElement $schemaelement, $iteratefurther = true)
	    {
		$elementnodename = $schemaelement->nodeName;
		$elementindex    = preg_replace("/^.+:/", "", $elementnodename) . md5($schemaelement->getNodePath());
		if (isset($this->schemaKeeper[$elementnodename][$elementindex]) === true)
		    {
			$iteratefurther = false;
		    }
		else
		    {
			$this->schemaKeeper[$elementnodename][$elementindex]          = array();
			$this->schemaKeeper[$elementnodename][$elementindex]["xpath"] = $schemaelement->getNodePath();
			$this->schemaKeeper[$elementnodename][$elementindex]["type"]  = $elementnodename;
			if ($schemaelement->hasAttributes() === true)
			    {
				$attributes = array();
				foreach ($schemaelement->attributes as $attribute)
				    {
					$attributes[$attribute->nodeName] = $attribute->nodeValue;
				    } //end foreach

				$this->schemaKeeper[$elementnodename][$elementindex]["attributes"] = $attributes;
			    } //end if
		    } //end if

		if ($iteratefurther === true)
		    {
			$childrenarray = $this->_iterate($schemaelement);
			$this->schemaKeeper[$elementnodename][$elementindex]["children"] = $childrenarray;
		    } //end if

		if (isset($this->schemaKeeper[$elementnodename][$elementindex]["attributes"]["type"]) === true
		&& in_array($this->schemaKeeper[$elementnodename][$elementindex]["attributes"]["type"], $this->baseTypes) === false)
		    {
			$searchedname = $this->schemaKeeper[$elementnodename][$elementindex]["attributes"]["type"];
			$queriesarray = array(
					 "/" . $this->schemaPrefix . "schema/*[@name=\"" . $searchedname . "\"]",
					 "/" . $this->schemaPrefix . "schema/*[@name=\"" . preg_replace("/^.+:/", "", $searchedname) . "\"]",
					);

			$elements = $this->queryMultiple($queriesarray);
			$this->_iterateSingle($elements->item(0));
		    }

		$this->_iterateOverTheNodeDeeperIteration($schemaelement);

		$schemakeeperlocation = array(
					 "type"  => $elementnodename,
					 "xpath" => $schemaelement->getNodePath(),
					 "id"    => $elementindex,
					);

		$this->schemaKeeper[$elementnodename][$elementindex]["schemakeeperlocation"] = $schemakeeperlocation;
		return $schemakeeperlocation;
	    } //end _iterateOverNode()


	/**
	 * Performs the iteration from the current node down the tree.
	 *
	 * @param DOMElement $schemaelement Iterated element
	 *
	 * @return void
	 *
	 * @untranslatable annotation
	 * @untranslatable restriction
	 * @untranslatable base
	 * @untranslatable schema/*[@name=\"
	 * @untranslatable union
	 */

	private function _iterateOverTheNodeDeeperIteration(DOMElement $schemaelement)
	    {
		$elementindex = preg_replace("/^.+:/", "", $schemaelement->nodeName) . md5($schemaelement->getNodePath());
		if ($schemaelement->nodeName === $this->schemaPrefix . "annotation")
		    {
			$this->schemaKeeper[$schemaelement->nodeName][$elementindex]["children"] = array();
		    }
		else if ($schemaelement->nodeName === $this->schemaPrefix . "restriction")
		    {
			if ($schemaelement->getAttribute("base") !== "" && isset($this->schemaKeeper["basetype"][$schemaelement->getAttribute("base")]) === false)
			    {
				$elementcandidates = $this->schemaDocumentXPath->query("/" . $this->schemaPrefix .
				"schema/*[@name=\"" . $schemaelement->getAttribute("base") . "\"]");
				if ($elementcandidates->length === 1)
				    {
					$this->_iterateSingle($elementcandidates->item(0));
				    }
			    }
		    }
		else if ($schemaelement->nodeName === $this->schemaPrefix . "union")
		    {
			$this->_iterateUnionSpecificElements($schemaelement);
		    } //end if
	    } //end _iterateOverTheNodeDeeperIteration()


	/**
	 * Processing simpleType elements that are mentioned in current union element.
	 *
	 * @param DOMElement $schemaelement Current schema node
	 *
	 * @return void
	 *
	 * @untranslatable memberTypes
	 * @untranslatable schema/
	 * @untranslatable simpleType[@name=\"
	 */

	private function _iterateUnionSpecificElements(DOMElement $schemaelement)
	    {
		$simpletypes = explode(" ", $schemaelement->getAttribute("memberTypes"));
		foreach ($simpletypes as $simpletypename)
		    {
			$elementcandidates = $this->schemaDocumentXPath->query("/" . $this->schemaPrefix .
			"schema/" . $this->schemaPrefix . "simpleType[@name=\"" . $simpletypename . "\"]");
			if ($elementcandidates->length === 1)
			    {
				$this->_iterateSingle($elementcandidates->item(0));
			    } //end if
		    } //end foreach
	    } //end _iterateUnionSpecificElements()


	/**
	 * Iterating over element schema node.
	 *
	 * @param DOMElement $schemaelement Current schema node
	 *
	 * @return array
	 *
	 * @untranslatable type
	 * @untranslatable ref
	 * @untranslatable schema/*[@name=\"
	 * @untranslatable element
	 */

	private function _iterateOverElement(DOMElement $schemaelement)
	    {
		$children      = array();
		$typeattribute = $schemaelement->getAttribute("type");
		$refattribute  = $schemaelement->getAttribute("ref");
		if ((empty($typeattribute) === true) && (empty($refattribute) === true))
		    {
			$underlyingdomelements = $this->getFirstLevelChildNodes($schemaelement);
			foreach ($underlyingdomelements as $underlyingdomelement)
			    {
				$children[] = $this->_iterateSingle($underlyingdomelement);
			    }
		    }
		else if (isset($this->schemaKeeper["basetype"][$typeattribute]) === false || empty($refattribute) === false)
		    {
			$searchedname = ((empty($refattribute) === false) ? $refattribute : $typeattribute);
			$queriesarray = array(
					 "/" . $this->schemaPrefix . "schema/*[@name=\"" . $searchedname . "\"]",
					 "/" . $this->schemaPrefix . "schema/*[@name=\"" . preg_replace("/^.+:/", "", $searchedname) . "\"]",
					);

			$elementsdescription = $this->queryMultiple($queriesarray);
			$children            = array($this->_iterateSingle($elementsdescription->item(0)));
		    }
		else
		    {
			$underlyingdomelements = $this->getFirstLevelChildNodes($schemaelement);
			foreach ($underlyingdomelements as $underlyingdomelement)
			    {
				$children[] = $this->_iterateSingle($underlyingdomelement);
			    }
		    } //end if

		$filleddata = $this->filler->pushToElementsArray($this->schemaKeeper, $schemaelement, $children);

		$this->schemaKeeper = $filleddata["schemakeeper"];

		$nameattribute = "element" . md5($schemaelement->getNodePath());
		return array(
			"type"  => ($this->schemaPrefix . "element"),
			"xpath" => $schemaelement->getNodePath(),
			"id"    => $nameattribute,
		       );
	    } //end _iterateOverElement()


	/**
	 * Iterating over choice schema node.
	 *
	 * @param DOMElement $schemaelement Current schema node
	 *
	 * @return array
	 *
	 * @untranslatable choice
	 */

	private function _iterateOverChoice(DOMElement $schemaelement)
	    {
		$children        = array();
		$underlyingnodes = $this->getFirstLevelChildNodes($schemaelement);
		foreach ($underlyingnodes as $underlyingnode)
		    {
			$childindexdata = $this->_iterateSingle($underlyingnode);
			$children       = array_merge($children, array($childindexdata));
		    } //end foreach

		$filleddata = $this->filler->pushToChoicesArray($this->schemaKeeper, $schemaelement, $children);

		$this->schemaKeeper = $filleddata["schemakeeper"];

		$nameattribute = "choice" . md5($schemaelement->getNodePath());
		return array(
			"type"  => ($this->schemaPrefix . "choice"),
			"xpath" => $schemaelement->getNodePath(),
			"id"    => $nameattribute,
		       );
	    } //end _iterateOverChoice()


	/**
	 * Iterating over group schema node.
	 *
	 * @param DOMElement $schemaelement Current schema node
	 *
	 * @return array
	 *
	 * @untranslatable ref
	 * @untranslatable schema/
	 * @untranslatable group[@name=\"
	 * @untranslatable group
	 */

	private function _iterateOverGroup(DOMElement $schemaelement)
	    {
		$childrenarray = array();
		$refattribute  = $schemaelement->getAttribute("ref");
		$queriesarray  = array(
				  "/" . $this->schemaPrefix . "schema/" . $this->schemaPrefix . "group[@name=\"" . $refattribute . "\"]",
				  "/" . $this->schemaPrefix . "schema/" . $this->schemaPrefix . "group[@name=\"" . preg_replace("/^.+:/", "", $refattribute) . "\"]",
				 );

		$elementsdescription = $this->queryMultiple($queriesarray);
		$referencedgroup     = $elementsdescription->item(0);
		$childrenarray       = $this->_iterate($referencedgroup);

		$elementnodename = $schemaelement->nodeName;
		$elementindex    = "group" . md5($schemaelement->getNodePath());

		$this->schemaKeeper[$elementnodename][$elementindex]             = array();
		$this->schemaKeeper[$elementnodename][$elementindex]["xpath"]    = $schemaelement->getNodePath();
		$this->schemaKeeper[$elementnodename][$elementindex]["type"]     = $elementnodename;
		$this->schemaKeeper[$elementnodename][$elementindex]["children"] = $childrenarray;

		if ($schemaelement->hasAttributes() === true)
		    {
			$attributes = array();
			foreach ($schemaelement->attributes as $attribute)
			    {
				$attributes[$attribute->nodeName] = $attribute->nodeValue;
			    } //end foreach

			$this->schemaKeeper[$elementnodename][$elementindex]["attributes"] = $attributes;
		    } //end if

		$schemakeeperlocation = array(
					 "type"  => $elementnodename,
					 "xpath" => $schemaelement->getNodePath(),
					 "id"    => $elementindex,
					);

		$this->schemaKeeper[$elementnodename][$elementindex]["schemakeeperlocation"] = $schemakeeperlocation;
		return $schemakeeperlocation;
	    } //end _iterateOverGroup()


    } //end class

?>
