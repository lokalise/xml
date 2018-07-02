<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\SchemaNametraceDescriptor;
use \Logics\Foundation\XML\SchemaProcessor;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaProcessor
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaProcessorTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class SchemaProcessorTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing __wakeup and __sleep execution.
	 *
	 * @return void
	 */

	public function testWakeupSleepRoutines()
	    {
		$schemaprocessor             = new SchemaProcessor(__DIR__ . "/schematestsets/sampleschemas/0.xsd");
		$schemaid                    = $schemaprocessor->getSchemaID();
		$serializedschemaprocessor   = serialize($schemaprocessor);
		$unserializedschemaprocessor = unserialize($serializedschemaprocessor);
		$this->assertEquals($schemaid, $unserializedschemaprocessor->getSchemaID());
	    } //end testWakeupSleepRoutines()


	/**
	 * Testing when invalid schema is provided.
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid schema was provided
	 */

	public function testInvalidProvidedSchema()
	    {
		define("EXCEPTION_INVALID_SCHEMA", 1);

		$descriptor = new SchemaNametraceDescriptor(__DIR__ . "/schematestsets/processortestset/testInvalidProvidedSchema/0.xsd");
		unset($descriptor);
	    } //end testInvalidProvidedSchema()


	/**
	 * Testing inclusion and importing of external schema documents.
	 *
	 * @return void
	 */

	public function testIncludeAndImport()
	    {
		$pathwalker   = new SchemaNametraceDescriptor(__DIR__ . "/schematestsets/processortestset/testIncludeAndImport/0.xsd");
		$rootelements = $pathwalker->describe();
		$this->assertEquals($rootelements[0]["children"][0]["name"], "IncludedElement");
		$this->assertEquals($rootelements[0]["children"][1]["name"], "ImportedElement");
		$this->assertEquals($rootelements[0]["children"][2]["name"], "InitialSchemaElement");

		$element = $pathwalker->describe("FirstLevel/ImportedElement/ext:SecondImportedElement");
		$this->assertEquals($element["name"], "ext:SecondImportedElement");
		$this->assertEmpty($element["children"]);
	    } //end testIncludeAndImport()


	/**
	 * Testing multi-level inclusion and importing of external schema documents.
	 *
	 * @return void
	 */

	public function testMultiLevelIncludeAndImport()
	    {
		$pathwalker = new SchemaNametraceDescriptor(__DIR__ . DIRECTORY_SEPARATOR . "schematestsets/processortestset/testMultiLevelIncludeAndImport/0.xsd");

		$includedleaf = $pathwalker->describe("BaseLevel/IncludedElement/SecondLevelElement");
		$this->assertEquals("hierarchyelement", $includedleaf["elementtype"]);
		$this->assertEquals(1, count($includedleaf["children"]));

		$includedleaf = $pathwalker->describe("BaseLevel/IncludedElement/SecondLevelElement/SecondLevelLastElement");
		$this->assertEquals("leafelement", $includedleaf["elementtype"]);
		$this->assertEquals("xs:boolean", $includedleaf["nodeattributes"]["type"]);

		$importedleaf = $pathwalker->describe("BaseLevel/ImportedElement/ext:SecondImportedElement");
		$this->assertEquals("hierarchyelement", $importedleaf["elementtype"]);
		$this->assertEquals(1, count($importedleaf["children"]));

		$importedleaf = $pathwalker->describe("BaseLevel/ImportedElement/ext:SecondImportedElement/deeper:SecondImportedElementLastNode");
		$this->assertEquals("leafelement", $importedleaf["elementtype"]);
		$this->assertEquals("xs:boolean", $importedleaf["nodeattributes"]["type"]);
	    } //end testMultiLevelIncludeAndImport()


	/**
	 * Testing processing of complex schemas.
	 *
	 * @return void
	 */

	public function testProcessesRealLifeSchemas()
	    {
		$descriptor = new SchemaNametraceDescriptor(__DIR__ . DIRECTORY_SEPARATOR . "schematestsets/processortestset/testProcessesRealLifeSchemas/ebaySvc.xsd");

		$description = $descriptor->describe("ItemsCanceledEvent");
		$this->assertEquals(18, count($description["children"]));
	    } //end testProcessesRealLifeSchemas()


    } //end class

?>
