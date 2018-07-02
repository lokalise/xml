<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\XMLerrors;
use \PHPUnit_Framework_TestCase;

/**
 * Test for XMLerrors trait
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/XMLerrorsTest.php $
 *
 * @donottranslate
 */

class XMLerrorsTest extends PHPUnit_Framework_TestCase
    {
	use XMLerrors;

	/**
	 * Sets up the fixture: we need to get libxml into original state
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		libxml_use_internal_errors(false);
	    } //end setUp()


	/**
	 * Tears down the fixture: clear all errors which may remain in libxml
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		libxml_clear_errors();
	    } //end tearDown()


	/**
	 * Testing that with preparation XML errors will be supressed
	 *
	 * @return void
	 */

	public function testShouldInterceptAllXmlErrorsAndWarnings()
	    {
		$this->prepareForXMLerrors();

		$this->_dom = new DOMDocument();
		$this->_dom->loadXML("not an XML document", LIBXML_PARSEHUGE);
	    } //end testShouldInterceptAllXmlErrorsAndWarnings()


	/**
	 * Test error reporting for different severity levels
	 *
	 * @return void
	 */

	public function testXmlErrorsAndWarningsCanBeRetrieved()
	    {
		$this->prepareForXMLerrors();

		$this->assertEquals(false, $this->getXMLerrors());

		$this->_dom = new DOMDocument();
		$this->_dom->loadXML("not an XML document", LIBXML_PARSEHUGE);

		$errors = $this->getXMLerrors();
		$this->assertStringStartsWith("Fatal Error", $errors);

		$this->clearXMLerrors();

		$this->prepareForXMLerrors();

		$this->_dom = new DOMDocument();
		$this->_dom->loadHTML("<h4><b>Links</h4></b>");

		$errors = $this->getXMLerrors();
		$this->assertStringStartsWith("Error", $errors);

		$this->clearXMLerrors();

		$this->prepareForXMLerrors();

		$this->_dom = new DOMDocument();
		$this->_dom->loadXML("<?xml version=\"1.02\" encoding=\"UTF-8\"?><test/>", LIBXML_PARSEHUGE);

		$errors = $this->getXMLerrors();
		$this->assertStringStartsWith("Warning", $errors);

		$this->clearXMLerrors();

		$errors = $this->getXMLerrors();
		$this->assertEquals(false, $errors);
	    } //end testXmlErrorsAndWarningsCanBeRetrieved()


	/**
	 * Test clearing of XML errors
	 *
	 * @return void
	 */

	public function testXmlErrorsAndWarningsCanBeCleared()
	    {
		$this->prepareForXMLerrors();

		$this->_dom = new DOMDocument();
		$this->_dom->loadXML("not an XML document", LIBXML_PARSEHUGE);

		$this->assertEquals($this->getXMLerrors(), $this->getXMLerrors());

		$this->clearXMLerrors();

		$this->assertFalse($this->getXMLerrors());
	    } //end testXmlErrorsAndWarningsCanBeCleared()


	/**
	 * Testing that without preparation XML errors translate to PHP warning
	 *
	 * @expectedException PHPUnit_Framework_Error_Warning
	 *
	 * @return void
	 */

	public function testIfXmlErrorsAndWarningsAreNotInterceptedThenTheyReportedNormallyAsPhpErrors()
	    {
		$this->_dom = new DOMDocument();
		$this->_dom->loadXML("not an XML document", LIBXML_PARSEHUGE);
	    } //end testIfXmlErrorsAndWarningsAreNotInterceptedThenTheyReportedNormallyAsPhpErrors()


    } //end class

?>
