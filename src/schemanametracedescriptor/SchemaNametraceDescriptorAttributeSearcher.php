<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;

/**
 * SchemaNametraceDescriptorAttributeSearcher class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/schemanametracedescriptor/SchemaNametraceDescriptorAttributeSearcher.php $
 */

class SchemaNametraceDescriptorAttributeSearcher
    {

	use DOMDocumentTools;

	/**
	 * Instaniate the class instance.
	 *
	 * @param DOMDocument $schemadocument Schema document
	 * @param array       $schemakeeper   Schema representation
	 * @param string      $schemaid       Current schema identificator
	 *
	 * @return void
	 */

	public function __construct(DOMDocument $schemadocument, array $schemakeeper, $schemaid)
	    {
		$this->schemaid = $schemaid;

		$this->schemadocument      = $schemadocument;
		$this->schemadocumentxpath = new DOMXPathExtended($this->schemadocument);
		$this->schemaPrefix        = $this->getSchemaPrefix($this->schemadocumentxpath);

		$this->schemaKeeper         = $schemakeeper;
		$this->_restrictionsearcher = new SchemaNametraceDescriptorRestrictionSearcher($this->schemadocument, $this->schemaKeeper, $this->schemaid);
		$this->_metasearcher        = new SchemaNametraceDescriptorMetaSearcher($this->schemadocument, $this->schemaKeeper, $this->schemaid);
	    } //end __construct()


	/**
	 * Invokes current instance from session
	 *
	 * @return SchemaNametraceDescriptorAttributeSearcher
	 */

	public function __wakeup()
	    {
		$this->schemadocumentxpath = new DOMXPathExtended($this->schemaDocument);
		return $this;
	    } //end __wakeup()


	/**
	 * Prepares instance for serialization.
	 *
	 * @return array
	 *
	 * @untranslatable schemadocument
	 * @untranslatable schemaKeeper
	 * @untranslatable schemaPrefix
	 * @untranslatable _restrictionsearcher
	 * @untranslatable _metasearcher
	 * @untranslatable schemaid
	 */

	public function __sleep()
	    {
		return array(
			"schemadocument",
			"schemaid",
			"schemaKeeper",
			"_metasearcher",
			"_restrictionsearcher",
			"schemaPrefix",
		       );
	    } //end __sleep()


	/**
	 * Getting attribute elements for provided element or choice.
	 *
	 * @param array $elementdata Full description of element
	 *
	 * @return array
	 */

	public function search(array $elementdata)
	    {
		if (isset($_SESSION["nametracedescriptorattributesearchercache"][$this->schemaid][$elementdata["xpath"]]) === true)
		    {
			return $_SESSION["nametracedescriptorattributesearchercache"][$this->schemaid][$elementdata["xpath"]];
		    }
		else
		    {
			$result = array();

			$schemakeeperelementdata = $this->schemaKeeper[$elementdata["schemakeeperlocation"]["type"]][$elementdata["schemakeeperlocation"]["id"]];
			foreach ($schemakeeperelementdata["children"] as $child)
			    {
				if (empty($child) === false)
				    {
					$result = array_merge($result, $this->_getAttributesIterate($child));
				    }
			    } //end foreach

			$_SESSION["nametracedescriptorattributesearchercache"][$this->schemaid][$elementdata["xpath"]] = $result;
			return $result;
		    }
	    } //end search()


	/**
	 * Get referenced attribute.
	 *
	 * @param array $keeperelement Element from schema keeper array
	 *
	 * @return array
	 *
	 * @untranslatable attribute
	 */

	private function _getReferencedAttribute(array $keeperelement)
	    {
		foreach ($keeperelement["children"] as $child)
		    {
			if ($child["type"] === $this->schemaPrefix . "attribute")
			    {
				$keeperelement = $this->schemaKeeper[$child["type"]][$child["id"]];
			    } //end if
		    } //end foreach

		return $keeperelement;
	    } //end _getReferencedAttribute()


	/**
	 * Getting attribute elements for provided element (recursive routine).
	 *
	 * @param array $element Short description of element
	 *
	 * @return array
	 *
	 * @untranslatable element
	 * @untranslatable restriction
	 * @untranslatable annotation
	 * @untranslatable sequence
	 * @untranslatable choice
	 * @untranslatable attribute
	 * @untranslatable optional
	 */

	private function _getAttributesIterate(array $element)
	    {
		$keeperelement = $this->schemaKeeper[$element["type"]][$element["id"]];

		$stopattributesearchelements = array(
						$this->schemaPrefix . "element",
						$this->schemaPrefix . "restriction",
						$this->schemaPrefix . "annotation",
						$this->schemaPrefix . "sequence",
						$this->schemaPrefix . "choice",
					       );

		if (((empty($keeperelement["children"]) === true) || in_array($keeperelement["type"], $stopattributesearchelements) === true)
		&& ($keeperelement["type"] !== $this->schemaPrefix . "attribute"))
		    {
			return array();
		    }
		else
		    {
			if (isset($keeperelement["attributes"]["ref"]) === true)
			    {
				$keeperelement = $this->_getReferencedAttribute($keeperelement);
			    } //end if

			$result = array();
			if ($keeperelement["type"] === $this->schemaPrefix . "attribute")
			    {
				$attributedescription                   = array();
				$attributedescription["name"]           = $keeperelement["attributes"]["name"];
				$attributedescription["nodexpath"]      = $keeperelement["xpath"];
				$attributedescription["nodeattributes"] = $keeperelement["attributes"];
				$attributedescription["keeperid"]       = $keeperelement["schemakeeperlocation"]["id"];

				if (isset($keeperelement["attributes"]["use"]) === true)
				    {
					$attributedescription["use"] = $keeperelement["attributes"]["use"];
				    }
				else
				    {
					$attributedescription["use"] = "optional";
				    } //end if

				$attributedescription["restrictions"] = $this->_restrictionsearcher->search($keeperelement);

				$attributedescription = array_merge($attributedescription, $this->_metasearcher->getDescriptionFromSchemaElement($element));
				$result               = array($attributedescription);
			    }
			else
			    {
				foreach ($keeperelement["children"] as $child)
				    {
					$result = array_merge($result, $this->_getAttributesIterate($child));
				    } //end foreach
			    } //end if

			return $result;
		    } //end if
	    } //end _getAttributesIterate()


    } //end class

?>
