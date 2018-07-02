<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\SchemaNametraceDescriptor;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaNametraceDescriptor
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaNametraceDescriptorTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class SchemaNametraceDescriptorTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test sets directory
	 *
	 * @var string
	 */
	private $_testsetsDirectory;

	/**
	 * Setting up the environment
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$this->_testsetsDirectory = __DIR__ . "/schematestsets/nametracedescriptortestset/";
	    } //end setUp()


	/**
	 * Testing common cases that arise during usage.
	 *
	 * @return void
	 */

	public function testCommonCases()
	    {
		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$descriptionwithvirtualelement = $descriptor->describe("FirstLevel/choice:fd017d76df6ba848f8c143894ca45e96");
		$this->assertTrue(isset($descriptionwithvirtualelement["children"]["branches"]));

		$description = $descriptor->describe("FirstLevel/choice:fd017d76df6ba848f8c143894ca45e96/ChoiceTwo");
		$this->assertEquals("ChoiceTwo", $description["name"]);
	    } //end testCommonCases()


	/**
	 * Testing renewal of schema ID.
	 *
	 * @return void
	 */

	public function testSchemaIDRenewal()
	    {
		$this->markTestSkipped("Not clear strategy for tracing schema changes");

		$specifictestsetdirectory = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/";

		if (file_exists($specifictestsetdirectory . "temporaryschema.xsd") === true)
		    {
			unlink($specifictestsetdirectory . "temporaryschema.xsd");
		    }

		copy($specifictestsetdirectory . "0.xsd", $specifictestsetdirectory . "temporaryschema.xsd");

		$descriptor = new SchemaNametraceDescriptor($specifictestsetdirectory . "temporaryschema.xsd");
		$initialid  = $descriptor->getSchemaID();
		$this->assertEquals("8d58f4cb88bdc56a4bc6ff0b52b0675c", $initialid);

		unlink($specifictestsetdirectory . "temporaryschema.xsd");
		copy($specifictestsetdirectory . "1.xsd", $specifictestsetdirectory . "temporaryschema.xsd");

		$descriptor->renewSchemaID();
		$changedschemaid = $descriptor->getSchemaID();
		$this->assertEquals("c363ab2e1248cc09b379f3e39fe223e9", $changedschemaid);
	    } //end testSchemaIDRenewal()


	/**
	 * Tesing root manipulations.
	 *
	 * @return void
	 */

	public function testRootSetting()
	    {
		$coreschema = __DIR__ . "/schematestsets/sampleschemas/1.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);
		$descriptor->setPreferredRoot(1);

		$description = $descriptor->describe("ProductTwo/SecondElementLevel");
		$this->assertEquals("SecondElementLevel", $description["name"]);
	    } //end testRootSetting()


	/**
	 * Testing service routines.
	 *
	 * @return void
	 */

	public function testServiceRoutines()
	    {
		$coreschema = __DIR__ . "/schematestsets/sampleschemas/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$description = $descriptor->describe("Product");

		$serializeddescriptor   = serialize($descriptor);
		$unserializeddescriptor = unserialize($serializeddescriptor);

		$latestdescription = $unserializeddescriptor->describe("Product");

		$this->assertEquals($description, $latestdescription);

		$coreschema = __DIR__ . "/schematestsets/sampleschemas/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$schemadescription = $descriptor->getSchemaElement("xs:schema", "", true);
		$this->assertEquals("xs:schema", $schemadescription["type"]);
	    } //end testServiceRoutines()


	/**
	 * Testing fetching of schemas from different folder.
	 *
	 * @return void
	 */

	public function testDifferentFolderSchemaFetch()
	    {
		$rootfoldername = str_replace(__CLASS__ . "::", "", __METHOD__);
		$coreschema     = $this->_testsetsDirectory . $rootfoldername . "/coreschema/0.xsd";

		$corereferencedschema      = $this->_testsetsDirectory . $rootfoldername . "/testschema/corereferencedschema.xsd";
		$GLOBALS["SCHEMA_MAPPING"] = array("urn:schemaDescriptor:corereferencedschema" => $corereferencedschema);

		$descriptor = new SchemaNametraceDescriptor($coreschema);
		unset($descriptor);
	    } //end testDifferentFolderSchemaFetch()


	/**
	 * Testing getSchemaElement() exception.
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Unable to return xs:sequence type according to the policy
	 */

	public function testGetSchemaElementExceptionWrongElement()
	    {
		define("EXCEPTION_UNABLE_TO_RETURN", 1);

		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$descriptor->getSchemaElement("xs:sequence", "sdhvohd7920b8bmf82");
	    } //end testGetSchemaElementExceptionWrongElement()


	/**
	 * Testing discovery mode check exception.
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Wrong discovery value was provided
	 */

	public function testInvalidDiscoveryModeValue()
	    {
		define("EXCEPTION_WRONG_DISCOVERY_MODE", 1);

		$descriptor = new SchemaNametraceDescriptor($this->_testsetsDirectory . "schemas/0.xsd", "INVALID_VALUE");
		unset($descriptor);
	    } //end testInvalidDiscoveryModeValue()


	/**
	 * Testing getSchemaElement() exception.
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage No element with type 'xs:element'
	 */

	public function testGetSchemaElementExceptionNonExistingElement()
	    {
		define("EXCEPTION_NO_ELEMENT_OF_TYPE", 1);

		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$descriptor->getSchemaElement("xs:element", "sdhvohd7920b8bmf82");
	    } //end testGetSchemaElementExceptionNonExistingElement()


	/**
	 * Testing formatting of elements in complex mode.
	 *
	 * @return void
	 */

	public function testDescriptionComplexMode()
	    {
		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema, "complex");

		$descriptionwithvirtualelement = $descriptor->describe("FirstLevel");
		$this->assertEquals("xs:sequence", $descriptionwithvirtualelement["children"][0]["schemakeeperlocation"]["type"]);
	    } //end testDescriptionComplexMode()


    } //end class

?>
