<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\DOMXPathPDF;
use \PHPUnit_Framework_TestCase;

/**
 * Test for DOMXPathPDF class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/DOMXPathPDFTest.php $
 *
 * @donottranslate
 */

class DOMXPathPDFTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test object
	 *
	 * @var DOMXPathPDF
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
		$pdf          = file_get_contents(__DIR__ . "/testDocument.pdf");
		$this->object = new DOMXPathPDF($pdf);
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
	 * Testing getLines()
	 *
	 * @return void
	 */

	public function testCanGetLinesOfTextAsArrayFromRectangularAreaOfPdfPage()
	    {
		$lines = $this->object->getLines(100, 700, 150, 250);
		$this->assertEquals(array(
				     array("The quick brown fox jumps over the lazy dog. The quick brown fox jumps"),
				     array("over the lazy dog. The quick brown fox jumps over the lazy dog."),
				     array("Another line"),
				    ), $lines);

		$lines = $this->object->getLines(100, 700, 150, 250, false);
		$this->assertEquals(array(), $lines);

		$lines = $this->object->getLines(100, 700, 150, 250, 1, true);
		$this->assertEquals(array(
				     array("over the lazy dog. The quick brown fox jumps over the lazy dog."),
				     array(
				      "The quick brown fox jumps over the lazy dog. The quick brown fox jumps",
				      "Another line",
				     ),
				    ), $lines);
	    } //end testCanGetLinesOfTextAsArrayFromRectangularAreaOfPdfPage()


	/**
	 * Testing getText()
	 *
	 * @return void
	 */

	public function testCanGetTextAsAStringFromRectangularAreaOfPdfPage()
	    {
		$text = $this->object->getText(100, 700, 150, 250);
		$this->assertEquals(
		    "The quick brown fox jumps over the lazy dog. The quick brown fox jumps " .
		    "over the lazy dog. The quick brown fox jumps over the lazy dog. Another line",
		    $text
		);
	    } //end testCanGetTextAsAStringFromRectangularAreaOfPdfPage()


	/**
	 * Testing getTable()
	 *
	 * @return void
	 */

	public function testCanGetTableAsTwoDimensionalArrayFromRectangularAreaOfPdfPage()
	    {
		$table = $this->object->getTable(100, 700, 280, 370);
		$this->assertEquals(
		    array(
		     array(
		      "Team",
		      "P",
		      "W",
		      "D",
		      "L",
		      "F",
		      "",
		      "Pts",
		     ),
		     array(
		      "Manchester United",
		      "6",
		      "4",
		      "0",
		      "2",
		      "10",
		      "5",
		      "12",
		     ),
		     array(
		      "Celtic",
		      "6",
		      "3",
		      "0",
		      "3",
		      "8",
		      "9",
		      "9",
		     ),
		     array(
		      "Benfica",
		      "6",
		      "2",
		      "1",
		      "3",
		      "7",
		      "8",
		      "7",
		     ),
		     array(
		      "FC Copenhagen",
		      "6",
		      "2",
		      "1",
		      "3",
		      "5",
		      "8",
		      "7",
		     ),
		    ),
		    $table
		);

		$table = $this->object->getTable(100, 700, 280, 370, false);
		$this->assertEquals(array(), $table);

		$table = $this->object->getTable(100, 700, 435, 466);
		$this->assertEquals(false, $table);
	    } //end testCanGetTableAsTwoDimensionalArrayFromRectangularAreaOfPdfPage()


	/**
	 * Testing of fields functions
	 *
	 * @return void
	 */

	public function testCanGetTextFieldValueIdentifiedByAdjacentTextLabel()
	    {
		$field = $this->object->getFieldToRightOf("Field name");
		$this->assertEquals("First value", $field);

		$field = $this->object->getFieldAbove("Field name");
		$this->assertEquals("Value above", $field);

		$field = $this->object->getFieldBelow("Field name");
		$this->assertEquals("Value below", $field);

		$field = $this->object->getFieldToRightOf("BOTTOM RIGHT CORNER");
		$this->assertFalse($field);

		$field = $this->object->getFieldAbove("The quick");
		$this->assertFalse($field);

		$field = $this->object->getFieldBelow("BOTTOM RIGHT CORNER");
		$this->assertFalse($field);

		$field = $this->object->getNextField("Second value");
		$this->assertEquals("Value below", $field);

		$field = $this->object->getNextField("BOTTOM RIGHT CORNER");
		$this->assertFalse($field);
	    } //end testCanGetTextFieldValueIdentifiedByAdjacentTextLabel()


	/**
	 * Testing of label search function
	 *
	 * @return void
	 */

	public function testCanFindTextElementOnPdfPageWhichStartsWithSpecifiedText()
	    {
		$value = $this->object->findStartsWith("Field");
		$this->assertEquals("Field name:", $value);

		$value = $this->object->findStartsWith("Non-existent");
		$this->assertFalse($value);
	    } //end testCanFindTextElementOnPdfPageWhichStartsWithSpecifiedText()


    } //end class

?>
