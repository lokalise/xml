<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\SchemaNametrace;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaNametrace trait
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaNametraceTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class SchemaNametraceTest extends PHPUnit_Framework_TestCase
    {

	use SchemaNametrace;

	/**
	 * Regular test.
	 *
	 * @return void
	 */

	public function testSchemaNametrace()
	    {
		$this->assertEquals("One[1]/Two[2]/Three", $this->_removeLastNametraceIndex("One[1]/Two[2]/Three"));
		$this->assertEquals("One[1]/Two[2]/Three", $this->_removeLastNametraceIndex("One[1]/Two[2]/Three[2]"));

		$this->assertEquals("2", $this->_getLastNametraceIndex("One[1]/Two[2]/Three[2]"));
	    } //end testSchemaNametrace()


	/**
	 * Testing SchemaTools trait, _getLastNametraceIndex() method
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid nametrace
	 */

	public function testSchemaToolsGetLastNametraceIndex()
	    {
		define("EXCEPTION_INVALID_NAMETRACE", 1);

		$this->_getLastNametraceIndex("/test/test");
	    } //end testSchemaToolsGetLastNametraceIndex()


    } //end class

?>
