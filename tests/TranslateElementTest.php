<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\DOMXPathExtended;
use \Logics\Foundation\XML\TranslateElement;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing TranslateElement trait
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/TranslateElementTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class TranslateElementTest extends PHPUnit_Framework_TestCase
    {

	use TranslateElement;

	/**
	 * Testing fecthing element for "en" language.
	 *
	 * @return void
	 */

	public function testEn()
	    {
		$this->_testTranslation("en", "en_US", "Please, enter valid comment");
	    } //end testEn()


	/**
	 * Testing fecthing element for "ru" language.
	 *
	 * @return void
	 */

	public function testRu()
	    {
		$this->_testTranslation("ru", "ru_RU", "Пожалуйста, введите корректный комментарий");
	    } //end testRu()


	/**
	 * Testing fecthing element for "zh-CN" language when element itself is machine translated (ie has -x-mt suffix).
	 *
	 * @return void
	 */

	public function testMachineTranslationsAreAcceptable()
	    {
		$this->_testTranslation("zh-CN", "zh_CN", "请输入有效性评论");
	    } //end testMachineTranslationsAreAcceptable()


	/**
	 * Perform test translation
	 *
	 * @param string $language Language code
	 * @param string $locale   Locale code
	 * @param string $expected Expected translation
	 *
	 * @return void
	 */

	private function _testTranslation($language, $locale, $expected)
	    {
		define("LANGUAGE", $language);
		define("LOCALE", $locale);

		$schema                     = new DOMDocument("1.0", "utf-8");
		$schema->preserveWhiteSpace = false;
		$schema->load(__DIR__ . "/schematestsets/translateelementtest/0.xsd");
		$this->translateElement($schema->documentElement, true);
		$schemaxpath = new DOMXPathExtended($schema);

		$title = $schemaxpath->query("//dc:title");
		$this->assertEquals(1, $title->length);
		$this->assertEquals("Comment", $title->item(0)->textContent);

		$documentation = $schemaxpath->query("//dc:documentation");
		$this->assertEquals(1, $documentation->length);
		$this->assertEquals("Documentation", $documentation->item(0)->textContent);

		$errormessage = $schemaxpath->query("//vxe:invalidInputErrorMessage");
		$this->assertEquals(1, $errormessage->length);
		$this->assertEquals($expected, $errormessage->item(0)->textContent);
	    } //end _testTranslation()


    } //end class

?>
