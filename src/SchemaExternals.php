<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;
use \DOMElement;
use \DOMNode;
use \Exception;

/**
 * Class for managing schema externals
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaExternals.php $
 */

class SchemaExternals
    {

	use DOMDocumentTools;

	/**
	 * List of schema files used
	 *
	 * @var array
	 */
	private $_listOfParticipatingSchemas;

	/**
	 * Mapping array
	 *
	 * @var array
	 */
	private $_mappingArray;

	/**
	 * Root schema file
	 *
	 * @var string
	 */
	private $_rootSchemaFile;

	/**
	 * Getting the ID of schema.
	 *
	 * @param string $schemafile Schemafile
	 *
	 * @return string
	 */

	public function getID($schemafile)
	    {
		$this->_rootSchemaFile = $schemafile;

		$this->_mappingArray = array();
		if (isset($_SESSION["SCHEMA_MAPPING"]) === true)
		    {
			$this->_mappingArray = $_SESSION["SCHEMA_MAPPING"];
		    }
		else if (isset($GLOBALS["SCHEMA_MAPPING"]) === true)
		    {
			$this->_mappingArray = $GLOBALS["SCHEMA_MAPPING"];
		    } //end if

		$this->_listOfParticipatingSchemas            = array();
		$this->_listOfParticipatingSchemas["root"]    = array();
		$this->_listOfParticipatingSchemas["include"] = array();
		$this->_listOfParticipatingSchemas["import"]  = array();

		$this->_fillParticipatingSchemasArray($schemafile);
		$this->_listOfParticipatingSchemas["root"][] = array(
								"externalfile"        => $schemafile,
								"externalfilecontent" => false,
								"externalnode"        => false,
							       );

		$md5concatenated = "";
		foreach ($this->_listOfParticipatingSchemas as $types)
		    {
			foreach ($types as $filedescription)
			    {
				if (empty($filedescription["externalfilecontent"]) === false)
				    {
					$md5concatenated .= md5($filedescription["externalfilecontent"]);
				    }
				else
				    {
					if (file_exists($filedescription["externalfile"]) === false)
					    {
						break 2;
					    }

					$md5concatenated .= md5(file_get_contents($filedescription["externalfile"]));
				    } //end if
			    } //end foreach
		    } //end foreach

		if (empty($md5concatenated) === false)
		    {
			return md5($md5concatenated);
		    }
	    } //end getID()


	/**
	 * Returns list of schemas that were processed at the last ID calcualtion.
	 *
	 * @return array
	 */

	public function getSchemasList()
	    {
		return $this->_listOfParticipatingSchemas;
	    } //end getSchemasList()


	/**
	 * Appends externals to schema document.
	 *
	 * @param DOMDocument $schemadocument Core scheam document
	 *
	 * @return DOMDocument
	 *
	 * @untranslatable include
	 * @untranslatable import
	 */

	public function completeSchemaWithExternals(DOMDocument $schemadocument)
	    {
		$this->schemadocument = $schemadocument;

		$schemaslist = $this->_listOfParticipatingSchemas;
		foreach ($schemaslist as $type => $externaldescriptions)
		    {
			if ($type === "include" || $type === "import")
			    {
				$this->_processExternalSchema($type, $externaldescriptions);
			    } //end if
		    } //end foreach

		return $this->schemadocument;
	    } //end completeSchemaWithExternals()


	/**
	 * Processes external schema.
	 *
	 * @param string $type                 External type
	 * @param array  $externaldescriptions External description
	 *
	 * @return void
	 *
	 * @untranslatable utf-8
	 * @untranslatable import
	 * @untranslatable namespace
	 * @untranslatable namespace::*
	 * @untranslatable schema/*
	 */

	private function _processExternalSchema($type, array $externaldescriptions)
	    {
		foreach ($externaldescriptions as $externaldescription)
		    {
			$externaldocument = new SerializableDOMDocument("1.0", "utf-8");
			if (empty($externaldescription["externalfilecontent"]) === false)
			    {
				$externaldocument->loadXML($externaldescription["externalfilecontent"]);
			    }
			else
			    {
				$externaldocument->load($externaldescription["externalfile"]);
			    } //end if

			if ($type === "import")
			    {
				$importnamespace      = $externaldescription["externalnode"]->getAttribute("namespace");
				$context              = $externaldescription["externalnode"]->ownerDocument->documentElement;
				$currentdocumentxpath = new DOMXPathExtended($externaldescription["externalnode"]->ownerDocument);
				foreach ($currentdocumentxpath->query("namespace::*", $context) as $node)
				    {
					if ($node->nodeValue === $importnamespace)
					    {
						$importnamespaceprefix = str_ireplace("xmlns:", "", $node->nodeName);
					    } //end if
				    } //end foreach
			    } //end if

			$externaldocumentxpath = new DOMXPathExtended($externaldocument);
			$prefix                = $this->getSchemaPrefix($externaldocumentxpath);
			$elementstoimport      = $externaldocumentxpath->query("/" . $prefix . "schema/*");
			foreach ($elementstoimport as $elementtoimport)
			    {
				$importednode = $this->schemadocument->importNode($elementtoimport, true);
				if ($type === "import")
				    {
					$this->_setNamespaceForImportedElement($importednode, $importnamespaceprefix);
				    }

				$this->schemadocument->documentElement->appendChild($importednode);
			    } //end foreach
		    } //end foreach
	    } //end _processExternalSchema()


	/**
	 * Appends namespace to imported node "name" attributes.
	 *
	 * @param DOMNode $element               Current node
	 * @param string  $importnamespaceprefix Namespace prefix
	 *
	 * @return void
	 *
	 * @untranslatable name
	 */

	private function _setNamespaceForImportedElement(DOMNode $element, $importnamespaceprefix)
	    {
		if ($element instanceof DOMElement === true)
		    {
			if ($element->getAttribute("name") !== "")
			    {
				$element->setAttribute("name", $importnamespaceprefix . ":" . $element->getAttribute("name"));
			    } //end if
		    } //end if

		if ($element->childNodes !== null)
		    {
			foreach ($element->childNodes as $child)
			    {
				$this->_setNamespaceForImportedElement($child, $importnamespaceprefix);
			    } //end foreach
		    } //end if
	    } //end _setNamespaceForImportedElement()


	/**
	 * Fills array with included or imported schemas.
	 *
	 * @param string $schemafile Currently processed schema
	 *
	 * @return void
	 *
	 * @throws Exception Unable to load schema
	 *
	 * @exceptioncode EXCEPTION_UNABLE_TO_LOAD_SCHEMA
	 *
	 * @untranslatable utf-8
	 * @untranslatable include
	 * @untranslatable import
	 */

	private function _fillParticipatingSchemasArray($schemafile)
	    {
		$currentdocument = new SerializableDOMDocument("1.0", "utf-8");
		set_error_handler(function ($errno, $errstr)
		    {
			unset($errno);
			throw new Exception(_("Unable to load schema, error message:") . " " . $errstr, EXCEPTION_UNABLE_TO_LOAD_SCHEMA);
		    }, E_WARNING);

		$loadresult = $currentdocument->load($schemafile);
		restore_error_handler();

		unset($loadresult);

		$currentdocumentxpath = new DOMXPathExtended($currentdocument);

		$schemaprefix = $this->getSchemaPrefix($currentdocumentxpath);

		$includes = $currentdocumentxpath->query("//" . $schemaprefix . "include");
		foreach ($includes as $include)
		    {
			$fillschemasarray = true;
			$externalsdata    = $this->_getExternalSchema($include, $schemafile);
			foreach ($this->_listOfParticipatingSchemas["include"] as $existingschema)
			    {
				if ($existingschema["externalfile"] === $externalsdata["externalfile"])
				    {
					$fillschemasarray = false;
				    } //end if
			    } //end foreach

			if ($fillschemasarray === true)
			    {
				$this->_listOfParticipatingSchemas["include"][] = $externalsdata;
				$furtherfill = $this->_fillParticipatingSchemasArray($externalsdata["externalfile"]);
			    }
		    } //end foreach

		$imports = $currentdocumentxpath->query("//" . $schemaprefix . "import");
		foreach ($imports as $import)
		    {
			$fillschemasarray = true;
			$externalsdata    = $this->_getExternalSchema($import, $schemafile);
			foreach ($this->_listOfParticipatingSchemas["import"] as $existingschema)
			    {
				if ($existingschema["externalfile"] === $externalsdata["externalfile"])
				    {
					$fillschemasarray = false;
				    } //end if
			    } //end foreach

			if ($fillschemasarray === true)
			    {
				$this->_listOfParticipatingSchemas["import"][] = $externalsdata;
				$furtherfill = $this->_fillParticipatingSchemasArray($externalsdata["externalfile"]);
			    }
		    } //end foreach
	    } //end _fillParticipatingSchemasArray()


	/**
	 * Gets schema file name or content from provided external schema node.
	 *
	 * @param DOMElement $externalnode Element with external defifnition
	 * @param string     $parentschema Parent schema, that referenced current schema
	 *
	 * @return array
	 *
	 * @throws Exception Unable to retrieve external
	 *
	 * @exceptioncode EXCEPTION_UNABLE_TO_RETRIEVE
	 * @exceptioncode EXCEPTION_UNABLE_TO_GET_EXTERNAL
	 *
	 * @untranslatable schemaLocation
	 */

	private function _getExternalSchema(DOMElement $externalnode, $parentschema = false)
	    {
		$externalfile        = false;
		$externalfilecontent = false;
		$schemalocation      = $externalnode->getAttribute("schemaLocation");
		if (isset($this->_mappingArray[dirname($this->_rootSchemaFile) . "/" . $schemalocation]) === true)
		    {
			$externalfile = $this->_mappingArray[dirname($this->_rootSchemaFile) . "/" . $schemalocation];
		    }
		else if (isset($this->_mappingArray[$schemalocation]) === true)
		    {
			$externalfile = $this->_mappingArray[$schemalocation];
		    }
		else if ($parentschema !== false && file_exists(dirname($parentschema) . "/" . $schemalocation) === true)
		    {
			$externalfile = dirname($parentschema) . "/" . $schemalocation;
		    }
		else if (filter_var($schemalocation, FILTER_VALIDATE_URL) !== false)
		    {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $schemalocation);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$externalfile        = $schemalocation;
			$externalfilecontent = curl_exec($ch);
			if (curl_error($ch) !== "")
			    {
				$error = curl_error($ch);
				curl_close($ch);
				throw new Exception(_("Unable to retrieve the") . " " . $schemalocation . ", " . _("reason:") . " " . $error, EXCEPTION_UNABLE_TO_RETRIEVE);
			    } //end if

			curl_close($ch);
		    }
		else
		    {
			throw new Exception(_("Unable to get specified external") . " " . $schemalocation, EXCEPTION_UNABLE_TO_GET_EXTERNAL);
		    } //end if

		return array(
			"externalfile"        => $externalfile,
			"externalfilecontent" => $externalfilecontent,
			"externalnode"        => $externalnode,
		       );
	    } //end _getExternalSchema()


    } //end class

?>
