<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 *
 * @untranslatable xdebug.max_nesting_level
 */

namespace Logics\Foundation\XML;

ini_set("xdebug.max_nesting_level", 200);

/**
 * SchemaProcessor class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaProcessor.php $
 */

class SchemaProcessor
    {

	/**
	 * Name of base schema file
	 *
	 * @var string
	 */
	protected $coreSchemaFile;

	/**
	 * Schema in DOM form
	 *
	 * @var DOMDocument
	 */
	protected $schemaDocument;

	/**
	 * Schema represented in text form
	 *
	 * @var string
	 */
	protected $schemaDocumentAsString;

	/**
	 * XPath object for schema
	 *
	 * @var DOMXPathExtended
	 */
	protected $schemaDocumentXPath;

	/**
	 * Place to keep compiled schema
	 *
	 * @var array
	 */
	protected $schemaKeeper = array();

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
	 * Mapping array
	 *
	 * @var array
	 */
	protected $mappingArray = array();

	/**
	 * Schema ID
	 *
	 * @var string
	 */
	protected $schemaID;

	/**
	 * List of all schema files used
	 *
	 * @var array
	 */
	protected $listOfParticipatingSchemaFiles = array();

	use XMLerrors, SchemaNametrace, DOMDocumentTools;

	/**
	 * Accepts the schema file path, loads it and extracts required data from schema.
	 *
	 * @param string $schemafile File path of the schema
	 *
	 * @return void
	 *
	 * @untranslatable utf-8
	 */

	public function __construct($schemafile)
	    {
		$this->coreSchemaFile = $schemafile;

		$this->schemaDocument = new SerializableDOMDocument("1.0", "utf-8");
		$this->schemaDocument->load($schemafile);

		$this->listOfParticipatingSchemaFiles[] = $this->schemaDocument->documentURI;

		$this->schemaDocumentXPath = new DOMXPathExtended($this->schemaDocument);
		$this->schemaPrefix        = $this->getSchemaPrefix($this->schemaDocumentXPath);

		$this->_registerSchemaMappings();
		if (isset($GLOBALS["SCHEMA_MAPPING"]) === true)
		    {
			$this->mappingArray = $GLOBALS["SCHEMA_MAPPING"];
		    }

		$schemaexternals = new SchemaExternals();
		$this->schemaID  = $schemaexternals->getID($this->coreSchemaFile);

		$this->schemaDocument      = $schemaexternals->completeSchemaWithExternals($this->schemaDocument);
		$this->schemaDocumentXPath = new DOMXPathExtended($this->schemaDocument);

		$this->validateSchema($schemafile);
		$this->_setSchemaKeeper();
		$this->_processSchema();
	    } //end __construct()


	/**
	 * Invokes current instance from session
	 *
	 * @return SchemaProcessor
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
	 * @untranslatable coreSchemaFile
	 * @untranslatable schemaDocument
	 * @untranslatable schemaKeeper
	 * @untranslatable schemaPrefix
	 * @untranslatable baseTypes
	 * @untranslatable mappingArray
	 * @untranslatable schemaID
	 * @untranslatable listOfParticipatingSchemaFiles
	 */

	public function __sleep()
	    {
		return array(
			"coreSchemaFile",
			"schemaDocument",
			"schemaKeeper",
			"schemaPrefix",
			"baseTypes",
			"mappingArray",
			"schemaID",
			"listOfParticipatingSchemaFiles",
		       );
	    } //end __sleep()


	/**
	 * Processes schema and puts elements in corresponding subarrays of main SchemaKeeper array.
	 *
	 * @return void
	 */

	private function _processSchema()
	    {
		if (isset($_SESSION) === true && isset($_SESSION["compiledschemas"][$this->schemaID]) === true)
		    {
			$this->schemaKeeper = $_SESSION["compiledschemas"][$this->schemaID];
		    }
		else
		    {
			$schemaiterator     = new SchemaProcessorIterator();
			$this->schemaKeeper = $schemaiterator->iterate($this->schemaDocumentXPath, $this->baseTypes, $this->schemaKeeper);

			$_SESSION["compiledschemas"][$this->schemaID] = $this->schemaKeeper;
		    } //end if
	    } //end _processSchema()


	/**
	 * Filling data types according to the default schema prefix
	 *
	 * @return void
	 *
	 * @untranslatable boolean
	 * @untranslatable base64Binary
	 * @untranslatable hexBinary
	 * @untranslatable anyURI
	 * @untranslatable language
	 * @untranslatable normalizedString
	 * @untranslatable string
	 * @untranslatable token
	 * @untranslatable byte
	 * @untranslatable decimal
	 * @untranslatable double
	 * @untranslatable float
	 * @untranslatable int
	 * @untranslatable integer
	 * @untranslatable long
	 * @untranslatable negativeInteger
	 * @untranslatable nonNegativeInteger
	 * @untranslatable nonPositiveInteger
	 * @untranslatable positiveInteger
	 * @untranslatable short
	 * @untranslatable unsignedByte
	 * @untranslatable unsignedInt
	 * @untranslatable unsignedLong
	 * @untranslatable unsignedShort
	 * @untranslatable date
	 * @untranslatable dateTime
	 * @untranslatable duration
	 * @untranslatable gDay
	 * @untranslatable gMonth
	 * @untranslatable gMonthDay
	 * @untranslatable gYear
	 * @untranslatable gYearMonth
	 * @untranslatable time
	 */

	private function _setSchemaKeeper()
	    {
		$dataTypes = array(
			      "boolean", "base64Binary", "hexBinary",
			      "anyURI", "language", "normalizedString", "string", "token",
			      "byte", "decimal", "double", "float", "int", "integer", "long", "negativeInteger",
			      "nonNegativeInteger", "nonPositiveInteger", "positiveInteger", "short", "unsignedByte", "unsignedInt", "unsignedLong ", "unsignedShort",
			      "date", "dateTime", "duration", "gDay", "gMonth", "gMonthDay", "gYear", "gYearMonth", "time",
			     );

		foreach ($dataTypes as $value)
		    {
			$this->schemaKeeper["basetype"][$this->schemaPrefix . $value] = array("domnode" => null);

			$this->baseTypes[] = $this->schemaPrefix . $value;
		    } //end foreach
	    } //end _setSchemaKeeper()


	/**
	 * Returns item from the schemaKeeper array.
	 *
	 * @param array  $parentelement Parent element
	 * @param string $nametracepart Searched nametrace
	 *
	 * @return array
	 */

	protected function getItem(array $parentelement, $nametracepart)
	    {
		$result  = null;
		$matches = array();
		if ((preg_match("/^[\w:]+\[\d+\]$/", $nametracepart) === 1) && (preg_match("/\d+/", $nametracepart, $matches) === 1))
		    {
			preg_match("/[\w:]+/", $nametracepart, $noindexmatches);
			$counter    = 1;
			$indexvalue = intval($matches[0]);
			foreach ($parentelement["children"] as $child)
			    {
				if ($child["type"] === $noindexmatches[0])
				    {
					if ($counter === $indexvalue)
					    {
						$result = $this->schemaKeeper[$child["type"]][$child["id"]];
						break;
					    } //end if

					$counter++;
				    } //end if
			    } //end foreach
		    }
		else if (preg_match("/^[\w:]+\[[\w:'=@]+\]$/", $nametracepart) === 1)
		    {
			$parentnodes                 = $this->schemaDocumentXPath->query($parentelement["xpath"]);
			$parentnode                  = $parentnodes->item(0);
			$suitablechildrenelements    = $this->schemaDocumentXPath->query("./" . $nametracepart, $parentnode);
			$suitablechildrenelement     = $suitablechildrenelements->item(0);
			$suitablechildrenelementtype = $suitablechildrenelement->nodeName;
			$suitablechildrenelementid   = str_replace($this->schemaPrefix, "", $suitablechildrenelementtype) . md5($suitablechildrenelement->getNodePath());

			$result = $this->schemaKeeper[$suitablechildrenelementtype][$suitablechildrenelementid];
		    } //end if

		return $result;
	    } //end getItem()


	/**
	 * Returns specific ID for current schema revision (including imported and included schemas).
	 *
	 * @return string
	 */

	public function getSchemaID()
	    {
		return $this->schemaID;
	    } //end getSchemaID()


    } //end class

?>
