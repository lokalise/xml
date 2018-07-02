<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\SchemaNametraceDescriptor;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SchemaNametraceDescriptor internal processing
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/SchemaNametraceDescriptorInternalsTest.php $
 *
 * @donottranslate
 */

class SchemaNametraceDescriptorInternalsTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Test sets directory
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
		$this->_testsetsDirectory = __DIR__ . "/schematestsets/nametracedescriptortestset/";
	    } //end setUp()


	/**
	 * Simple test for regular path.
	 *
	 * @return void
	 */

	public function testSchemaNametraceDescriptor()
	    {
		$descriptor = new SchemaNametraceDescriptor($this->_testsetsDirectory . "schemas/0.xsd");

		$description    = $descriptor->describe("Product[1]");
		$expectedresult = unserialize(file_get_contents($this->_testsetsDirectory . "output/0.txt"));

		$this->assertEquals($expectedresult, $description);

		$description    = $descriptor->describe("Product[1]/Jewelry[1]/Fashion[1]/Charm[1]");
		$expectedresult = unserialize(file_get_contents($this->_testsetsDirectory . "output/1.txt"));

		$this->assertEquals($expectedresult, $description);
	    } //end testSchemaNametraceDescriptor()


	/**
	 * Testing processing of attribute restrictions.
	 *
	 * @return void
	 */

	public function testSchemaNametraceDescriptorAttributeRestrictions()
	    {
		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$description = $descriptor->describe("Product[1]/First");
		$this->assertEquals(3, count($description["elementattributes"][0]["restrictions"][0]["values"]));
	    } //end testSchemaNametraceDescriptorAttributeRestrictions()


	/**
	 * Testing choice description.
	 *
	 * @return void
	 */

	public function testSchemaNametraceDescriptorFormatter()
	    {
		$descriptor = new SchemaNametraceDescriptor($this->_testsetsDirectory . "schemas/1.xsd");

		$description = $descriptor->describe("Product/One");
		$this->assertEquals(3, count($description["restrictions"]));
	    } //end testSchemaNametraceDescriptorFormatter()


	/**
	 * Testing restrictions of leaf element.
	 *
	 * @return void
	 */

	public function testLeafElementWithRestrictions()
	    {
		$descriptor     = new SchemaNametraceDescriptor($this->_testsetsDirectory . "schemas/0.xsd");
		$description    = $descriptor->describe("Product[1]/Jewelry/Fashion[1]/Charm[1]/Size/Height");
		$expectedresult = unserialize(file_get_contents($this->_testsetsDirectory . "output/2.txt"));
		$this->assertEquals($expectedresult, $description);
	    } //end testLeafElementWithRestrictions()


	/**
	 * Testing choice name substitution in nametrace.
	 *
	 * @return void
	 */

	public function testChoiceNametraceSubstitution()
	    {
		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$description = $descriptor->describe("Store/AmericanLiterature/Two");
		$this->assertEquals("Two", $description["name"]);
	    } //end testChoiceNametraceSubstitution()


	/**
	 * Testing processing of referenced element.
	 *
	 * @return void
	 */

	public function testReferencedElements()
	    {
		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/coreschema.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$description = $descriptor->describe("CoreElement/ReferencedElement");
		$this->assertEquals(1, count($description["children"]));
	    } //end testReferencedElements()


	/**
	 * Testing restrictions of leaf element.
	 *
	 * @return void
	 */

	public function testReferencedAnnotation()
	    {
		$descriptor = new SchemaNametraceDescriptor($this->_testsetsDirectory . "/" . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd");

		$description = $descriptor->describe("Catalog");
		$document    = new DOMDocument();
		$document->loadXML($description["documentation"]);
		foreach ($document->documentElement->childNodes as $child)
		    {
			if ($child->nodeName === "test")
			    {
				$this->assertEquals("Catalog test", $child->textContent);
			    } //end if
		    } //end foreach

		$description = $descriptor->describe("Catalog/Book");
		$document    = new DOMDocument();
		$document->loadXML($description["documentation"]);
		foreach ($document->documentElement->childNodes as $child)
		    {
			if ($child->nodeName === "test")
			    {
				$this->assertEquals("Book test", $child->textContent);
			    } //end if
		    } //end foreach

		$description = $descriptor->describe("Catalog/Painting");
		$document    = new DOMDocument();
		$document->loadXML($description["documentation"]);
		foreach ($document->documentElement->childNodes as $child)
		    {
			if ($child->nodeName === "test")
			    {
				$this->assertEquals("Painting test", $child->textContent);
			    } //end if
		    } //end foreach
	    } //end testReferencedAnnotation()


	/**
	 * Testing how annotations corresponding to single element can be merged.
	 *
	 * @return void
	 */

	public function testAnnotationMerge()
	    {
		$coreschema = $this->_testsetsDirectory . str_replace(__CLASS__ . "::", "", __METHOD__) . "/0.xsd";
		$descriptor = new SchemaNametraceDescriptor($coreschema);

		$description = $descriptor->describe("Product/Jewelry");

		$appinfodocument                     = new DOMDocument("1.0", "utf-8");
		$appinfodocument->preserveWhiteSpace = false;
		$appinfodocument->loadXML($description["appinfo"]);
		$this->assertEquals(2, $appinfodocument->documentElement->childNodes->length);
	    } //end testAnnotationMerge()


    } //end class

?>
