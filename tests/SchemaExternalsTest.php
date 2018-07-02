<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\DOMXPathExtended;
use \Logics\Foundation\XML\SchemaExternals;
use \Logics\Foundation\XML\SchemaNametraceDescriptor;
use \Logics\Tests\InternalWebServer;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaExternals class
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-06-07 03:05:15 +0930 (Tue, 07 Jun 2016) $ $Revision: 1640 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaExternalsTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class SchemaExternalsTest extends PHPUnit_Framework_TestCase
    {

	use InternalWebServer;

	/**
	 * Test set folder
	 *
	 * @var string
	 */
	private $_testsetfolder;

	/**
	 * Perform test preparations.
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$this->_testsetfolder       = __DIR__ . "/schematestsets/schemaexternalstest/";
		$_SESSION["SCHEMA_MAPPING"] = array(
					       "http://www.dublincore.org/schemas/xmls/qdc/dc.xsd" => __DIR__ . "/externalschemas/www.dublincore.org/schemas/xmls/qdc/dc.xsd",
					       "http://www.w3.org/2001/03/xml.xsd"                 => __DIR__ . "/externalschemas/www.w3.org/2001/03/xml.xsd",
					      );
	    } //end setUp()


	/**
	 * Getting remote schema file.
	 *
	 * @return void
	 */

	public function testExternalsGetRemoteFile()
	    {
		$descriptor = new SchemaNametraceDescriptor($this->webserverURL() . "/externalschemas/www.w3.org/2001/03/xml.xsd");
		unset($descriptor);
	    } //end testExternalsGetRemoteFile()


	/**
	 * Testing fetching the schema through the HTTP request.
	 *
	 * @return void
	 */

	public function testExternalsHTTPFetchContent()
	    {
		$schemadocument = new DOMDocument("1.0", "utf-8");
		$schemadocument->loadXML(file_get_contents($this->_testsetfolder . "4.xsd"));
		$xpath = new DOMXPathExtended($schemadocument);

		$importstatement = $xpath->query("//xs:import")->item(0);
		$importstatement->setAttribute("schemaLocation", $this->webserverURL() . "/externalschemas/www.w3.org/2001/03/xml.xsd");

		$temporaryschema = sys_get_temp_dir() . "/schemaexternalfetchtest" . time() . ".xsd";
		$schemadocument->save($temporaryschema);

		$descriptor = new SchemaNametraceDescriptor($temporaryschema);
		unset($descriptor);

		unlink($temporaryschema);
	    } //end testExternalsHTTPFetchContent()


	/**
	 * Testing fetching the schema through the HTTP request.
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Unable to retrieve
	 */

	public function testExternalsHTTPFetchError()
	    {
		define("EXCEPTION_UNABLE_TO_RETRIEVE", 1);

		$descriptor = new SchemaNametraceDescriptor($this->_testsetfolder . "/1.xsd");
		unset($descriptor);
	    } //end testExternalsHTTPFetchError()


	/**
	 * Testing fetching the schema through the HTTP request.
	 *
	 * @return void
	 *
	 * @expectedException        Exception
	 * @expectedExceptionMessage Unable to get specified external
	 */

	public function testUnresolvableExternal()
	    {
		define("EXCEPTION_UNABLE_TO_GET_EXTERNAL", 1);

		$descriptor = new SchemaNametraceDescriptor($this->_testsetfolder . "/2.xsd");
		unset($descriptor);
	    } //end testUnresolvableExternal()


	/**
	 * Testing ability to process invalid schema filepath.
	 *
	 * @return void
	 *
	 * @runInSeparateProcess
	 * @expectedException        Exception
	 * @expectedExceptionMessage Unable to load schema
	 */

	public function testUnableToLoadInitialSchema()
	    {
		define("EXCEPTION_UNABLE_TO_LOAD_SCHEMA", 1);

		if (file_exists($this->_testsetfolder . "/3.xsd") === true)
		    {
			unlink($this->_testsetfolder . "/3.xsd");
		    }

		copy($this->_testsetfolder . "/2.xsd", $this->_testsetfolder . "/3.xsd");
		$externals = new SchemaExternals();
		unlink($this->_testsetfolder . "/3.xsd");
		$externals->getID($this->_testsetfolder . "/3.xsd");
	    } //end testUnableToLoadInitialSchema()


	/**
	 * Test the case when referenced schema filename relates to physical schema with different filename.
	 *
	 * @return void
	 */

	public function testAliasedSchema()
	    {
		$_SESSION["SCHEMA_MAPPING"] = array($this->_testsetfolder . "31.xsd" => $this->_testsetfolder . "32.xsd");

		$descriptor = new SchemaNametraceDescriptor($this->_testsetfolder . "30.xsd");
		unset($descriptor);
	    } //end testAliasedSchema()


	/**
	 * Testing schema list completion.
	 *
	 * @return void
	 */

	public function testSchemaListCompletion()
	    {
		$externals = new SchemaExternals();
		$externals->getID($this->_testsetfolder . "/0.xsd");

		$list = $externals->getSchemasList();
		$this->assertStringEndsWith("0.xsd", $list["root"][0]["externalfile"]);
		$this->assertStringEndsWith("dc.xsd", $list["import"][0]["externalfile"]);
	    } //end testSchemaListCompletion()


    } //end class

?>
