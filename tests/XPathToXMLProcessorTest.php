<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\SchemaNametraceDescriptor;
use \Logics\Foundation\XML\XPathToXMLProcessor;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing XPathToXMLProcessor.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/XPathToXMLProcessorTest.php $
 *
 * @donottranslate
 */

class XPathToXMLProcessorTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing document with leaf elements.
	 *
	 * @return void
	 */

	public function testGeneratesDocumentWhenLeafValuesOnlyAreProvided()
	    {
		$arrayfile    = __DIR__ . "/xpathtoxmlprocessor/0.json";
		$documentfile = __DIR__ . "/xpathtoxmlprocessor/0.xml";
		$dataarray    = json_decode(file_get_contents($arrayfile), true);

		$processor         = new XPathToXMLProcessor();
		$generateddocument = $processor->process($dataarray);

		$expecteddocument                     = new DOMDocument("1.0", "utf-8");
		$expecteddocument->preserveWhiteSpace = false;
		$expecteddocument->load($documentfile);

		$this->assertEquals($generateddocument->saveXML(), $expecteddocument->saveXML());
	    } //end testGeneratesDocumentWhenLeafValuesOnlyAreProvided()


	/**
	 * Testing document with leaf elements and attributes for both leaf and hierarchy elements.
	 *
	 * @return void
	 */

	public function testGeneratesDocumentWhenLeafElementsAndAttributesForBothLeafAndHierarchyElementsAreProvided()
	    {
		$arrayfile    = __DIR__ . "/xpathtoxmlprocessor/2.json";
		$documentfile = __DIR__ . "/xpathtoxmlprocessor/2.xml";
		$dataarray    = json_decode(file_get_contents($arrayfile), true);

		$processor         = new XPathToXMLProcessor();
		$generateddocument = $processor->process($dataarray);

		$expecteddocument                     = new DOMDocument("1.0", "utf-8");
		$expecteddocument->preserveWhiteSpace = false;
		$expecteddocument->load($documentfile);

		$this->assertEquals($generateddocument->saveXML(), $expecteddocument->saveXML());
	    } //end testGeneratesDocumentWhenLeafElementsAndAttributesForBothLeafAndHierarchyElementsAreProvided()


	/**
	 * Testing ability to fix the order of provided data if compiled schema is available.
	 *
	 * @return void
	 */

	public function testFixesInputDataOrderIfNametraceDescriptorIsProvided()
	    {
		$arrayfile    = __DIR__ . "/xpathtoxmlprocessor/3.json";
		$schemafile   = __DIR__ . "/xpathtoxmlprocessor/3.xsd";
		$documentfile = __DIR__ . "/xpathtoxmlprocessor/3.xml";
		$dataarray    = json_decode(file_get_contents($arrayfile), true);

		$nametracedescriptor = new SchemaNametraceDescriptor($schemafile);

		$processor         = new XPathToXMLProcessor($nametracedescriptor);
		$generateddocument = $processor->process($dataarray);

		$expecteddocument                     = new DOMDocument("1.0", "utf-8");
		$expecteddocument->preserveWhiteSpace = false;
		$expecteddocument->load($documentfile);

		$this->assertEquals($expecteddocument->saveXML(), $generateddocument->saveXML());
	    } //end testFixesInputDataOrderIfNametraceDescriptorIsProvided()


    } //end class

?>
