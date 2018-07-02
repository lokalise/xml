<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\DOMrunner;
use \PHPUnit_Framework_TestCase;

/**
 * Test for DOMrunner class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/DOMrunnerTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class DOMrunnerTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test object
	 *
	 * @var DOMRunner
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
		$xml          = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml         .= "<test><element attribute=\"attr\">value</element><anotherelement></anotherelement><lastelement>element</lastelement></test>";
		$this->object = new DOMrunner($xml);
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
	 * Test next()
	 *
	 * @return void
	 */

	public function testSequentiallyIteratesThroughElementsOfXmlDocument()
	    {
		$this->assertEquals("/test", $this->object->next());
		$this->assertEquals("/test/element", $this->object->next());
		$this->assertEquals("/test/anotherelement", $this->object->next());
		$this->assertEquals("/test/lastelement", $this->object->next());
		$this->assertEquals(false, $this->object->next());
	    } //end testSequentiallyIteratesThroughElementsOfXmlDocument()


	/**
	 * Test leaf()
	 *
	 * @return void
	 */

	public function testCanReturnCurrentXmlLeaf()
	    {
		$this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
				    "<test><element attribute=\"attr\">value</element><anotherelement/><lastelement>element</lastelement></test>\n", $this->object->leaf());
		$this->object->next();
		$this->assertEquals("<test><element attribute=\"attr\">value</element><anotherelement/><lastelement>element</lastelement></test>", $this->object->leaf());
		$this->object->next();
		$this->assertEquals("<element attribute=\"attr\">value</element>", $this->object->leaf());
		$this->object->next();
		$this->assertEquals("<anotherelement/>", $this->object->leaf());
		$this->object->next();
		$this->assertEquals("<lastelement>element</lastelement>", $this->object->leaf());
		$this->object->next();
		$this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
				    "<test><element attribute=\"attr\">value</element><anotherelement/><lastelement>element</lastelement></test>\n", $this->object->leaf());
	    } //end testCanReturnCurrentXmlLeaf()


	/**
	 * Test value()
	 *
	 * @return void
	 */

	public function testCanGetCurrentElementValue()
	    {
		$this->assertEquals("valueelement", $this->object->value());
		$this->object->next();
		$this->assertEquals("valueelement", $this->object->value());
		$this->object->next();
		$this->assertEquals("value", $this->object->value());
		$this->object->next();
		$this->assertEquals("", $this->object->value());
		$this->object->next();
		$this->assertEquals("element", $this->object->value());
		$this->object->next();
		$this->assertEquals("valueelement", $this->object->value());
	    } //end testCanGetCurrentElementValue()


	/**
	 * Test attributes()
	 *
	 * @return void
	 */

	public function testCanGetCurrentElementAttributes()
	    {
		$this->assertEquals(array(), $this->object->attributes());
		$this->object->next();
		$this->assertEquals(array(), $this->object->attributes());
		$this->object->next();
		$this->assertEquals(array("attribute" => "attr"), $this->object->attributes());
		$this->object->next();
		$this->assertEquals(array(), $this->object->attributes());
		$this->object->next();
		$this->assertEquals(array(), $this->object->attributes());
	    } //end testCanGetCurrentElementAttributes()


	/**
	 * Test __construct()
	 *
	 * @expectedException Exception
	 *
	 * @return void
	 */

	public function testThrowsAnExceptionIfMalformedXmlIsProvided()
	    {
		define("EXCEPTION_XML_PARSING_ERROR", 1);

		$this->object = new DOMrunner("malformed xml");
	    } //end testThrowsAnExceptionIfMalformedXmlIsProvided()


    } //end class

?>
