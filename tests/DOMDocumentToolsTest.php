<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\DOMDocumentTools;
use \Logics\Foundation\XML\DOMXPathExtended;
use \Logics\Foundation\XML\XMLerrors;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing DOMDocumentTools trait
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/DOMDocumentToolsTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class DOMDocumentToolsTest extends PHPUnit_Framework_TestCase
    {

	use DOMDocumentTools, XMLerrors;

	/**
	 * Testing DOMElement children fetch.
	 *
	 * @return void
	 */

	public function testChildNodesFetch()
	    {
		$document = new DOMDocument("1.0", "utf-8");
		$document->loadXML("<xs:schema xmlns:xs=\"http://www.w3.org/2001/XMLSchema\"><xs:element name=\"Test\">" .
		"<xs:complexType><xs:sequence><xs:element name=\"InnerTest\" type=\"xs:string\"/></xs:sequence></xs:complexType></xs:element></xs:schema>");
		$documentxpath = new DOMXPathExtended($document);

		$elements = $documentxpath->query("/xs:schema/xs:element");
		$element  = $elements->item(0);
		$result   = $this->getFirstLevelChildNodes($element);
		$this->assertEquals(1, count($result));

		$elements = $documentxpath->query("/xs:schema/xs:element/xs:complexType/xs:sequence/xs:element");
		$element  = $elements->item(0);
		$result   = $this->getFirstLevelChildNodes($element);
		$this->assertEquals(0, count($result));
	    } //end testChildNodesFetch()


	/**
	 * Testing any element children fetch.
	 *
	 * @return void
	 *
	 * @expectedException        PHPUnit_Framework_Error
	 * @expectedExceptionMessage must be an instance of DOMNode, string given
	 */

	public function testChildNodesAnyTypeFetch()
	    {
		$document = new DOMDocument("1.0", "utf-8");
		$document->loadXML("<xs:schema xmlns:xs=\"http://www.w3.org/2001/XMLSchema\"><xs:element name=\"Test\"><!-- Testing comment -->" .
		"<xs:complexType><xs:sequence><xs:element name=\"InnerTest\" type=\"xs:string\"><xs:annotation><xs:documentation>Test</xs:documentation>" .
		"<xs:appinfo/></xs:annotation></xs:element></xs:sequence></xs:complexType></xs:element></xs:schema>");
		$documentxpath = new DOMXPathExtended($document);

		$elements = $documentxpath->query("/xs:schema/xs:element");
		$element  = $elements->item(0);
		$result   = $this->getFirstLevelChildNodesAnyType($element);
		$this->assertEquals(2, count($result));

		$elements = $documentxpath->query("/xs:schema/xs:element/xs:complexType/xs:sequence/xs:element");
		$element  = $elements->item(0);
		$result   = $this->getFirstLevelChildNodesAnyType($element);
		$this->assertEquals(1, count($result));

		$elements = $documentxpath->query("/xs:schema/xs:element/xs:complexType/xs:sequence/xs:element/xs:annotation/xs:appinfo");
		$element  = $elements->item(0);
		$result   = $this->getFirstLevelChildNodesAnyType($element);
		$this->assertEquals(0, count($result));

		$this->getFirstLevelChildNodesAnyType("");
	    } //end testChildNodesAnyTypeFetch()


	/**
	 * Testing prefix fetch.
	 *
	 * @return void
	 */

	public function testSchemaPrefixFetch()
	    {
		$document = new DOMDocument("1.0", "utf-8");
		$document->loadXML("<xs:schema xmlns:xs=\"http://www.w3.org/2001/XMLSchema\"><xs:element name=\"Test\">" .
		"<xs:complexType><xs:sequence><xs:element name=\"InnerTest\" type=\"xs:string\"/></xs:sequence></xs:complexType></xs:element></xs:schema>");
		$documentxpath = new DOMXPathExtended($document);

		$prefix = $this->getSchemaPrefix($documentxpath);
		$this->assertEquals("xs:", $prefix);
	    } //end testSchemaPrefixFetch()


	/**
	 * Testing input data filtering.
	 *
	 * @return void
	 *
	 * @expectedException        PHPUnit_Framework_Error
	 * @expectedExceptionMessage must be an instance of DOMNode, string given
	 */

	public function testWrongInputDataException()
	    {
		$this->getFirstLevelChildNodes("WRONG_INPUT");
	    } //end testWrongInputDataException()


	/**
	 * Validates provided schema.
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid schema was provided, encountered errors: Error 3004
	 */

	public function testValidatesSchemas()
	    {
		define("EXCEPTION_INVALID_SCHEMA", 1);

		$schema   = "<xs:schema xmlns:xs=\"http://www.w3.org/2001/XMLSchema\"><xs:element name=\"Test\">" .
		"<xs:complexType><xs:sequence><xs:element name=\"InnerTest\" type=\"NonExistingType\"/></xs:sequence></xs:complexType></xs:element></xs:schema>";
		$savepath = sys_get_temp_dir() . "/invalidschema.xsd";

		file_put_contents($savepath, $schema);
		$this->validateSchema($savepath);
	    } //end testValidatesSchemas()


    } //end class

?>
