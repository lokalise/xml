<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;
use \Exception;

/**
 * SchemaNametraceDescriptorFormatter class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/schemanametracedescriptor/SchemaNametraceDescriptorFormatter.php $
 */

class SchemaNametraceDescriptorFormatter
    {

	/**
	 * Last element reference
	 *
	 * @var mixed
	 */
	private $_lastelementreference = false;

	/**
	 * Discovery mode
	 *
	 * @var string
	 */
	protected $discoverymode;

	/**
	 * Schema in DOM form
	 *
	 * @var DOMDocument
	 */
	protected $schemaDocument;

	/**
	 * XPath object for schema document
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
	 * Place for compiled schema
	 *
	 * @var array
	 */
	protected $schemaKeeper;

	use SchemaNametrace, DOMDocumentTools;

	/**
	 * Instaniate the class instance.
	 *
	 * @param DOMDocument $completeddocument Schema document with all inclusions and imports
	 * @param array       $schemakeeper      Compiled schema array
	 * @param string      $schemaid          Current schema identificator
	 * @param string      $discoverymode     Specifies the way childrens are described
	 *
	 * @return void
	 *
	 * @throws Exception Wrong discovery mode
	 *
	 * @exceptioncode EXCEPTION_WRONG_DISCOVERY_MODE
	 *
	 * @untranslatable simple
	 * @untranslatable complex
	 */

	public function __construct(DOMDocument $completeddocument, array $schemakeeper, $schemaid, $discoverymode = "simple")
	    {
		$this->schemaid = $schemaid;

		if (isset($_SESSION["nametracedescriptorformattercache"][$this->schemaid]) === false)
		    {
			$_SESSION["nametracedescriptorformattercache"][$this->schemaid] = array();
		    }

		$this->schemaKeeper = $schemakeeper;

		$discoveryvariants = array(
				      "simple",
				      "complex",
				     );

		if (in_array($discoverymode, $discoveryvariants) === true)
		    {
			$this->discoverymode = $discoverymode;
		    }
		else
		    {
			throw new Exception(_("Wrong discovery value was provided") . " " . $discoverymode, EXCEPTION_WRONG_DISCOVERY_MODE);
		    }

		$this->schemaDocument      = $completeddocument;
		$this->schemaDocumentXPath = new DOMXPathExtended($this->schemaDocument);

		$this->schemaPrefix = $this->getSchemaPrefix($this->schemaDocumentXPath);

		$this->_restrictionsearcher = new SchemaNametraceDescriptorRestrictionSearcher($this->schemaDocument, $this->schemaKeeper, $this->schemaid);
		$this->_metasearcher        = new SchemaNametraceDescriptorMetaSearcher($this->schemaDocument, $this->schemaKeeper, $this->schemaid);
		$this->_attributesearcher   = new SchemaNametraceDescriptorAttributeSearcher($this->schemaDocument, $this->schemaKeeper, $this->schemaid);
	    } //end __construct()


	/**
	 * Invokes current instance from session
	 *
	 * @return SchemaNametraceDescriptorFormatter
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
	 * @untranslatable schemaDocument
	 * @untranslatable schemaKeeper
	 * @untranslatable schemaPrefix
	 * @untranslatable discoverymode
	 * @untranslatable _lastelementreference
	 */

	public function __sleep()
	    {
		unset($this->schemaDocumentXPath);
		return array(
			"_lastelementreference",
			"schemaDocument",
			"schemaKeeper",
			"schemaPrefix",
			"discoverymode",
		       );
	    } //end __sleep()


	/**
	 * Formats output schema element description according to convention, defined in the description of this class.
	 *
	 * @param array $elementinformation Element description from the schemaKeeper
	 * @param bool  $getchildren        Specifies if children lookup is needed
	 *
	 * @return array
	 *
	 * @untranslatable schema
	 * @untranslatable choice
	 * @untranslatable choice:
	 * @untranslatable unbounded
	 */

	public function format(array $elementinformation, $getchildren = true)
	    {
		if (isset($_SESSION["nametracedescriptorformattercache"][$this->schemaid][$elementinformation["element"]["xpath"] .
		var_export($elementinformation["lastelementreference"], true) . var_export($getchildren, true)]) === true)
		    {
			return $_SESSION["nametracedescriptorformattercache"][$this->schemaid][$elementinformation["element"]["xpath"] .
			var_export($elementinformation["lastelementreference"], true) . var_export($getchildren, true)];
		    }
		else
		    {
			$elementdata                 = $elementinformation["element"];
			$this->_lastelementreference = $elementinformation["lastelementreference"];

			if ($elementdata["type"] === $this->schemaPrefix . "schema")
			    {
				$result = $this->_formatSchema($elementdata);
			    }
			else
			    {
				if (isset($elementdata["attributes"]["name"]) === false && isset($elementdata["attributes"]["ref"]) === true)
				    {
					$elementdata = $this->schemaKeeper[$elementdata["children"][0]["type"]][$elementdata["children"][0]["id"]];
				    } //end if

				$singleelementdata = array();
				if ($elementdata["type"] === $this->schemaPrefix . "choice")
				    {
					$descriptionitemname              = "choice:" . md5($elementdata["xpath"]);
					$singleelementdata["branchnames"] = $this->_getBranchNames($elementdata);
				    }
				else
				    {
					$descriptionitemname = $elementdata["attributes"]["name"];
				    } //end if

				$singleelementdata["name"]                 = $descriptionitemname;
				$singleelementdata["schemakeeperlocation"] = $elementdata["schemakeeperlocation"];
				$singleelementdata["nodexpath"]            = $elementdata["xpath"];

				$metadescription   = $this->_metasearcher->getDescription($elementinformation);
				$singleelementdata = array_merge($singleelementdata, $metadescription);

				$singleelementdata["nodeattributes"] = $elementdata["attributes"];
				if (isset($elementdata["attributes"]["minOccurs"]) === true)
				    {
					$singleelementdata["nodeattributes"]["minOccurs"] = intval($elementdata["attributes"]["minOccurs"]);
				    }
				else
				    {
					$singleelementdata["nodeattributes"]["minOccurs"] = 1;
				    } //end if

				if (isset($elementdata["attributes"]["maxOccurs"]) === true)
				    {
					$singleelementdata["nodeattributes"]["maxOccurs"] = intval($elementdata["attributes"]["maxOccurs"]);
					if ($elementdata["attributes"]["maxOccurs"] === "unbounded")
					    {
						$singleelementdata["nodeattributes"]["attributes"]["maxOccurs"] = "unbounded";
						$singleelementdata["nodeattributes"]["maxOccurs"]               = "unbounded";
					    } //end if
				    }
				else
				    {
					$singleelementdata["nodeattributes"]["maxOccurs"] = 1;
				    } //end if

				if ($getchildren === true)
				    {
					$singleelementdata = $this->_getChildren($singleelementdata, $elementdata);
				    } //end if

				$singleelementdata["elementattributes"] = $this->_attributesearcher->search($elementdata);
				$singleelementdata["restrictions"]      = $this->_restrictionsearcher->search($elementdata);
				$result = $singleelementdata;
			    } //end if

			$_SESSION["nametracedescriptorformattercache"][$this->schemaid][$elementinformation["element"]["xpath"] .
			var_export($elementinformation["lastelementreference"], true) . var_export($getchildren, true)] = $result;
			return $result;
		    } //end if
	    } //end format()


	/**
	 * Getting children for the element.
	 *
	 * @param array $singleelementdata Formatted element
	 * @param array $elementdata       Element description from the schemaKeeper
	 *
	 * @return array
	 *
	 * @untranslatable leafelement
	 */

	private function _getChildren(array $singleelementdata, array $elementdata)
	    {
		$singleelementdata = $this->_getChildrenForDescriptionItem($singleelementdata, $elementdata);
		if ($singleelementdata["elementtype"] === "leafelement" && isset($singleelementdata["nodeattributes"]["type"]) === false)
		    {
			$possibletype = $this->_getTypeForElement($elementdata);
			if ($possibletype !== false)
			    {
				$singleelementdata["nodeattributes"]["type"] = $possibletype;
			    }
		    } //end if

		return $singleelementdata;
	    } //end _getChildren()


	/**
	 * Providing special formatiing for schema elements.
	 *
	 * @param array $elementdata Element description from the schemaKeeper
	 *
	 * @return array
	 */

	private function _formatSchema(array $elementdata)
	    {
		$compoundelement = array(
				    "element"              => $elementdata,
				    "lastelementreference" => false,
				   );

		$metadescription = $this->_metasearcher->getDescription($compoundelement);
		$elementdata     = array_merge($elementdata, $metadescription);

		return $elementdata;
	    } //end _formatSchema()


	/**
	 * Getting "type" attribute for specified element.
	 *
	 * @param array $elementdata Element description from the schemaKeeper
	 *
	 * @return string
	 */

	private function _getTypeForElement(array $elementdata)
	    {
		$result = false;
		foreach ($elementdata["children"] as $child)
		    {
			$childelement = $this->schemaKeeper[$child["type"]][$child["id"]];
			if (isset($childelement["attributes"]["base"]) === true)
			    {
				$result = $childelement["attributes"]["base"];
				break;
			    }

			$subresult = $this->_getTypeForElement($childelement);
			if ($subresult !== false)
			    {
				$result = $subresult;
				break;
			    } //end if
		    } //end foreach

		return $result;
	    } //end _getTypeForElement()


	/**
	 * Fetching children for description item.
	 *
	 * @param array $singleelementdata Element description
	 * @param array $elementdata       Element description from the schema keeper
	 *
	 * @return array
	 *
	 * @untranslatable choice
	 * @untranslatable annotation
	 * @untranslatable attribute
	 * @untranslatable leafelement
	 * @untranslatable hierarchyelement
	 */

	private function _getChildrenForDescriptionItem(array $singleelementdata, array $elementdata)
	    {
		$localsingleelementdata = $singleelementdata;

		if ($this->_isChoiceNametraceItem($localsingleelementdata["name"]) === true)
		    {
			$localsingleelementdata["elementtype"] = "choice";

			$localsingleelementdata["children"] = array();
			if (isset($elementdata["children"]) === true)
			    {
				$localsingleelementdata["children"]["branches"] = array();
				foreach ($elementdata["children"] as $child)
				    {
					if ($child["type"] !== $this->schemaPrefix . "annotation" && $child["type"] !== $this->schemaPrefix . "attribute")
					    {
						$localsingleelementdata["children"]["branches"][]
						    = $this->_childrenElementsLookup($this->schemaKeeper[$child["type"]][$child["id"]]);
					    } //end if
				    } //end foreach
			    } //end if
		    }
		else
		    {
			$localsingleelementdata["children"] = array();
			if (isset($elementdata["children"]) === true)
			    {
				foreach ($elementdata["children"] as $child)
				    {
					if ($child["type"] !== $this->schemaPrefix . "annotation" && $child["type"] !== $this->schemaPrefix . "attribute")
					    {
						$localsingleelementdata["children"] = array_merge($localsingleelementdata["children"],
						$this->_childrenElementsLookup($this->schemaKeeper[$child["type"]][$child["id"]]));
					    }
				    } //end foreach
			    } //end if

			$realchildren = $this->_getRealChildren($localsingleelementdata);
			if (empty($realchildren) === true)
			    {
				$localsingleelementdata["elementtype"] = "leafelement";
			    }
			else
			    {
				$localsingleelementdata["elementtype"] = "hierarchyelement";
			    } //end if
		    } //end if

		return $localsingleelementdata;
	    } //end _getChildrenForDescriptionItem()


	/**
	 * Getting real children for the element.
	 *
	 * @param array $element Element description
	 *
	 * @return array
	 */

	private function _getRealChildren(array $element)
	    {
		$resultarray = array();
		foreach ($element["children"] as $child)
		    {
			if (isset($child["children"]) === false || isset($child["elementtype"]) === true)
			    {
				$resultarray[] = $child;
			    }
			else
			    {
				$resultarray = array_merge($resultarray, $this->_getRealChildren($child));
			    }
		    } //end foreach

		return $resultarray;
	    } //end _getRealChildren()


	/**
	 * Returns names for branches for given choice element.
	 *
	 * @param array $elementdata Element schema keeper location.
	 *
	 * @return array
	 *
	 * @untranslatable annotation
	 * @untranslatable .//dc:title
	 */

	private function _getBranchNames(array $elementdata)
	    {
		$branchnames = array();
		foreach ($elementdata["children"] as $branch)
		    {
			if ($branch["type"] !== $this->schemaPrefix . "annotation")
			    {
				$result        = null;
				$keeperelement = $this->schemaKeeper[$branch["type"]][$branch["id"]];
				foreach ($keeperelement["children"] as $child)
				    {
					if ($child["type"] === $this->schemaPrefix . "annotation")
					    {
						$keeperchildelement   = $this->schemaKeeper[$child["type"]][$child["id"]];
						$annotationcandidates = $this->schemaDocumentXPath->query($keeperchildelement["xpath"]);
						$annotation           = $annotationcandidates->item(0);
						$titlecandidates      = $this->schemaDocumentXPath->query(".//dc:title", $annotation);

						if ($titlecandidates->length === 1)
						    {
							$result = $titlecandidates->item(0)->textContent;
							break;
						    }
					    } //end if
				    } //end foreach

				if ($result === null && isset($keeperelement["attributes"]) === true && isset($keeperelement["attributes"]["name"]) === true)
				    {
					$result = $keeperelement["attributes"]["name"];
				    } //end if

				$branchnames[] = $result;
			    } //end if
		    } //end foreach

		return $branchnames;
	    } //end _getBranchNames()


	/**
	 * Finds underlying schema elements.
	 *
	 * @param array $element Searched element
	 *
	 * @return array
	 *
	 * @untranslatable element
	 * @untranslatable choice
	 * @untranslatable complex
	 * @untranslatable attribute
	 * @untranslatable annotation
	 */

	private function _childrenElementsLookup(array $element)
	    {
		$childrenelementarray = array();
		if ($element["type"] === ($this->schemaPrefix . "element") || $element["type"] === ($this->schemaPrefix . "choice"))
		    {
			$compoundelement = array(
					    "element"              => $element,
					    "lastelementreference" => false,
					   );

			$result = array($this->format($compoundelement, false));
		    }
		else
		    {
			$virtualelement = false;
			if ($this->discoverymode === "complex")
			    {
				$compoundelement = array(
						    "element"              => $element,
						    "lastelementreference" => false,
						   );

				$virtualelementmeta = $this->_metasearcher->getDescription($compoundelement, false);
				if (empty($virtualelementmeta["appinfo"]) === false)
				    {
					$virtualelement = array();
					$virtualelement = array_merge($virtualelement, $virtualelementmeta);
					$virtualelement["schemakeeperlocation"] = $element["schemakeeperlocation"];
				    } //end if
			    } //end if

			if (isset($element["children"]) === true)
			    {
				foreach ($element["children"] as $child)
				    {
					if ($child["type"] !== $this->schemaPrefix . "annotation" && $child["type"] !== $this->schemaPrefix . "attribute")
					    {
						$subresult            = $this->_childrenElementsLookup($this->schemaKeeper[$child["type"]][$child["id"]]);
						$childrenelementarray = array_merge($childrenelementarray, $subresult);
					    }
				    } //end foreach
			    } //end if

			if ($virtualelement !== false)
			    {
				$virtualelement["children"] = $childrenelementarray;
				$childrenelementarray       = array($virtualelement);
			    } //end if

			$result = $childrenelementarray;
		    } //end if

		return $result;
	    } //end _childrenElementsLookup()


    } //end class

?>
