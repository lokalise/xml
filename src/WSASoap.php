<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;

/**
 * WSA capabilities
 *
 * @author    Robert Richards <rrichards@ctindustries.net>
 * @author    Kévin Dunglas <kevin@les-tilleuls.coop>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2007 Robert Richards <rrichards@ctindustries.net>
 * @copyright 2013 La Coopérative des Tilleuls <contact@les-tilleuls.coop>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/WSASoap.php $
 *
 * @untranslatable wsa
 */

class WSASoap
    {

	const WSANS = "http://www.w3.org/2005/08/addressing";

	const WSAPFX = "wsa";

	/**
	 * SOAP namespace
	 *
	 * @var string
	 */
	private $_soapNS;

	/**
	 * SOAP namespace prefix
	 *
	 * @var string
	 */
	private $_soapPFX;

	/**
	 * SOAP document
	 *
	 * @var DOMDocument
	 */
	private $_soapDoc = false;

	/**
	 * SOAP envelope
	 *
	 * @var DOMElement
	 */
	private $_envelope = false;

	/**
	 * SOAP XPath object
	 *
	 * @var DOMXPathExtended
	 */
	private $_soapXPath = false;

	/**
	 * Header
	 *
	 * @var DOMNode
	 */
	private $_header = false;

	/**
	 * Message ID
	 *
	 * @var string
	 */
	private $_messageID = false;

	/**
	 * Initiate the class
	 *
	 * @param \DOMDocument $doc Initial SOAP document
	 *
	 * @return void
	 */

	public function __construct(DOMDocument $doc)
	    {
		$this->_soapDoc   = $doc;
		$this->_envelope  = $doc->documentElement;
		$this->_soapNS    = $this->_envelope->namespaceURI;
		$this->_soapPFX   = $this->_envelope->prefix;
		$this->_soapXPath = new DOMXPathExtended($doc);

		$this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:" . self::WSAPFX, self::WSANS);
	    } //end __construct()


	/**
	 * Adds the MessageID.
	 *
	 * @return void
	 *
	 * @untranslatable :MessageID
	 * @untranslatable uuid:
	 */

	private function _addMessageId()
	    {
		$uuid = md5(uniqid(rand(), true));
		$guid = "uuid:" . substr($uuid, 0, 8) . "-" .
		    substr($uuid, 8, 4) . "-" .
		    substr($uuid, 12, 4) . "-" .
		    substr($uuid, 16, 4) . "-" .
		    substr($uuid, 20, 12);

		$header = $this->_locateHeader();

		$nodeID = $this->_soapDoc->createElementNS(self::WSANS, self::WSAPFX . ":MessageID", $guid);
		$header->appendChild($nodeID);
		$this->_messageID = $guid;
	    } //end _addMessageId()


	/**
	 * Locates header section.
	 *
	 * @return string
	 *
	 * @untranslatable //S:Envelope/S:Header
	 */

	private function _locateHeader()
	    {
		if ($this->_header === false)
		    {
			$headers = $this->_soapXPath->query("//S:Envelope/S:Header");
			$header  = $headers->item(0);

			$this->_header = $header;
		    }

		return $this->_header;
	    } //end _locateHeader()


	/**
	 * Adds the WSA Action
	 *
	 * @param string $action Setted action
	 *
	 * @return void
	 *
	 * @untranslatable :Action
	 */

	public function addAction($action)
	    {
		$header = $this->_locateHeader();

		$nodeAction = $this->_soapDoc->createElementNS(self::WSANS, self::WSAPFX . ":Action", $action);
		$header->appendChild($nodeAction);
	    } //end addAction()


	/**
	 * Using magic methods for getting properties.
	 *
	 * @param string $name      Name of the called method
	 * @param array  $arguments Method arguments
	 *
	 * @return mixed String or false if no action was found
	 *
	 * @untranslatable Action
	 * @untranslatable getAction
	 * @untranslatable To
	 * @untranslatable getTo
	 * @untranslatable ReplyTo
	 * @untranslatable getReplyTo
	 */

	public function __call($name, array $arguments)
	    {
		unset($arguments);
		$paramname = false;
		if ($name === "getAction")
		    {
			$paramname = "Action";
		    }
		else if ($name === "getTo")
		    {
			$paramname = "To";
		    }
		else if ($name === "getReplyTo")
		    {
			$paramname = "ReplyTo";
		    }

		$header = $this->_locateHeader();
		$nodes  = $this->_soapXPath->query("//" . self::WSAPFX . ":" . $paramname, $header);
		if ($nodes->length === 1)
		    {
			return $nodes->item(0)->textContent;
		    }
		else
		    {
			return false;
		    }
	    } //end __call()


	/**
	 * Adds the WSA To
	 *
	 * @param string $location Setted location
	 *
	 * @return void
	 *
	 * @untranslatable :To
	 */

	public function addTo($location)
	    {
		$header = $this->_locateHeader();

		$nodeTo = $this->_soapDoc->createElementNS(self::WSANS, self::WSAPFX . ":To", $location);
		$header->appendChild($nodeTo);
	    } //end addTo()


	/**
	 * Adds the WSA Reply To
	 *
	 * @param string $address Added address
	 *
	 * @return void
	 *
	 * @untranslatable :ReplyTo
	 * @untranslatable :Address
	 */

	public function addReplyTo($address = false)
	    {
		if ($this->_messageID === false)
		    {
			$this->_addMessageId();
		    }

		$header = $this->_locateHeader();

		$nodeReply = $this->_soapDoc->createElementNS(self::WSANS, self::WSAPFX . ":ReplyTo");
		$header->appendChild($nodeReply);

		if ($address === false)
		    {
			$address = "http://schemas.xmlsoap.org/ws/2004/08/addressing/role/anonymous";
		    }

		$nodeAddress = $this->_soapDoc->createElementNS(self::WSANS, self::WSAPFX . ":Address", $address);
		$nodeReply->appendChild($nodeAddress);
	    } //end addReplyTo()


	/**
         * Gets the DOM document
         *
         * @return \DOMDocument
	 */

	public function getDoc()
	    {
		return $this->_soapDoc;
	    } //end getDoc()


    } //end class

?>
