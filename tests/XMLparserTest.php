<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Foundation\XML\XMLparser;
use \PHPUnit_Framework_TestCase;

/**
 * Test for XMLparser trait
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-06-01 00:20:46 +0930 (Wed, 01 Jun 2016) $ $Revision: 1627 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/XMLparserTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class XMLparserTest extends PHPUnit_Framework_TestCase
    {
	use XMLparser;

	/**
	 * Database object
	 *
	 * @var mixed
	 */
	private $_db;

	/**
	 * Preserved include path
	 *
	 * @var string
	 */
	private $_includePath;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$this->_includePath = ini_get("include_path");
		ini_set("include_path", $this->_includePath . ":" . __DIR__);

		$this->_db = new MySQLdatabase(false, false, false, false);
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		ini_set("include_path", $this->_includePath);
	    } //end tearDown()


	/**
	 * Testing validateDocument() with bad XML
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Start tag expected, '<' not found
	 *
	 * @return void
	 */

	public function testValidateDocumentBadXML()
	    {
		define("EXCEPTION_LOAD_XML_FAILED", 1);

		$schemas = array();
		$xml     = "Not an XML document";
		$this->validateDocument($xml, $schemas);
	    } //end testValidateDocumentBadXML()


	/**
	 * Testing validateDocument() with unknown document type
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Unknown document type
	 *
	 * @return void
	 */

	public function testValidateDocumentUnknownType()
	    {
		define("EXCEPTION_UNKNOWN_DOCUMENT_TYPE", 1);

		$schemas = array();
		$xml     = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml    .= "<test><element attribute=\"attr\">value</element><anotherelement></anotherelement><lastelement>element</lastelement></test>";
		$this->validateDocument($xml, $schemas);
	    } //end testValidateDocumentUnknownType()


	/**
	 * Testing validateDocument() with document not fitting schema
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid document
	 *
	 * @return void
	 */

	public function testValidateDocumentInvalidDocument()
	    {
		define("EXCEPTION_INVALID_DOCUMENT", 1);

		$schemas = array("test" => __DIR__ . "/XMLparserTestSchema.xsd");
		$xml     = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml    .= "<test><element attribute=\"attr\">value</element><anotherelement></anotherelement><lastelement>element</lastelement></test>";
		$this->validateDocument($xml, $schemas);
	    } //end testValidateDocumentInvalidDocument()


	/**
	 * Testing validateDocument() with valid document
	 *
	 * @return void
	 */

	public function testValidateDocument()
	    {
		$schemas = array("test" => __DIR__ . "/XMLparserTestSchema.xsd");
		$xml     = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml    .= "<test><mandatory>text</mandatory></test>";
		$type    = $this->validateDocument($xml, $schemas);
		$this->assertEquals("test", $type);
	    } //end testValidateDocument()


	/**
	 * Testing processDocument() with document without translation
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage No translation for document type /notest
	 *
	 * @return void
	 */

	public function testProcessDocumentWithoutTranslation()
	    {
		define("EXCEPTION_NO_TRANSLATION_FOR_DOCUMENT_TYPE", 1);

		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<notest><mandatory>text</mandatory></notest>";
		$xlat = array(
			 "/test" => array(
				     "class" => __NAMESPACE__ . "\TestXMLparser",
				     "xlat"  => "xlatTest",
				    ),
			);

		$this->processDocument($xml, $xlat);
	    } //end testProcessDocumentWithoutTranslation()


	/**
	 * Testing processDocument() with unknown path
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Unknown path /test/unknown with attribute(s) attribute
	 *
	 * @return void
	 */

	public function testProcessDocumentUnknownPath()
	    {
		define("EXCEPTION_UNKNOWN_PATH", 1);

		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<test><unknown attribute=\"test\">text</unknown></test>";
		$xlat = array(
			 "/test" => array(
				     "class" => __NAMESPACE__ . "\TestXMLparser",
				     "xlat"  => "xlatTest",
				    ),
			);

		$this->processDocument($xml, $xlat);
	    } //end testProcessDocumentUnknownPath()


	/**
	 * Testing processDocument() with absent translation method
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage No translation method noMethod defined in Logics\Tests\Foundation\XML\TestXMLparser
	 *
	 * @return void
	 */

	public function testProcessDocumentNoMethod()
	    {
		define("EXCEPTION_NO_TRANSLATION_METHOD", 1);

		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<test><nomethod>text</nomethod></test>";
		$xlat = array(
			 "/test" => array(
				     "class" => __NAMESPACE__ . "\TestXMLparser",
				     "xlat"  => "xlatTest",
				    ),
			);

		$this->processDocument($xml, $xlat);
	    } //end testProcessDocumentNoMethod()


	/**
	 * Testing processDocument()
	 *
	 * @return void
	 */

	public function testProcessDocument()
	    {
		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<test code=\"200\"><mandatory>text</mandatory><mandatory>text</mandatory></test>";
		$xlat = array(
			 "/test" => array(
				     "class" => __NAMESPACE__ . "\TestXMLparser",
				     "xlat"  => "xlatTest",
				    ),
			);

		$class = $this->processDocument($xml, $xlat);

		$this->assertEquals("texttext", $class->rootelement);
		$this->assertEquals("200", $class->code);
		$this->assertEquals("text", $class->mandatory);
	    } //end testProcessDocument()


	/**
	 * Testing processDocument() with 100MB document. We expect to deal with such large document in under 5 seconds.
	 *
	 * @return void
	 */

	public function testProcessDocumentPerformance()
	    {
		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<test><mandatory>" . str_repeat("A", (100 * 1024 * 1024)) . "</mandatory></test>";
		$xlat = array(
			 "/test" => array(
				     "class" => __NAMESPACE__ . "\TestXMLparser",
				     "xlat"  => "xlatTest",
				    ),
			);

		$commence = microtime(true);

		$this->processDocument($xml, $xlat);

		$complete = microtime(true);
		$exectime = ($complete - $commence);

		$this->assertTrue($exectime < 60, "Execution time is excessive: " . $exectime . " seconds");
	    } //end testProcessDocumentPerformance()


    } //end class

?>
