<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Foundation\XML\SchemaFlatten;
use \Logics\Foundation\XML\XMLerrors;
use \Logics\Foundation\XML\XMLfailure;
use \Logics\Tests\DefaultDataSet;
use \Logics\Tests\GetConnectionMySQL;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;

/**
 * Class for testing SchemaFlatten
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-06-02 09:07:56 +0930 (Thu, 02 Jun 2016) $ $Revision: 1632 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaFlattenTest.php $
 *
 * @donottranslate
 */

class SchemaFlattenTest extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	use GetConnectionMySQL, DefaultDataSet;

	use XMLfailure, XMLerrors;

	/**
	 * Defining the location of test cases
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$this->_db = new MySQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);

		parent::setUp();
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		$this->_db->execUntilSuccessful("DROP TABLE IF EXISTS `FailedDocuments`");
	    } //end tearDown()


	/**
	 * Testing specific flatten testset for equality
	 *
	 * @param string $testindex Index of the testset
	 *
	 * @return void
	 */

	private function _testFlattenedModeForEquality($testindex)
	    {
		$testdocuments = $this->_getFlattenTestDocuments($testindex);

		foreach ($testdocuments as $documentset)
		    {
			$schemadocumentpath         = $documentset["schema"];
			$expandeddocumentpath       = $documentset["expandeddocumentfilename"];
			$flattenedarraydocumentpath = $documentset["flattenedarray"];

			$schema = new SchemaFlatten($schemadocumentpath, $this->_db);

			$expandeddocument                     = new DOMDocument("1.0", "utf-8");
			$expandeddocument->preserveWhiteSpace = false;
			$expandeddocument->load($expandeddocumentpath);

			$itemsarray = $schema->getFlattenedElementsArray($expandeddocument->documentElement);

			$this->assertEquals(file_get_contents($flattenedarraydocumentpath), json_encode($itemsarray));
		    } //end foreach
	    } //end _testFlattenedModeForEquality()


	/**
	 * Test case: invalid expanded document provided.
	 * Expected: check for exception.
	 *
	 * @return void
	 */

	public function testInvalidExpandedDocumentException()
	    {
		$testdocuments = $this->_getFlattenTestDocuments(6);

		foreach ($testdocuments as $documentset)
		    {
			$schemadocumentpath           = $documentset["schema"];
			$expandeddocumentdocumentpath = $documentset["expandeddocumentfilename"];

			$schema = new SchemaFlatten($schemadocumentpath, $this->_db);

			$inputpropertiesdocument                     = new DOMDocument("1.0", "utf-8");
			$inputpropertiesdocument->preserveWhiteSpace = false;
			$inputpropertiesdocument->load($expandeddocumentdocumentpath);

			$itemsarray = $schema->getFlattenedElementsArray($inputpropertiesdocument->documentElement);
			$this->assertNull($itemsarray);
		    } //end foreach
	    } //end testInvalidExpandedDocumentException()


	/**
	 * Generating path to specific Flatten documents
	 *
	 * @param string $testindex Number of the test
	 *
	 * @return array
	 *
	 * @throws Exception No flattened array
	 *
	 * @exceptioncode EXCEPTION_NO_FLATTENED_ARRAY
	 */

	private function _getFlattenTestDocuments($testindex)
	    {
		$filesetarray = array();
		$basedir      = __DIR__ . DIRECTORY_SEPARATOR . "schematestsets/flatten" . DIRECTORY_SEPARATOR;

		$schemafolder                = $basedir . "Schema";
		$expandeddocumentfolder      = $basedir . "ExpandedDocument";
		$flattenedarrayfolder        = $basedir . "FlattenedArray";
		$expandeddocumentfolderfiles = scandir($expandeddocumentfolder);

		foreach ($expandeddocumentfolderfiles as $filename)
		    {
			if (($filename !== ".") && ($filename !== ".."))
			    {
				$expandeddocumentfilenamearray = explode(".", $filename);
				$indexfilename                 = $expandeddocumentfilenamearray[0];
				$testindexarray                = explode("_", $indexfilename);
				$currenttestindex              = $testindexarray[0];

				if (intval($currenttestindex) === intval($testindex))
				    {
					$schemafilename           = $schemafolder . DIRECTORY_SEPARATOR . $indexfilename . ".xsd";
					$expandeddocumentfilename = $expandeddocumentfolder . DIRECTORY_SEPARATOR . $indexfilename . ".xml";
					$flattenedarrayfilename   = $flattenedarrayfolder . DIRECTORY_SEPARATOR . $indexfilename . ".json";

					if (file_exists($flattenedarrayfilename) === false)
					    {
						throw new Exception(
						    "No FlattenedArray (" . $flattenedarrayfilename . ") for ExpandedDocument " . $expandeddocumentfilename,
						    EXCEPTION_NO_FLATTENED_ARRAY
						);
					    }

					$filesetarray[] = array(
							   "expandeddocumentfilename" => $expandeddocumentfilename,
							   "flattenedarray"           => $flattenedarrayfilename,
							   "schema"                   => $schemafilename,
							  );
				    } //end if
			    } //end if
		    } //end foreach

		return $filesetarray;
	    } //end _getFlattenTestDocuments()


	/**
	 * Test case: expanding general case.
	 * Expected: successfull generation of flattened document.
	 *
	 * @return void
	 */

	public function testGeneral()
	    {
		$this->_testFlattenedModeForEquality(0);
	    } //end testGeneral()


	/**
	 * Test case: three elements with the same attributes.
	 * Expected: according to rules - unique names in flattened document.
	 *
	 * @return void
	 */

	public function testSameAttributes()
	    {
		$this->_testFlattenedModeForEquality(1);
	    } //end testSameAttributes()


	/**
	 * Test case: two elements with the same name.
	 * Expected: according to rules - unique names in flattened document.
	 *
	 * @return void
	 */

	public function testSameNames()
	    {
		$this->_testFlattenedModeForEquality(2);
	    } //end testSameNames()


	/**
	 * Test case: two elements with the same name and one-level parentname.
	 * Expected: according to rules - unique names in flattened document.
	 *
	 * @return void
	 */

	public function testSameNamesAndParentName()
	    {
		$this->_testFlattenedModeForEquality(3);
	    } //end testSameNamesAndParentName()


	/**
	 * Test case: two elements with the same name and three-level parentname.
	 * Expected: according to rules - unique names in flattened document.
	 *
	 * @return void
	 */

	public function testSameNamesAndThreeLevelSameParentNames()
	    {
		$this->_testFlattenedModeForEquality(4);
	    } //end testSameNamesAndThreeLevelSameParentNames()


	/**
	 * Test case: two elements with the same name and three-level parentname.
	 * one of the hierarchy nodes has an unique attribute, two of hierarchy nodes with same name has same attribute.
	 * Expected: according to rules - unique names in flattened document.
	 *
	 * @return void
	 */

	public function testSameNamesAndThreeLevelSameParentNamesAndHierarchyNodeAttribute()
	    {
		$this->_testFlattenedModeForEquality(5);
	    } //end testSameNamesAndThreeLevelSameParentNamesAndHierarchyNodeAttribute()


    } //end class

?>
