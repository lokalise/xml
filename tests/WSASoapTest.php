<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \DOMDocument;
use \Logics\Foundation\XML\WSASoap;
use \PHPUnit_Framework_TestCase;

/**
 * Test for WSASoap class
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-05-19 09:54:28 +0930 (Thu, 19 May 2016) $ $Revision: 1614 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/WSASoapTest.php $
 *
 * @donottranslate
 */

class WSASoapTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing setting and getting of WS-Addressing fields
	 *
	 * @return void
	 */

	public function testSetsAndGetsToReplyToAndActionFileds()
	    {
		$document                     = new DOMDocument("1.0", "UTF-8");
		$document->preserveWhiteSpace = true;
		$document->load(__DIR__ . "/soapexample.xml");

		$wssoap = new WSASoap($document);

		$this->assertEquals(false, $wssoap->getTo());
		$this->assertEquals(false, $wssoap->getAction());
		$this->assertEquals(false, $wssoap->getReplyTo());

		$wssoap->addAction("testaction");
		$wssoap->addTo("testto");
		$wssoap->addReplyTo("testreplyto");

		$resultingdoc = $wssoap->getDoc();
		$rawdoc       = $resultingdoc->saveXML();

		$this->assertContains("<wsa:To>testto</wsa:To>", $rawdoc);
		$this->assertContains("<wsa:Action>testaction</wsa:Action>", $rawdoc);
		$this->assertContains("<wsa:Address>testreplyto</wsa:Address>", $rawdoc);

		$this->assertEquals("testto", $wssoap->getTo());
		$this->assertEquals("testaction", $wssoap->getAction());
		$this->assertEquals("testreplyto", $wssoap->getReplyTo());
	    } //end testSetsAndGetsToReplyToAndActionFileds()


	/**
	 * Ensuring that unreachable replyto endpoints are handled properly.
	 *
	 * @return void
	 */

	public function testSetsAnonymousReplyToIfReplyToEndpointIsUnreachable()
	    {
		$document                     = new DOMDocument("1.0", "UTF-8");
		$document->preserveWhiteSpace = true;
		$document->load(__DIR__ . "/soapexample.xml");

		$wssoap = new WSASoap($document);

		$wssoap->addAction("testaction");
		$wssoap->addTo("testto");
		$wssoap->addReplyTo();

		$this->assertEquals("http://schemas.xmlsoap.org/ws/2004/08/addressing/role/anonymous", $wssoap->getReplyTo());
	    } //end testSetsAnonymousReplyToIfReplyToEndpointIsUnreachable()


    } //end class

?>
