<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\XMLmapping;
use \PHPUnit_Framework_TestCase;

/**
 * Test for XMLmapping trait
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/XMLmappingTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class XMLmappingTest extends PHPUnit_Framework_TestCase
    {

	use XMLmapping;

	/**
	 * Schema mappings in session have precedence over schema mappings in globals
	 *
	 * @return void
	 */

	public function testSchemaMappingsInSessionHavePrecedenceOverSchemaMappingsInGlobals()
	    {
		session_start();
		$_SESSION["SCHEMA_MAPPING"] = array("schema" => "sessionfile.xsd");
		$GLOBALS["SCHEMA_MAPPING"]  = array("schema" => "globalsfile.xsd");
		$this->_registerSchemaMappings();

		$this->assertEquals("sessionfile.xsd", $GLOBALS["SCHEMA_MAPPING"]["schema"]);
	    } //end testSchemaMappingsInSessionHavePrecedenceOverSchemaMappingsInGlobals()


	/**
	 * Supplied schema mappings have precedence over schema mappings in session
	 *
	 * @return void
	 */

	public function testSuppliedSchemaMappingsHavePrecedenceOverSchemaMappingsInSession()
	    {
		session_start();
		$_SESSION["SCHEMA_MAPPING"] = array("schema" => "sessionfile.xsd");
		$this->_registerSchemaMappings(array("schema" => "suppliedfile.xsd"));

		$this->assertEquals("suppliedfile.xsd", $_SESSION["SCHEMA_MAPPING"]["schema"]);
	    } //end testSuppliedSchemaMappingsHavePrecedenceOverSchemaMappingsInSession()


    } //end class

?>
