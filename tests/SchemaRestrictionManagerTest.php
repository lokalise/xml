<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\SchemaNametraceDescriptor;
use \Logics\Foundation\XML\SchemaRestrictionManager;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing schema restriction processing
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaRestrictionManagerTest.php $
 *
 * @donottranslate
 */

class SchemaRestrictionManagerTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing how errors are fetched from descriptions.
	 *
	 * @return void
	 */

	public function testErrorFetching()
	    {
		$this->_descriptor         = new SchemaNametraceDescriptor(__DIR__ . "/schematestsets/restrictionmanager/0.xsd");
		$this->_restrictionmanager = new SchemaRestrictionManager();

		$description = $this->_descriptor->describe("Restrictions/patternRestriction");
		$this->assertEquals(false, $this->_restrictionmanager->validate("Test?201.[/]4@com", $description["restrictions"]));

		$failedrestriction = $this->_restrictionmanager->getLastFailedRestriction();
		$this->assertEquals("xs:pattern", $failedrestriction["type"]);
		$this->assertEquals(1, count($failedrestriction["values"]));
	    } //end testErrorFetching()


	/**
	 * Testing how default values are fetched from restrictions.
	 *
	 * @return void
	 */

	public function testDefaultValueFromRestriction()
	    {
		$this->_restrictionmanager = new SchemaRestrictionManager();

		$mockedenumerationdescription                 = array();
		$mockedenumerationdescription["restrictions"] = array(
								 array(
								  "type"   => "xs:enumeration",
								  "values" => array(
									       array("value" => "1"),
									       array("value" => "2"),
									      )
								 ),
								);

		$this->assertEquals("1", $this->_restrictionmanager->getDefaultValue($mockedenumerationdescription));

		$mockedemptydescription = $mockedenumerationdescription;
		$mockedemptydescription["restrictions"][0]["type"] = "xs:NONVALID";
		$this->assertEquals("", $this->_restrictionmanager->getDefaultValue($mockedemptydescription));

		$mockedbooleandescription                   = array();
		$mockedbooleandescription["nodeattributes"] = array();
		$mockedbooleandescription["nodeattributes"]["type"] = "xs:boolean";

		$this->assertEquals("false", $this->_restrictionmanager->getDefaultValue($mockedbooleandescription));

		$mockedbooleandescription["nodeattributes"]["type"] = "xs:string";
		$this->assertEquals("", $this->_restrictionmanager->getDefaultValue($mockedbooleandescription));
	    } //end testDefaultValueFromRestriction()


	/**
	 * Testing restriction processing.
	 *
	 * @return void
	 */

	public function testRestrictions()
	    {
		$this->_descriptor         = new SchemaNametraceDescriptor(__DIR__ . "/schematestsets/restrictionmanager/0.xsd");
		$this->_restrictionmanager = new SchemaRestrictionManager();

		$this->_testPattern();
		$this->_testEnumeration();
		$this->_testLength();
		$this->_testInclusive();
		$this->_testFractionDigits();
		$this->_testTotalDigits();
	    } //end testRestrictions()


	/**
	 * Testing pattern restriction.
	 *
	 * @return void
	 */

	private function _testPattern()
	    {
		$description = $this->_descriptor->describe("Restrictions/patternRestriction");
		$this->assertEquals(true, $this->_restrictionmanager->validate("test@test.com", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("test@test.com.au", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("test.test@test.com.au", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("Test2014@test.com", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("Test?201.[/]4@com", $description["restrictions"]));
	    } //end _testPattern()


	/**
	 * Testing enumeration restriction.
	 *
	 * @return void
	 */

	private function _testEnumeration()
	    {
		$description = $this->_descriptor->describe("Restrictions/enumerationRestriction");
		$this->assertEquals(true, $this->_restrictionmanager->validate("Afghanistan", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("Togo", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("Test", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("", $description["restrictions"]));
	    } //end _testEnumeration()


	/**
	 * Testing (min|max)Length restriction.
	 *
	 * @return void
	 */

	private function _testLength()
	    {
		$description = $this->_descriptor->describe("Restrictions/minLengthRestriction");
		$this->assertEquals(true, $this->_restrictionmanager->validate("aaaa", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("bbbbb", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("vvv", $description["restrictions"]));

		$description = $this->_descriptor->describe("Restrictions/maxLengthRestriction");
		$this->assertEquals(true, $this->_restrictionmanager->validate("aaaaaaa", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("bbbbbbbb", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("ccccccccc", $description["restrictions"]));
	    } //end _testLength()


	/**
	 * Testing (min|max)Inclusive restriction.
	 *
	 * @return void
	 */

	private function _testInclusive()
	    {
		$description = $this->_descriptor->describe("Restrictions/minInclusiveRestriction");
		$this->assertEquals(true, $this->_restrictionmanager->validate("10", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("11", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("9", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("null", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("TEST_VALUE", $description["restrictions"]));

		$description = $this->_descriptor->describe("Restrictions/maxInclusiveRestriction");
		$this->assertEquals(true, $this->_restrictionmanager->validate("120", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("119", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("121", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("null", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("TEST_VALUE", $description["restrictions"]));
	    } //end _testInclusive()


	/**
	 * Testing fractionDigits restriction.
	 *
	 * @return void
	 */

	private function _testFractionDigits()
	    {
		$description = $this->_descriptor->describe("Restrictions/fractionDigitsRestriction");
		$this->assertEquals(true, $this->_restrictionmanager->validate("-0.54", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("5", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("465.999", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("45.2356987", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("null", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("TEST_VALUE", $description["restrictions"]));
	    } //end _testFractionDigits()


	/**
	 * Testing fractionDigits restriction.
	 *
	 * @return void
	 */

	private function _testTotalDigits()
	    {
		$description = $this->_descriptor->describe("Restrictions/totalDigitsRestriction");

		$this->assertEquals(true, $this->_restrictionmanager->validate("-0.54", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("5", $description["restrictions"]));
		$this->assertEquals(true, $this->_restrictionmanager->validate("465.99", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("45.2356987", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("null", $description["restrictions"]));
		$this->assertEquals(false, $this->_restrictionmanager->validate("TEST_VALUE", $description["restrictions"]));
	    } //end _testTotalDigits()


    } //end class

?>
