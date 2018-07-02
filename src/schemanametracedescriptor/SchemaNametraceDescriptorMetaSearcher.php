<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;

/**
 * SchemaNametraceDescriptorMetaSearcher class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/schemanametracedescriptor/SchemaNametraceDescriptorMetaSearcher.php $
 */

class SchemaNametraceDescriptorMetaSearcher
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

		if (is_file($this->schemadocument->documentURI) === true)
		    {
			$externals       = new SchemaExternals();
			$this->_schemaID = $externals->getID($this->schemadocument->documentURI);
		    }
		else
		    {
			$this->_schemaID = md5($this->schemadocument->documentURI);
		    }

		$this->schemaKeeper = $schemakeeper;
	    } //end __construct()


	/**
	 * Invokes current instance from session
	 *
	 * @return SchemaNametraceDescriptorMetaSearcher
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
	 * @untranslatable schemaid
	 */

	public function __sleep()
	    {
		return array(
			"schemadocument",
			"schemaid",
			"schemaKeeper",
			"schemaPrefix",
		       );
	    } //end __sleep()


	/**
	 * Fetches documentation and appinfo data from schema.
	 *
	 * @param array $elementinformation Short element description
	 * @param bool  $deepsearch         Specificies if the search can be preformed on children nodes
	 *
	 * @return array
	 *
	 * @untranslatable choice
	 */

	public function getDescription(array $elementinformation, $deepsearch = true)
	    {
		$deepsearchstringvalue           = var_export($deepsearch, true);
		$lastelementreferencestringvalue = var_export($elementinformation["lastelementreference"], true);
		$sessionstorageid                = md5($elementinformation["element"]["xpath"] . $deepsearchstringvalue . $lastelementreferencestringvalue);

		if (isset($_SESSION["xmlsessionstorage"]["elementinformationstorage"][$this->_schemaID][$sessionstorageid]) === true)
		    {
			return $_SESSION["xmlsessionstorage"]["elementinformationstorage"][$this->_schemaID][$sessionstorageid];
		    }
		else
		    {
			$appinfos       = array();
			$documentations = array();
			if ($elementinformation["lastelementreference"] !== false)
			    {
				$lastelementreferenceresult = $this->getDescriptionFromSchemaElement($elementinformation["lastelementreference"], false);

				$appinfos[]       = $lastelementreferenceresult["appinfo"];
				$documentations[] = $lastelementreferenceresult["documentation"];
			    }

			$elementresult    = $this->getDescriptionFromSchemaElement($elementinformation["element"]);
			$appinfos[]       = $elementresult["appinfo"];
			$documentations[] = $elementresult["documentation"];

			if ($deepsearch === true)
			    {
				if ($elementinformation["element"]["schemakeeperlocation"]["type"] !== $this->schemaPrefix . "choice")
				    {
					foreach ($elementinformation["element"]["children"] as $child)
					    {
						$currentelement   = $this->schemaKeeper[$child["type"]][$child["id"]];
						$deeperresult     = $this->getDescriptionFromSchemaElement($currentelement);
						$appinfos[]       = $deeperresult["appinfo"];
						$documentations[] = $deeperresult["documentation"];
					    } //end foreach
				    } //end if
			    } //end if

			$result = array(
				   "appinfo"       => $this->_mergeMeta($appinfos),
				   "documentation" => $this->_mergeMeta($documentations),
				  );

			if (isset($_SESSION["xmlsessionstorage"]["elementinformationstorage"]) === true)
			    {
				$_SESSION["xmlsessionstorage"]["elementinformationstorage"][$this->_schemaID][$sessionstorageid] = $result;
			    }
			else
			    {
				$_SESSION["xmlsessionstorage"]["elementinformationstorage"] = array();
			    }

			return $result;
		    } //end if
	    } //end getDescription()


	/**
	 * Fetching annotation elements from provided element.
	 *
	 * @param array $elementdata    Element description
	 * @param bool  $checkreference Specifies if referenced element should be checked
	 *
	 * @return array
	 */

	public function getDescriptionFromSchemaElement(array $elementdata, $checkreference = true)
	    {
		$checkreferencestringvalue = var_export($checkreference, true);
		$sessionstorageid          = md5($elementdata["xpath"] . $checkreferencestringvalue);

		if (isset($_SESSION["xmlsessionstorage"]["elementdescriptionstorage"][$this->_schemaID][$sessionstorageid]) === true)
		    {
			return $_SESSION["xmlsessionstorage"]["elementdescriptionstorage"][$this->_schemaID][$sessionstorageid];
		    }
		else
		    {
			$result = $this->_checkReferencedElement($elementdata, $checkreference);
			if (isset($_SESSION["xmlsessionstorage"]["elementdescriptionstorage"]) === true)
			    {
				$_SESSION["xmlsessionstorage"]["elementdescriptionstorage"][$this->_schemaID][$sessionstorageid] = $result;
			    }
			else
			    {
				$_SESSION["xmlsessionstorage"]["elementdescriptionstorage"] = array();
			    }

			return $result;
		    } //end if
	    } //end getDescriptionFromSchemaElement()


	/**
	 * Meges data from multiple containers.
	 *
	 * @param array $metas Meta containers
	 *
	 * @return string
	 *
	 * @untranslatable utf-8
	 */

	private function _mergeMeta(array $metas)
	    {
		$resultingmeta = array();

		$mergeddocument = false;
		foreach ($metas as $metasource)
		    {
			if (is_string($metasource) === true)
			    {
				if ($mergeddocument === false)
				    {
					$mergeddocument = new SerializableDOMDocument("1.0", "utf-8");
					$mergeddocument->loadXML($metasource);
				    }
				else
				    {
					$appendeddocument = new SerializableDOMDocument("1.0", "utf-8");
					$appendeddocument->loadXML($metasource);
					$candidates = $this->getFirstLevelChildNodes($appendeddocument->documentElement);
					foreach ($candidates as $candidate)
					    {
						$mergeddocument->documentElement->appendChild($mergeddocument->importNode($candidate, true));
					    }
				    }
			    }
		    } //end foreach

		if ($mergeddocument !== false)
		    {
			$resultingmeta = $mergeddocument->saveXML();
		    }

		return $resultingmeta;
	    } //end _mergeMeta()


	/**
	 * Check referenced element for meta information.
	 *
	 * @param array $elementdata    Element description
	 * @param bool  $checkreference Specifies if referenced element should be checked
	 *
	 * @return array
	 *
	 * @untranslatable schema/
	 * @untranslatable [@name=\"
	 */

	private function _checkReferencedElement(array $elementdata, $checkreference)
	    {
		$schemaelementnodelist = $this->schemadocumentxpath->query($elementdata["xpath"]);
		$schemaelement         = $schemaelementnodelist->item(0);
		$children              = $this->getFirstLevelChildNodes($schemaelement);

		if (isset($elementdata["attributes"]["ref"]) === true && $checkreference === true)
		    {
			$candidates = $this->schemadocumentxpath->query("/" . $this->schemaPrefix . "schema/" .
			$elementdata["schemakeeperlocation"]["type"] . "[@name=\"" . $elementdata["attributes"]["ref"] . "\"]");
			if ($candidates->length === 1)
			    {
				$children = array_merge($children, $this->getFirstLevelChildNodes($candidates->item(0)));
			    }
		    } //end if

		$result = $this->_processReferencedElements($children);
		return $result;
	    } //end _checkReferencedElement()


	/**
	 * Processes referenced elements, fetches appinfo and documentation sections.
	 *
	 * @param array $referencedelements Set of referenced elements
	 *
	 * @return array
	 *
	 * @untranslatable annotation
	 * @untranslatable utf-8
	 * @untranslatable documentation
	 * @untranslatable dc
	 * @untranslatable appinfo
	 */

	private function _processReferencedElements(array $referencedelements)
	    {
		$result = array(
			   "documentation" => array(),
			   "appinfo"       => array(),
			  );

		if (empty($referencedelements) === false)
		    {
			foreach ($referencedelements as $referencedelement)
			    {
				if ($referencedelement->nodeName === $this->schemaPrefix . "annotation")
				    {
					$annotationchildren = $this->getFirstLevelChildNodes($referencedelement);
					foreach ($annotationchildren as $annotationchild)
					    {
						$annotationchilddocument = new SerializableDOMDocument("1.0", "utf-8");
						$localannotationchild    = $annotationchilddocument->importNode($annotationchild, true);
						$annotationchilddocument->appendChild($localannotationchild);
						if ($annotationchild->nodeName === $this->schemaPrefix . "documentation")
						    {
							$annotationchilddocument->documentElement->removeAttributeNS("http://dublincore.org/schemas/xmls/qdc/2008/02/11/dc.xsd",
							"dc");
							$result["documentation"] = $annotationchilddocument->saveXML();
						    }
						else if ($annotationchild->nodeName === $this->schemaPrefix . "appinfo")
						    {
							$rootchildren    = $this->getFirstLevelChildNodes($annotationchilddocument->documentElement);
							$appinfodocument = new SerializableDOMDocument("1.0", "utf-8");
							$appinfonode     = $appinfodocument->importNode($rootchildren[0], true);
							$appinfodocument->appendChild($appinfonode);
							$result["appinfo"] = $appinfodocument->saveXML();
						    } //end if
					    } //end foreach
				    } //end if
			    } //end foreach
		    } //end if

		return $result;
	    } //end _processReferencedElements()


    } //end class

?>
