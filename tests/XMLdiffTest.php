<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\XMLdiff;
use \PHPUnit_Framework_TestCase;

/**
 * Test for XMLdiff class
 *
 * @author    Kate Bizimova <kate@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/XMLdiffTest.php $
 *
 * @donottranslate
 */

class XMLdiffTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test object
	 *
	 * @var XMLdiff
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
		$this->object = new XMLdiff();
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
	 * Testing validateDocument() with bad XML
	 *
	 * @return void
	 */

	public function testXMLdiff()
	    {
		$originalxml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><listofitems><item id=\"1\" index=\"1\">First</item><item id=\"2\">Second</item></listofitems>";
		$newxml      = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><listofitems><item id=\"2\">second</item><item id=\"3\">Third</item></listofitems>";
		$diff        = $this->object->diff($originalxml, $newxml);
		$this->assertXmlStringEqualsXmlString(
		    "<?xml version=\"1.0\"?><dm:diff xmlns:dm=\"http://www.locus.cz/diffmark\">" .
		    "<listofitems><dm:delete>" .
		    "<item id=\"1\" index=\"1\">First</item>" .
		    "</dm:delete>" .
		    "<item id=\"2\">" .
		    "<dm:delete>Second</dm:delete>" .
		    "<dm:insert>second</dm:insert>" .
		    "</item>" .
		    "<dm:insert>" .
		    "<item id=\"3\">Third</item>" .
		    "</dm:insert>" .
		    "</listofitems>" .
		    "</dm:diff>",
		    $diff);
	    } //end testXMLdiff()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	public function testXMLmerge()
	    {
		$originalxml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><listofitems><item id=\"1\" index=\"1\">First</item><item id=\"2\">Second</item></listofitems>";
		$newxml      = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><listofitems><item id=\"2\">second</item><item id=\"3\">Third</item></listofitems>";
		$diff        = $this->object->diff($originalxml, $newxml);
		$result      = $this->object->merge($originalxml, $diff);
		$this->assertXmlStringEqualsXmlString($newxml, $result);
	    } //end testXMLmerge()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	public function testXMLmergereverse()
	    {
		$originalxml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><listofitems><item id=\"1\" index=\"1\">First</item><item id=\"2\">Second</item></listofitems>";
		$newxml      = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><listofitems><item id=\"2\">second</item><item id=\"3\">Third</item></listofitems>";
		$diff        = $this->object->diff($originalxml, $newxml);
		$originalxml = $this->object->mergereverse($newxml, $diff);
		$this->assertXmlStringEqualsXmlString("<?xml version=\"1.0\" encoding=\"utf-8\" ?><listofitems><item id=\"1\" index=\"1\">First</item>" .
		"<item id=\"2\">Second</item></listofitems>",
		 $originalxml);
	    } //end testXMLmergereverse()


    } //end class

?>
