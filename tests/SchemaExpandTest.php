<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\SchemaExpand;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaExpand
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaExpandTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class SchemaExpandTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Defining the location of test cases
	 *
	 * @return void
	 */

	public function setUp()
	    {
		define("EXCEPTION_UNABLE_TO_EXPAND", 1);
		define("EXCEPTION_UNUSED_ITEMS", 2);
		define("EXCEPTION_INVALID_DOCUMENT", 3);
		define("EXCEPTION_ROOT_INDEX_NOT_VALID", 4);
	    } //end setUp()


	/**
	 * Generating path to specific Expand documents
	 *
	 * @param string $testindex Number of the test
	 *
	 * @return array
	 */

	protected function getExpandTestDocuments($testindex)
	    {
		$basedir = __DIR__ . DIRECTORY_SEPARATOR . "schemaexpand" . DIRECTORY_SEPARATOR . $testindex . DIRECTORY_SEPARATOR;

		$schema           = $basedir . "Schema.xsd";
		$expandeddocument = $basedir . "ExpandedDocument.xml";
		$inputelements    = $basedir . "InputElements.json";

		if (file_exists($expandeddocument) === false)
		    {
			$expandeddocument = false;
		    }

		if (file_exists($inputelements) === false)
		    {
			$inputelements = false;
		    }

		return array(
			$schema,
			$inputelements,
			$expandeddocument,
		       );
	    } //end getExpandTestDocuments()


	/**
	 * Testing specific testset for equality of output and validity against schema
	 *
	 * @param string $testindex Index of the testset
	 * @param int    $rootindex Index of the chosen root element
	 *
	 * @return array
	 *
	 * @dataProvider validTestsProvider
	 */

	public function testForValidity($testindex, $rootindex = false)
	    {
		list($schemafile, $inputelementsfile, $expandeddocumentfile) = $this->getExpandTestDocuments($testindex);

		$expander = new SchemaExpand($schemafile);

		if ($rootindex === false)
		    {
			$expandeddocument = $expander->getExpandedDocument(json_decode(file_get_contents($inputelementsfile), true));
		    }
		else
		    {
			$expandeddocument = $expander->getExpandedDocument(json_decode(file_get_contents($inputelementsfile), true), $rootindex);
		    }

		$this->assertXmlStringEqualsXmlFile($expandeddocumentfile, $expandeddocument->saveXML());
		$this->assertTrue($expandeddocument->schemaValidate($schemafile));

		return $expander;
	    } //end testForValidity()


	/**
	 * Data provider for testForValidity
	 *
	 * @return array
	 */

	public function validTestsProvider()
	    {
		return array(
			"generating expanded document and validating it"                                              => array("valid-01"),
			"adding one hierarchy node"                                                                   => array("valid-02"),
			"adding three hierarchy nodes"                                                                => array("valid-03"),
			"moving hierarchy node around"                                                                => array("valid-04"),
			"testing absence of mandatory attribute of one of the hierarchy nodes"                        => array("valid-05"),
			"changing placement of one of the mandatory leaves (for example, in different choice branch)" => array("valid-06"),
			"one mandatory leaf element is removed from schema"                                           => array("valid-07"),
			"three mandatory leaves are deleted from schema"                                              => array("valid-08"),
			"last-level choice is first selected"                                                         => array("valid-09"),
			"last-level choice is second selected"                                                        => array("valid-10"),
			"three choices in a row are wrong"                                                            => array("valid-11"),
			"processing non-DOMElement nodes (for example, DOMComment)"                                   => array("valid-12"),
			"two nodes with the same name have attributes with the same name"                             => array("valid-13"),
			"elements with the same name have similar nametrace"                                          => array("valid-14"),
			"elements with the same name have similar nametrace, also the choice change occurs"           => array("valid-15"),
			"one or more elements in inputelements has absolute addressing"                               => array("valid-16"),
			"all elements in inputelements have absolute addressing"                                      => array("valid-17"),
			"schema has multiple root elements"                                                           => array(
															  "valid-18",
															  1,
															 ),
			"schema has attribute group for one of nodes"                                                 => array(
															  "valid-19",
															  0,
															 ),
			"inserting two hierarchy levels between two elements"                                         => array("valid-20"),
			"root element attributes are processed as regular element attributes"                         => array(
															  "valid-21",
															  0,
															 ),
			"choice adjacent to another element which contains another choice"                            => array("valid-22"),
		       );
	    } //end validTestsProvider()


	/**
	 * Testing specific testset for expected failure
	 *
	 * @param string $testindex Index of the testset
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 1
	 *
	 * @dataProvider invalidTestsProvider
	 */

	public function testForInvalidity($testindex)
	    {
		list($schemafile, $inputelementsfile) = $this->getExpandTestDocuments($testindex);

		$schema = new SchemaExpand($schemafile);
		$schema->getExpandedDocument(json_decode(file_get_contents($inputelementsfile), true));
	    } //end testForInvalidity()


	/**
	 * Data provider for testForInvalidity
	 *
	 * @return array
	 */

	public function invalidTestsProvider()
	    {
		return array(
			"testing with added mandatory attribute of one of the hierarchy nodes" => array("invalid-1"),
			"one of the non-mandatory attributes is wrong"                         => array("invalid-2"),
			"one new mandatory leaf element is added to the schema"                => array("invalid-3"),
			"non-mandatory attribute of the leaf node is wrong"                    => array("invalid-4"),
			"mandatory attribute of the leaf node has wrong value"                 => array("invalid-5"),
			"mandatory attribute of the leaf node has wrong name"                  => array("invalid-6"),
		       );
	    } //end invalidTestsProvider()


	/**
	 * Test case: provided root index is not an integer.
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 4
	 */

	public function testWrongRootIndex()
	    {
		$this->testForValidity("wrongroot", "NOT_INT");
	    } //end testWrongRootIndex()


	/**
	 * Test SchemaExpand's getSchemaID()
	 *
	 * @return void
	 */

	public function testSchemaID()
	    {
		$instance = $this->testForValidity("schemaid");
		$this->assertEquals("d5bd8e853c0bed18f79a452dce98b822", $instance->getSchemaID());
	    } //end testSchemaID()


	/**
	 * Test case: no resulting document is returned, invalid input array.
	 *
	 * @return void
	 *
	 * @expectedException     Exception
	 * @expectedExceptionCode 1
	 */

	public function testNoResultingDocument()
	    {
		$instance = $this->testForValidity("nodocument");
		$instance->getExpandedDocument(array(), 0);
	    } //end testNoResultingDocument()


    } //end class

?>
