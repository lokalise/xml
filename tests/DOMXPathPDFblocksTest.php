<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\DOMXPathPDFblocks;
use \PHPUnit_Framework_TestCase;

/**
 * Test for DOMXPathPDFblocks class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/DOMXPathPDFblocksTest.php $
 *
 * @donottranslate
 */

class DOMXPathPDFblocksTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test object
	 *
	 * @var DOMXPathPDFblocks
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
		$this->object = new DOMXPathPDFblocks($pdf);
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
	 * Testing getPageSize()
	 *
	 * @return void
	 */

	public function testCanGetPdfPageSize()
	    {
		$size = $this->object->getPageSize(1);
		$this->assertEquals(array("width" => 892, "height" => 1262), $size);

		$size = $this->object->getPageSize(0);
		$this->assertFalse($size);
	    } //end testCanGetPdfPageSize()


	/**
	 * Testing of boundary functions
	 *
	 * @return void
	 */

	public function testCanCalculateVariousTextElementBoundariesOnPdfPage()
	    {
		$leftof = $this->object->leftOf("TOP LEFT CORNER");
		$this->assertEquals(0, $leftof);

		$toleftof = $this->object->toLeftOf("TOP LEFT CORNER");
		$this->assertEquals(0, $toleftof);

		$rightof = $this->object->rightOf("TOP LEFT CORNER");
		$this->assertEquals(148, $rightof);

		$torightof = $this->object->toRightOf("TOP LEFT CORNER");
		$this->assertEquals(149, $torightof);

		$topof = $this->object->topOf("TOP LEFT CORNER");
		$this->assertEquals(0, $topof);

		$above = $this->object->above("TOP LEFT CORNER");
		$this->assertEquals(0, $above);

		$bottomof = $this->object->bottomOf("TOP LEFT CORNER");
		$this->assertEquals(13, $bottomof);

		$below = $this->object->below("TOP LEFT CORNER");
		$this->assertEquals(14, $below);

		$leftof = $this->object->leftOf("MULTIPLE LABELS");
		$this->assertEquals(103, $leftof);

		$toleftof = $this->object->toLeftOf("MULTIPLE LABELS");
		$this->assertEquals(102, $toleftof);

		$rightof = $this->object->rightOf("MULTIPLE LABELS");
		$this->assertEquals(287, $rightof);

		$torightof = $this->object->toRightOf("MULTIPLE LABELS");
		$this->assertEquals(288, $torightof);

		$topof = $this->object->topOf("MULTIPLE LABELS");
		$this->assertEquals(518, $topof);

		$above = $this->object->above("MULTIPLE LABELS");
		$this->assertEquals(517, $above);

		$bottomof = $this->object->bottomOf("MULTIPLE LABELS");
		$this->assertEquals(568, $bottomof);

		$below = $this->object->below("MULTIPLE LABELS");
		$this->assertEquals(569, $below);

		$leftof = $this->object->leftOf("BOTTOM RIGHT CORNER", 1, true);
		$this->assertEquals(697, $leftof);

		$toleftof = $this->object->toLeftOf("BOTTOM RIGHT CORNER");
		$this->assertEquals(696, $toleftof);

		$rightof = $this->object->rightOf("BOTTOM RIGHT CORNER", 1, true);
		$this->assertEquals(892, $rightof);

		$torightof = $this->object->toRightOf("BOTTOM RIGHT CORNER");
		$this->assertEquals(893, $torightof);

		$topof = $this->object->topOf("BOTTOM RIGHT CORNER", 1, true);
		$this->assertEquals(1249, $topof);

		$above = $this->object->above("BOTTOM RIGHT CORNER");
		$this->assertEquals(1248, $above);

		$bottomof = $this->object->bottomOf("BOTTOM RIGHT CORNER", 1, true);
		$this->assertEquals(1262, $bottomof);

		$below = $this->object->below("BOTTOM RIGHT CORNER");
		$this->assertEquals(1263, $below);

		$torightof = $this->object->toRightOf("NON-EXISTENT LABEL");
		$this->assertFalse($torightof);

		$below = $this->object->below("NON-EXISTENT LABEL");
		$this->assertFalse($below);
	    } //end testCanCalculateVariousTextElementBoundariesOnPdfPage()


    } //end class

?>
