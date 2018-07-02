<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;

/**
 * SchemaNametraceDescriptorRestrictionSearcher class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/schemanametracedescriptor/SchemaNametraceDescriptorRestrictionSearcher.php $
 */

class SchemaNametraceDescriptorRestrictionSearcher
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

		$this->schemaKeeper  = $schemakeeper;
		$this->_metasearcher = new SchemaNametraceDescriptorMetaSearcher($this->schemadocument, $this->schemaKeeper, $this->schemaid);
	    } //end __construct()


	/**
	 * Invokes current instance from session
	 *
	 * @return SchemaNametraceDescriptorRestrictionSearcher
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
	 * @untranslatable schemaid
	 * @untranslatable schemaPrefix
	 * @untranslatable schemaKeeper
	 * @untranslatable _metasearcher
	 */

	public function __sleep()
	    {
		return array(
			"schemadocument",
			"schemaid",
			"schemaKeeper",
			"_metasearcher",
			"schemaPrefix",
		       );
	    } //end __sleep()


	/**
	 * Getting restrictions for provided element
	 *
	 * @param array $element Short description of element
	 *
	 * @return array
	 *
	 * @untranslatable element
	 * @untranslatable attribute
	 */

	public function search(array $element)
	    {
		if (isset($_SESSION["nametracedescriptorrestrictionsearchercache"][$this->schemaid][$element["xpath"]]) === true)
		    {
			return $_SESSION["nametracedescriptorrestrictionsearchercache"][$this->schemaid][$element["xpath"]];
		    }
		else
		    {
			if ($element["type"] !== $this->schemaPrefix . "element" && $element["type"] !== $this->schemaPrefix . "attribute")
			    {
				$result = array();
			    }
			else
			    {
				$restrictions = array();
				foreach ($element["children"] as $elementaddress)
				    {
					$restrictions = array_merge($restrictions, $this->_getRestrictionsIterate($elementaddress));
				    } //end foreach

				$result = $restrictions;
			    } //end if

			$_SESSION["nametracedescriptorrestrictionsearchercache"][$this->schemaid][$element["xpath"]] = $result;
			return $result;
		    } //end if
	    } //end search()


	/**
	 * Getting restrictions for provided element (recursive routine).
	 *
	 * @param array $elementaddress Short description of element
	 *
	 * @return array
	 *
	 * @untranslatable element
	 * @untranslatable attribute
	 * @untranslatable annotation
	 * @untranslatable sequence
	 * @untranslatable choice
	 * @untranslatable union
	 * @untranslatable restriction
	 * @untranslatable simpleType
	 */

	private function _getRestrictionsIterate(array $elementaddress)
	    {
		$currentelement = $this->schemaKeeper[$elementaddress["type"]][$elementaddress["id"]];

		$exisitngrestrictions = array();
		$stopelements         = array(
					 $this->schemaPrefix . "element",
					 $this->schemaPrefix . "attribute",
					 $this->schemaPrefix . "annotation",
					 $this->schemaPrefix . "sequence",
					 $this->schemaPrefix . "choice",
					);

		if ($currentelement["type"] === $this->schemaPrefix . "union")
		    {
			$exisitngrestrictions = $this->_getRestrictionsFromUnion($currentelement);
		    }

		if (empty($exisitngrestrictions) === true && ((empty($currentelement["children"]) === true) || in_array($currentelement["type"], $stopelements) === true))
		    {
			return array();
		    }
		else
		    {
			if ($currentelement["type"] === $this->schemaPrefix . "restriction")
			    {
				$exisitngrestrictions = array_merge($exisitngrestrictions, $this->_getRestrictionsFromAttributeReferences($currentelement));
				foreach ($currentelement["children"] as $elementaddress)
				    {
					if ($elementaddress["type"] !== $this->schemaPrefix . "simpleType")
					    {
						$restriction = $this->schemaKeeper[$elementaddress["type"]][$elementaddress["id"]];

						$exisitngrestrictionsdata = $this->_addExistingRestrictions($exisitngrestrictions, $restriction);
						$exisitngrestrictions     = $exisitngrestrictionsdata["exisitngrestrictions"];
						$restrictionlareadyexists = $exisitngrestrictionsdata["restrictionlareadyexists"];

						if ($restrictionlareadyexists === false)
						    {
							$newrestriction             = array();
							$newrestriction["type"]     = $restriction["type"];
							$newrestriction["values"]   = array();
							$newrestriction["values"][] = array(
										       "value"           => $restriction["attributes"]["value"],
										       "metadescription" => $this->_metasearcher->getDescriptionFromSchemaElement($restriction),
										      );

							$exisitngrestrictions[] = $newrestriction;
						    } //end if
					    } //end if
				    } //end foreach
			    }
			else
			    {
				foreach ($currentelement["children"] as $elementaddress)
				    {
					$exisitngrestrictions = array_merge($exisitngrestrictions, $this->_getRestrictionsIterate($elementaddress));
				    } //end foreach
			    } //end if

			return $exisitngrestrictions;
		    } //end if
	    } //end _getRestrictionsIterate()


	/**
	 * Process already existing restrictions.
	 *
	 * @param array $exisitngrestrictions Already defined restrictions
	 * @param array $restriction          Restriction description
	 *
	 * @return array
	 */

	private function _addExistingRestrictions(array $exisitngrestrictions, array $restriction)
	    {
		$restrictionlareadyexists = false;
		foreach ($exisitngrestrictions as $key => $exisitngrestriction)
		    {
			if ($exisitngrestriction["type"] === $restriction["type"])
			    {
				$restrictionlareadyexists               = true;
				$metadescription                        = $this->_metasearcher->getDescriptionFromSchemaElement($restriction);
				$exisitngrestrictions[$key]["values"][] = array(
									   "value"           => $restriction["attributes"]["value"],
									   "metadescription" => $metadescription,
									  );
			    }
		    } //end foreach

		return array(
			"exisitngrestrictions"     => $exisitngrestrictions,
			"restrictionlareadyexists" => $restrictionlareadyexists,
		       );
	    } //end _addExistingRestrictions()


	/**
	 * Getting restrictions from union element.
	 *
	 * @param array $unionelement Short description of element
	 *
	 * @return array
	 *
	 * @untranslatable simpleType
	 */

	private function _getRestrictionsFromUnion(array $unionelement)
	    {
		$restrictions = array();
		$simpletypes  = explode(" ", $unionelement["attributes"]["memberTypes"]);
		foreach ($simpletypes as $simpletype)
		    {
			$chosensimpletype = null;
			foreach ($this->schemaKeeper[$this->schemaPrefix . "simpleType"] as $simpletypecandidate)
			    {
				if (isset($simpletypecandidate["attributes"]["name"]) === true &&
				$simpletypecandidate["attributes"]["name"] === $simpletype)
				    {
					$chosensimpletype = $simpletypecandidate["schemakeeperlocation"];
					break;
				    }
			    } //end foreach

			if ($chosensimpletype === null)
			    {
				$restriction = array(
						array(
						 "type"   => $simpletype,
						 "values" => array(),
						),
					       );

				$restrictions = $this->_mergeRestrictions($restrictions, $restriction);
			    }
			else
			    {
				$restrictions = $this->_mergeRestrictions($restrictions, $this->_getRestrictionsIterate($chosensimpletype));
			    }
		    } //end foreach

		return $restrictions;
	    } //end _getRestrictionsFromUnion()


	/**
	 * Merging restrctions in one structure.
	 *
	 * @param array $restrictionset Set of current restrctions
	 * @param array $restrictions   Appended restrictions
	 *
	 * @return array
	 */

	private function _mergeRestrictions(array $restrictionset, array $restrictions)
	    {
		foreach ($restrictions as $restriction)
		    {
			$restrictionsmerged = false;
			foreach ($restrictionset as $key => $existingrestriction)
			    {
				if ($existingrestriction["type"] === $restriction["type"])
				    {
					$restrictionsmerged             = true;
					$restrictionset[$key]["values"] = array_merge($restrictionset[$key]["values"], $restriction["values"]);
				    } //end if
			    } //end foreach

			if ($restrictionsmerged === false)
			    {
				$restrictionset[] = $restriction;
			    } //end if
		    } //end foreach

		return $restrictionset;
	    } //end _mergeRestrictions()


	/**
	 * Returns set of restrictions that are defined through the attribute references (@base).
	 *
	 * @param array $currentelement Analyzed element
	 *
	 * @return array
	 *
	 * @untranslatable simpleType
	 */

	private function _getRestrictionsFromAttributeReferences(array $currentelement)
	    {
		$exisitngrestrictions = array();
		if (isset($currentelement["attributes"]["base"]) === true)
		    {
			if (isset($this->schemaKeeper["basetype"][$currentelement["attributes"]["base"]]) === false)
			    {
				foreach ($this->schemaKeeper[$this->schemaPrefix . "simpleType"] as $simpletype)
				    {
					if (isset($simpletype["attributes"]) === true && $simpletype["attributes"]["name"] === $currentelement["attributes"]["base"])
					    {
						$exisitngrestrictions = array_merge($exisitngrestrictions, $this->_getRestrictionsIterate($simpletype["schemakeeperlocation"]));
					    }
				    } //end foreach
			    } //end if
		    } //end if

		return $exisitngrestrictions;
	    } //end _getRestrictionsFromAttributeReferences()


    } //end class

?>
