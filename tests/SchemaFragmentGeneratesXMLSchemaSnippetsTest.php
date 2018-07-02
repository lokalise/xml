<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\XML\SchemaFragment;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaFragment class.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaFragmentGeneratesXMLSchemaSnippetsTest.php $
 *
 * @donottranslate
 */

class SchemaFragmentGeneratesXMLSchemaSnippetsTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Defining the location of test cases
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$this->_testsetFolder = __DIR__ . "/schematestsets/schemafragment";
	    } //end setUp()


	/**
	 * Testing generation of one-line Schema element with provided documentation.
	 *
	 * @return void
	 */

	public function testGeneratesElementsWithDocumentation()
	    {
		$schemafragment = new SchemaFragment("xs");

		$elementdescription                   = array();
		$elementdescription["name"]           = "TestElement";
		$elementdescription["nodeattributes"] = array("type" => "string");

		$elementdescription["annotation"]                  = array();
		$elementdescription["annotation"]["documentation"] = array(
								      "title"       => "Test element",
								      "description" => "Test description",
								     );

		$generatedelement = $schemafragment->get($elementdescription, false);

		$snippetlocation = $this->_testsetFolder . "/elementwithdocumentation.xmlsnippet";
		$this->assertEquals(file_get_contents($snippetlocation), $generatedelement->ownerDocument->saveXML($generatedelement));
	    } //end testGeneratesElementsWithDocumentation()


	/**
	 * Testing generation of simpleType Schema element with restrictions.
	 *
	 * @return void
	 */

	public function testGeneratesElementsWithRestrictions()
	    {
		$schemafragment = new SchemaFragment("xs");

		$elementdescription                   = array();
		$elementdescription["name"]           = "TestElement";
		$elementdescription["nodeattributes"] = array("type" => "string");

		$elementdescription["annotation"]                  = array();
		$elementdescription["annotation"]["documentation"] = array(
								      "title"       => "Test element",
								      "description" => "Test description",
								     );

		$data = array(
			 array(
			  "type"   => "xs:enumeration",
			  "values" => array(
				       array(
					"value"      => "Dernholm",
					"annotation" => array(
							 "documentation" => array(
									     "title"       => "Dernholm",
									     "description" => "Capital of Cambria",
									    ),
							),
				       ),
				       array(
					"value"      => "Thula",
					"annotation" => array(
							 "documentation" => array(
									     "title"       => "Thula",
									     "description" => "Lost in the Wastes",
									    ),
							),
				       ),
				      ),
			 ),
			);

		$elementdescription["restrictions"] = $data;

		$generatedelement = $schemafragment->get($elementdescription, false);

		$snippetlocation = $this->_testsetFolder . "/elementwithrestrictions.xmlsnippet";
		$this->assertEquals(file_get_contents($snippetlocation), $generatedelement->ownerDocument->saveXML($generatedelement));
	    } //end testGeneratesElementsWithRestrictions()


	/**
	 * Testing that custom appinfo settings can be set.
	 *
	 * @return void
	 */

	public function testGeneratesElementsWithAppinfoSettings()
	    {
		$schemafragment = new SchemaFragment("xs", array(array("prefix" => "vxe", "uri" => "urn:visualXMLeditor:settings")));

		$elementdescription                   = array();
		$elementdescription["name"]           = "TestElement";
		$elementdescription["nodeattributes"] = array("type" => "string");

		$elementdescription["annotation"]                  = array();
		$elementdescription["annotation"]["documentation"] = array(
								      "title"       => "Test element",
								      "description" => "Test description",
								     );

		$elementdescription["annotation"]["appinfo"] = array(
								"vxe:settings" => array(
										   "hidebuttons" => "true",
										   "widget"      => array(
												     "select" => array(
														  "allowEmptyElement" => "true",
														  "emptyElementText"  => "Enter the value",
														 )
												    )
										  )
							       );

		$data = array(
			 array(
			  "type"   => "xs:enumeration",
			  "values" => array(
				       array(
					"value"      => "Dernholm",
					"annotation" => array(
							 "documentation" => array(
									     "title"       => "Dernholm",
									     "description" => "Capital of Cambria",
									    ),
							),
				       ),
				       array(
					"value"      => "Thula",
					"annotation" => array(
							 "documentation" => array(
									     "title"       => "Thula",
									     "description" => "Lost in the Wastes",
									    ),
							),
				       ),
				      ),
			 ),
			);

		$elementdescription["restrictions"] = $data;

		$generatedelement = $schemafragment->get($elementdescription);
		$this->assertContains("<xs:appinfo><vxe:settings><hidebuttons>true</hidebuttons><widget><select>" .
		"<allowEmptyElement>true</allowEmptyElement><emptyElementText>Enter the value</emptyElementText>" .
		"</select></widget></vxe:settings></xs:appinfo>", $generatedelement->ownerDocument->saveXML($generatedelement));
	    } //end testGeneratesElementsWithAppinfoSettings()


    } //end class

?>
