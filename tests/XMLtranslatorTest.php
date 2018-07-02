<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\XMLtranslator;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing XML translator
 *
 * @author    Ekaterina Bizimova <kate@logics.net.au>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/XMLtranslatorTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class XMLtranslatorTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing bad response from Translate service.
	 *
	 * @expectedException              Exception
	 * @expectedExceptionMessageRegExp #.*: Unable to parse URL#
	 *
	 * @return void
	 *
	 * @requires extension runkit
	 */

	public function testBadTranslateResponseProcessing()
	    {
		define("EXCEPTION_TRANSLATION_FAULT", 1);

		class_exists("Logics\Foundation\XML\XMLtranslator");
		runkit_constant_redefine("\Logics\Foundation\XML\XMLtranslator::SERVICE_WSDL", __DIR__ . "/TranslateService.wsdl");
		$this->_processPair("process", "check.xml", "result.xml");
	    } //end testBadTranslateResponseProcessing()


	/**
	 * Testing XML translator document processing.
	 *
	 * @return void
	 */

	public function testXMLTranslatorProcess()
	    {
		define("EXCEPTION_TRANSLATION_FAULT", 1);

		$this->_processPair("process", "check.xml", "result.xml");
		$this->_processPair("process", "checkduplicated.xml", "resultduplicated.xml");
		$this->_processPair("process", "checknamespaces.xml", "resultnamespaces.xml");
	    } //end testXMLTranslatorProcess()


	/**
	 * Testing XML translator document cleaning.
	 *
	 * @return void
	 */

	public function testXMLTranslatorClean()
	    {
		$this->_processPair("clean", "cleancheck.xml", "cleanresult.xml");
		$this->_processPair("clean", "cleancheck-x-mt.xml", "cleanresult-x-mt.xml");
		$this->_processPair("clean", "cleanchecknotag.xml", "cleanresultnotag.xml");
	    } //end testXMLTranslatorClean()


	/**
	 * Testing XML translator document cleaning with additional attributes
	 *
	 * @return void
	 */

	public function testXMLTranslatorCleanWithAdditionalAttributes()
	    {
		$object = new XMLtranslator();

		$document                     = new DOMDocument("1.0", "utf-8");
		$document->preserveWhiteSpace = false;
		$document->load(__DIR__ . "/translatortest/cleanadditional.xml");

		$translateddocument                     = new DOMDocument("1.0", "utf-8");
		$translateddocument->preserveWhiteSpace = false;
		$translateddocument->load(__DIR__ . "/translatortest/resultcleanadditional.xml");

		$result = $object->clean($document->saveXML(), "en", array("languageID"));

		$this->assertEquals($translateddocument->saveXML(), $result);
	    } //end testXMLTranslatorCleanWithAdditionalAttributes()


	/**
	 * Process pair of testsets.
	 *
	 * @param string $action   Specifies what to test
	 * @param string $input    Input document
	 * @param string $expected Expected document
	 * @param string $priority Translator service priority
	 *
	 * @return void
	 *
	 * @throws Exception Specify correct action
	 */

	private function _processPair($action, $input, $expected, $priority = "1")
	    {
		$class     = new XMLtranslator();
		$languages = array(
			      "ru",
			      "en",
			      "mt",
			     );

		$document                     = new DOMDocument("1.0", "utf-8");
		$document->preserveWhiteSpace = false;
		$document->load(__DIR__ . "/translatortest/" . $input);

		if ($action === "process")
		    {
			$result = $class->process($document->saveXML(), "Logics", "8d9e4c8c77c29a28064c07fbf62fa735", $priority, $languages);
		    }
		else if ($action === "clean")
		    {
			$result = $class->clean($document->saveXML(), "en");
		    }
		else
		    {
			throw new Exception("Specify correct action", 100);
		    }

		$document                     = new DOMDocument("1.0", "utf-8");
		$document->preserveWhiteSpace = false;
		$document->loadXML($result);

		$translatedDocument                     = new DOMDocument("1.0", "utf-8");
		$translatedDocument->preserveWhiteSpace = false;
		$translatedDocument->load(__DIR__ . "/translatortest/" . $expected);

		$this->assertEquals($translatedDocument->saveXML(), $document->saveXML());
	    } //end _processPair()


    } //end class

?>
