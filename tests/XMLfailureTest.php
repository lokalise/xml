<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Foundation\XML\XMLfailure;
use \Logics\Tests\DefaultDataSet;
use \Logics\Tests\GetConnectionMySQL;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;

/**
 * Test for XMLfailure trait
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-06-02 09:07:56 +0930 (Thu, 02 Jun 2016) $ $Revision: 1632 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/XMLfailureTest.php $
 *
 * @donottranslate
 */

class XMLfailureTest extends PHPUnit_Extensions_Database_SQL_TestCase
    {
	use GetConnectionMySQL, DefaultDataSet;

	use XMLfailure;

	/**
	 * Database connection
	 *
	 * @var MySQLdatabase
	 */
	private $_db;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
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
		unset($this->object);
		$this->_db->exec("DROP TABLE IF EXISTS `FailedDocuments`");
	    } //end tearDown()


	/**
	 * Testing failure logging
	 *
	 * @return void
	 */

	public function testKeepsOnlyLastFailureMessageAssociatedWithParticularXmlDocument()
	    {
		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<test><element attribute=\"attr\">value</element><anotherelement></anotherelement><lastelement>element</lastelement></test>";

		$this->logFailure("Failure", $xml);

		$conn          = $this->getConnection();
		$queryTable    = $conn->createQueryTable("FailedDocuments", "SELECT failure, XML FROM FailedDocuments");
		$expectedTable = $this->createFlatXmlDataSet(__DIR__ . "/expectedXMLfailure1.xml")->getTable("FailedDocuments");
		$this->assertTablesEqual($expectedTable, $queryTable);

		$this->logFailure("Not a failure", $xml);

		$conn          = $this->getConnection();
		$queryTable    = $conn->createQueryTable("FailedDocuments", "SELECT failure, XML FROM FailedDocuments");
		$expectedTable = $this->createFlatXmlDataSet(__DIR__ . "/expectedXMLfailure2.xml")->getTable("FailedDocuments");
		$this->assertTablesEqual($expectedTable, $queryTable);
	    } //end testKeepsOnlyLastFailureMessageAssociatedWithParticularXmlDocument()


	/**
	 * Testing failure clearing
	 *
	 * @return void
	 */

	public function testCanClearLastFailureMessageForParticularXmlDocument()
	    {
		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$xml .= "<test><element attribute=\"attr\">value</element><anotherelement></anotherelement><lastelement>element</lastelement></test>";

		$this->clearFailure($xml);

		$conn = $this->getConnection();
		$this->assertEquals(0, $conn->getRowCount("FailedDocuments"));
	    } //end testCanClearLastFailureMessageForParticularXmlDocument()


    } //end class

?>
