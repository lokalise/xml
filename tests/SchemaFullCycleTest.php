<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Foundation\XML\SchemaExpand;
use \Logics\Foundation\XML\SchemaFlatten;
use \Logics\Tests\InternalWebServer;
use \PHPUnit_Framework_TestCase;

/**
 * SchemaFullCycleTest class for testing full SchemaExpand to SchemaFlatten workflow.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-06-07 03:08:52 +0930 (Tue, 07 Jun 2016) $ $Revision: 1641 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaFullCycleTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class SchemaFullCycleTest extends PHPUnit_Framework_TestCase
    {

	use InternalWebServer;

	/**
	 * Database connection
	 *
	 * @var MySQLdatabase
	 */
	private $_db;

	/**
	 * Defining the location of test cases
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$this->_db = new MySQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);

		define("EXCEPTION_UNABLE_TO_EXPAND", 1);
		define("EXCEPTION_UNUSED_ITEMS", 2);
		define("EXCEPTION_INVALID_DOCUMENT", 3);
	    } //end setUp()


	/**
	 * Testing element locking.
	 *
	 * @return void
	 */

	public function testElementLocking()
	    {
		$this->_fullCycle(9);
		$this->_fullCycle(11);
	    } //end testElementLocking()


	/**
	 * Testing correct flattening mechanism in case of elements with same names and same parent names.
	 *
	 * @return void
	 */

	public function testParentsWithSameNameSeparation()
	    {
		$this->_fullCycle(3);
		$this->_fullCycle(8);
	    } //end testParentsWithSameNameSeparation()


	/**
	 * Testing flattening for nodes with attributes.
	 *
	 * @return void
	 */

	public function testAttributeFlattening()
	    {
		$this->_fullCycle(6);
	    } //end testAttributeFlattening()


	/**
	 * Testing case when one of schema sequence's has choices and elements in a row.
	 *
	 * @return void
	 */

	public function testChoiceDivisionAtSchemaProcessing()
	    {
		$this->_fullCycle(10);
	    } //end testChoiceDivisionAtSchemaProcessing()


	/**
	 * Testing miscellaneous cases.
	 *
	 * @return void
	 */

	public function testMiscellaneousCases()
	    {
		$GLOBALS["SCHEMA_MAPPING"] = array("http://www.w3.org/2001/xml.xsd" => $this->webserverURL() . "/externalschemas/www.w3.org/2001/03/xml.xsd");

		$this->_fullCycle(14, false);
	    } //end testMiscellaneousCases()


	/**
	 * Common testing routine template.
	 *
	 * @param int  $testsetindex    Index of the test set
	 * @param bool $checkoutputdata Specifies if test has to check resulting data array
	 *
	 * @return void
	 */

	private function _fullCycle($testsetindex, $checkoutputdata = true)
	    {
		$testsetfolder = __DIR__ . "/schematestsets/fullcycle/" . $testsetindex . "/";

		$inputdata                      = json_decode(file_get_contents($testsetfolder . "InputData.json"), true);
		$schemafile                     = $testsetfolder . "Schema.xsd";
		$schemaexpand                   = new SchemaExpand($schemafile);
		$expandeddocument               = $schemaexpand->getExpandedDocument($inputdata);
		$expandeddocument->formatOutput = true;

		$expectedexpandeddocument                     = new DOMDocument("1.0", "utf-8");
		$expectedexpandeddocument->preserveWhiteSpace = false;
		$expectedexpandeddocument->load($testsetfolder . "ExpandedDocument.xml");
		$expectedexpandeddocument->formatOutput = true;

		$this->assertEquals($expectedexpandeddocument->saveXML(), $expandeddocument->saveXML());

		$schemaflatten = new SchemaFlatten($schemafile, $this->_db);
		$resultingdata = $schemaflatten->getFlattenedElementsArray($expandeddocument->documentElement);

		if ($checkoutputdata === true)
		    {
			$expecteddata = json_decode(file_get_contents($testsetfolder . "OutputData.json"), true);
			$this->assertEquals($expecteddata, $resultingdata);
		    } //end if
	    } //end _fullCycle()


    } //end class

?>
