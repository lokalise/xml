<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\SchemaExtendedPath;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaExtendedPath
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaExtendedPathTest.php $
 *
 * @donottranslate
 */

class SchemaExtendedPathTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test sets folder
	 *
	 * @var string
	 */
	private $_testsetsDirectory;

	/**
	 * Setting up the environment
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$this->_testsetsDirectory = __DIR__ . "/schematestsets/extendedpathtestset/";
	    } //end setUp()


	/**
	 * Simple test for extended path.
	 *
	 * @return void
	 */

	public function testExtendedPath()
	    {
		$pathwalker = new SchemaExtendedPath($this->_testsetsDirectory . "schemas/0.xsd");

		$stepresult     = $pathwalker->stepdown("/xs:schema/xs:element[1]/xs:complexType/xs:choice/xs:element/xs:complexType/xs:choice");
		$expectedresult = unserialize(file_get_contents($this->_testsetsDirectory . "output/0.txt"));
		$this->assertEquals($expectedresult, $stepresult);

		$stepresult     = $pathwalker->stepdown("/xs:schema/xs:element/xs:complexType/xs:choice/xs:element[2]/xs:complexType/" .
		"xs:choice/xs:element/xs:complexType/xs:choice/xs:element/xs:complexType/xs:sequence");
		$expectedresult = unserialize(file_get_contents($this->_testsetsDirectory . "output/1.txt"));
		$this->assertEquals($expectedresult, $stepresult);

		$stepresult     = $pathwalker->stepdown("/xs:schema/xs:element[@name='Product']/xs:complexType/xs:choice/xs:element[@name='Jewelry']/xs:complexType/xs:choice");
		$expectedresult = unserialize(file_get_contents($this->_testsetsDirectory . "output/2.txt"));
		$this->assertEquals($expectedresult, $stepresult);

		$stepresult     = $pathwalker->stepdown();
		$expectedresult = unserialize(file_get_contents($this->_testsetsDirectory . "output/3.txt"));
		$this->assertEquals($expectedresult, $stepresult);
	    } //end testExtendedPath()


    } //end class

?>
