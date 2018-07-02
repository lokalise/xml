<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\DOMXPathExtended;
use \PHPUnit_Framework_TestCase;

/**
 * Test for DOMXPathExtended class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/DOMXPathExtendedTest.php $
 *
 * @donottranslate
 */

class DOMXPathExtendedTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test object
	 *
	 * @var DOMXPathExtended
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<test><element attribute=\"attr\">value</element><anotherelement></anotherelement><lastelement>element</lastelement></test>";
		$dom  = new DOMDocument();
		$dom->loadXML($xml, LIBXML_PARSEHUGE);
		$this->object = new DOMXPathExtended($dom);
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		unset($this->object);
	    } //end tearDown()


	/**
	 * Testing of multiple namespaces in XML
	 *
	 * @return void
	 */

	public function testAllowsToUseNamespacesInXpathStatements()
	    {
		$this->assertTrue($this->object->exists("/test/element"));

		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<ApplicationResponse ";
		$xml .= "xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2\" ";
		$xml .= "xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" ";
		$xml .= "xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" ";
		$xml .= "xmlns:ccts=\"urn:un:unece:uncefact:documentation:2\" ";
		$xml .= "xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\" ";
		$xml .= "xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">";
		$xml .= "<cbc:UBLVersionID>2.1</cbc:UBLVersionID>";
		$xml .= "</ApplicationResponse>";
		$dom  = new DOMDocument();
		$dom->loadXML($xml, LIBXML_PARSEHUGE);
		$this->object = new DOMXPathExtended($dom);
		$this->assertTrue($this->object->exists("/null:ApplicationResponse"));
		$this->assertTrue($this->object->exists("/null:ApplicationResponse/cbc:UBLVersionID"));
	    } //end testAllowsToUseNamespacesInXpathStatements()


	/**
	 * Testing of exists()
	 *
	 * @return void
	 */

	public function testCanCheckIfElementExistsInXmlDocument()
	    {
		$this->assertTrue($this->object->exists("/test/element[@attribute='attr']"));
		$this->assertFalse($this->object->exists("/test/element[@attribute='non-existent']"));
	    } //end testCanCheckIfElementExistsInXmlDocument()


	/**
	 * Testing of getFirstItemValueFrom()
	 *
	 * @return void
	 */

	public function testCanGetValueOfMatchingElement()
	    {
		$list = $this->object->query("/test/element[@attribute='attr']");
		$this->assertEquals("value", $this->object->getFirstItemValueFrom($list));

		$list = $this->object->query("/test/element[@attribute='non-existent']");
		$this->assertFalse($this->object->getFirstItemValueFrom($list));
	    } //end testCanGetValueOfMatchingElement()


    } //end class

?>
