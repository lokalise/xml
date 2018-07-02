<?php

/**
 * XMLtranslator
 *
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;
use \DOMElement;
use \DOMNode;
use \DOMNodeList;
use \DOMXPath;
use \Exception;
use \SoapClient;

/**
 * XMLtranslator return translated XML
 *
 * @author    Ekaterina Bizimova <kate@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/XMLtranslator.php $
 *
 * @untranslatable xml:lang
 */

class XMLtranslator
    {

	use XMLparser, XMLfailure, XMLmapping, SchemaNametrace;

	const SERVICE_WSDL = "http://translator.logics.net.au/TranslateService.wsdl";

	/**
	 * SOAP client for connection to translation service
	 *
	 * @var SoapClient
	 */
	public $clientservice;

	/**
	 * Translation service name
	 *
	 * @var string
	 */
	private $_clientname;

	/**
	 * Tranlation service key
	 *
	 * @var string
	 */
	private $_remotekey;

	/**
	 * Translation priority to use
	 *
	 * @var string
	 */
	private $_priority;

	/**
	 * Faults encountered during translation
	 *
	 * @var array
	 */
	private $_translationFaults;

	/**
	 * Flag to signify machine translation
	 *
	 * @var bool
	 */
	private $_isItMachineTranslation;

	/**
	 * Instantiate this class
	 *
	 * @return void
	 */

	public function __construct()
	    {
		$this->clientservice = $this->getClient();
	    } //end __construct()


	/**
	 * Get client
	 *
	 * @return mixed
	 */

	protected function getClient()
	    {
		$wsdl = self::SERVICE_WSDL;
		return new SoapClient($wsdl);
	    } //end getClient()


	/**
	 * Process XML file
	 *
	 * @param string $xml        Input XML document
	 * @param string $clientName Client name
	 * @param string $remotekey  Access key
	 * @param string $priority   Priority of translation
	 * @param array  $languages  Array of languages
	 *
	 * @return string
	 *
	 * @throws Exception In case of error throw exception
	 *
	 * @exceptioncode EXCEPTION_TRANSLATION_FAULT
	 *
	 * @untranslatable utf-8
	 * @untranslatable Ymd
	 */

	public function process($xml, $clientName, $remotekey, $priority, array $languages)
	    {
		$wsdl = self::SERVICE_WSDL;

		$this->_clientname = $clientName;
		$this->_remotekey  = $remotekey;
		$this->_priority   = $priority;

		$doc = new DOMDocument("1.0", "utf-8");
		$doc->loadXML($xml);

		$xpath = new DOMXPathExtended($doc);
		$list  = $xpath->query("//*");

		foreach ($list as $node)
		    {
			$this->_removeIncompleteTranslations($node);
		    } //end foreach

		$xpath = new DOMXPathExtended($doc);
		$list  = $xpath->query("//*");

		$this->_translationFaults = array();

		foreach ($languages as $language)
		    {
			foreach ($list as $node)
			    {
				$this->_processNode($language, $node);
			    } //end foreach

			try
			    {
				$this->clientservice->commit($clientName, strtoupper(md5(date("Ymd") . strtoupper($this->_remotekey))), $priority);
			    }
			catch (Exception $e)
			    {
				$this->_translationFaults[] = _("Commit failed") . ": " . $e->getMessage();
			    }
		    } //end foreach

		if (count($this->_translationFaults) > 0)
		    {
			$faults = array_unique($this->_translationFaults);
			throw new Exception(implode("; ", $faults), EXCEPTION_TRANSLATION_FAULT);
		    }

		return $doc->saveXML();
	    } //end process()


	/**
	 * Remove incomplete translations. If say 'en' element is found two times and 'ru' only once then we cannot
	 * find out which 'en' element is not translated and so we drop all 'ru' elements and perform translation from the scratch
	 *
	 * @param DOMElement $node Node which siblings must be checked for completness
	 *
	 * @return void
	 */

	private function _removeIncompleteTranslations(DOMElement $node)
	    {
		if ($node->parentNode !== null)
		    {
			$counts = $this->_countTranslations($node);
			foreach ($counts as $language => $count)
			    {
				if ($count < max($counts))
				    {
					foreach ($node->parentNode->childNodes as $childNode)
					    {
						$lang = preg_replace("/-x-mt$/", "", $childNode->getAttribute("xml:lang"));
						if ($lang === $language)
						    {
							$node->parentNode->removeChild($childNode);
						    }
					    }
				    }
			    }
		    }
	    } //end _removeIncompleteTranslations()


	/**
	 * Count translations for particular node. Run through all siblings with same node name and count them.
	 *
	 * @param DOMElement $node Node whos siblings must be counted
	 *
	 * @return array Indexes are language and values are number of occurrences
	 */

	private function _countTranslations(DOMElement $node)
	    {
		$parent = $node->parentNode;
		$counts = array();
		foreach ($parent->childNodes as $childNode)
		    {
			if ($node->nodeName === $childNode->nodeName && $childNode->hasAttribute("xml:lang") !== false)
			    {
				$lang = preg_replace("/-x-mt$/", "", $childNode->getAttribute("xml:lang"));
				if (isset($counts[$lang]) === false)
				    {
					$counts[$lang] = 1;
				    }
				else
				    {
					$counts[$lang]++;
				    }
			    }
		    }

		return $counts;
	    } //end _countTranslations()


	/**
	 * Recurse through children
	 *
	 * @param string  $language Language
	 * @param DOMNode $node     Current translatable node
	 *
	 * @return void
	 *
	 * @untranslatable xml:lang
	 */

	private function _processNode($language, DOMNode $node)
	    {
		if ($node->parentNode !== null)
		    {
			if ($node->getAttributeNode("xml:lang") !== false)
			    {
				if ($node->getAttribute("xml:lang") !== $language)
				    {
					$currentlanguage = false;
					$counts          = $this->_countTranslations($node);
					foreach (array_keys($counts) as $lang)
					    {
						if (strpos($language, $lang) === 0)
						    {
							$currentlanguage = $lang;
						    }
					    }

					if ($currentlanguage === false || $counts[$currentlanguage] !== max($counts))
					    {
						$parent                        = $node->parentNode;
						$this->_isItMachineTranslation = false;

						$newNode = $node->cloneNode(true);
						$newNode = $this->_recurseThroughChildren($newNode, $node, $language);
						$newNode = $this->_processNewNode($newNode, $language);
						if ($node->nextSibling !== null)
						    {
							$parent->insertBefore($newNode, $node->nextSibling);
						    }
						else
						    {
							$parent->appendChild($newNode);
						    }
					    } //end if
				    } //end if
			    } //end if
		    } //end if
	    } //end _processNode()


	/**
	 * Processing newly created node.
	 *
	 * @param DOMNode $newNode  Processed node
	 * @param string  $language Current language
	 *
	 * @return DOMNode
	 *
	 * @untranslatable xml:lang
	 * @untranslatable -x-mt
	 */

	private function _processNewNode(DOMNode $newNode, $language)
	    {
		if ($this->_isItMachineTranslation === true && $newNode->hasAttributes() === true)
		    {
			$newNode->setAttribute("xml:lang", $language . "-x-mt");
		    }
		else if ($newNode->hasAttributes() === true)
		    {
			$newNode->setAttribute("xml:lang", $language);
		    }

		return $newNode;
	    } //end _processNewNode()


	/**
	 * Recurse through children
	 *
	 * @param DOMNode $newNode  Duplicated node
	 * @param DOMNode $oldNode  Current translatable node
	 * @param string  $language Language
	 *
	 * @return DOMNode
	 *
	 * @untranslatable xml:lang
	 */

	private function _recurseThroughChildren(DOMNode $newNode, DOMNode $oldNode, $language)
	    {
		if ($newNode->hasChildNodes() === true)
		    {
			foreach ($newNode->childNodes as $child)
			    {
				$this->_recurseThroughChildren($child, $oldNode, $language);
			    }
		    }
		else
		    {
			$this->_tryToTranslateNode($newNode, $oldNode->getAttribute("xml:lang"), $language);
		    } //end if

		return $newNode;
	    } //end _recurseThroughChildren()


	/**
	 * Try to translate node
	 *
	 * @param DOMNode $node         Translated node
	 * @param string  $fromLanguage Source language
	 * @param string  $toLanguage   Destination language
	 *
	 * @return void
	 *
	 * @untranslatable Ymd
	 * @untranslatable /Translate/TranslatedPhrase/text()
	 * @untranslatable /Translate/Priority/text()
	 */

	private function _tryToTranslateNode(DOMNode $node, $fromLanguage, $toLanguage)
	    {
		$s = trim($node->textContent);
		if ($s !== "")
		    {
			try
			    {
				$response = $this->clientservice->translate(
						$this->_clientname,
						strtoupper(md5(date("Ymd") . strtoupper($this->_remotekey))),
						preg_replace("/-x-mt$/", "", $fromLanguage), $toLanguage, $this->_priority, $s
					    );

				if (($response !== null) && ($response !== ""))
				    {
					$this->prepareForXMLerrors();
					$doc = new DOMDocument();
					$doc->loadXML($response);
					$errors = $this->getXMLerrors();

					if ($errors === false)
					    {
						$xpath    = new DOMXPath($doc);
						$phrase   = $xpath->query("/Translate/TranslatedPhrase/text()");
						$priority = $xpath->query("/Translate/Priority/text()");

						if ($phrase->length === 1 && $priority->length === 1)
						    {
							$node->nodeValue               = $phrase->item(0)->nodeValue;
							$this->_isItMachineTranslation = $this->_isItMachineTranslation || ($priority->item(0)->nodeValue === "1");
						    }
						else
						    {
							$this->_translationFaults[] = _("Incomplete response received");
						    }
					    }
					else
					    {
						$this->_translationFaults[] = _("Bad response received: ") . $errors;
					    } //end if
				    }
				else
				    {
					$this->_translationFaults[] = _("No response received");
				    } //end if
			    }
			catch (Exception $e)
			    {
				$this->_translationFaults[] = $e->getMessage();
			    } //end try
		    } //end if
	    } //end _tryToTranslateNode()


	/**
	 * Process XML file
	 *
	 * @param string $xml        Input XML document
	 * @param string $language   Specified language
	 * @param array  $attributes Array containing additional language attributes' names
	 *
	 * @return string
	 *
	 * @untranslatable utf-8
	 */

	public function clean($xml, $language, array $attributes = array())
	    {
		$attributes[] = "xml:lang";

		$doc = new DOMDocument("1.0", "utf-8");
		$doc->loadXML($xml);
		$xpath = new DOMXPathExtended($doc);
		$list  = $xpath->query("//*");

		foreach ($list as $listitem)
		    {
			if ($listitem instanceof DOMElement === true)
			    {
				foreach ($attributes as $attr)
				    {
					if ($listitem->hasAttribute($attr) === true)
					    {
						$this->_cleanElement($xpath, $listitem->getNodePath(), $language, $attr);
					    }
				    }
			    }
		    }

		return $doc->saveXML();
	    } //end clean()


	/**
	 * Cleans the excessive translations for specific element.
	 *
	 * @param DOMXPathExtended $xpath     XPath object
	 * @param string           $location  Location of the element in the document
	 * @param string           $language  Desired language
	 * @param string           $attribute Language attribute to search
	 *
	 * @return void
	 *
	 * @untranslatable -x-mt
	 */

	private function _cleanElement(DOMXPathExtended $xpath, $location, $language, $attribute)
	    {
		$languages = array();

		$elements = $xpath->query(preg_replace("/\[\d+\]$/", "", $location));
		foreach ($elements as $element)
		    {
			if ($element->hasAttribute($attribute) === true)
			    {
				$languages[] = $element->getAttribute($attribute);
			    }
			else
			    {
				$languages[] = "";
			    }
		    }

		if (in_array($language, $languages) === true)
		    {
			$this->_dropAllBut($elements, $attribute, $language);
		    }
		else if (in_array($language . "-x-mt", $languages) === true)
		    {
			$this->_dropAllBut($elements, $attribute, $language . "-x-mt");
		    }
		else if (count($languages) > 0)
		    {
			sort($languages);
			$this->_dropAllBut($elements, $attribute, array_shift($languages));
		    }
	    } //end _cleanElement()


	/**
	 * Drop all DOM elements except one with appropriate language tag
	 *
	 * @param DOMNodeList $elements  Semantically same elements but in different languages
	 * @param string      $attribute Attribute containing LanguageID
	 * @param string      $language  LanguageID which should remain
	 *
	 * @return void
	 */

	private function _dropAllBut(DOMNodeList $elements, $attribute, $language)
	    {
		foreach ($elements as $element)
		    {
			if (($element->hasAttribute($attribute) === true && $element->getAttribute($attribute) !== $language) ||
			    ($element->hasAttribute($attribute) === false && $language !== ""))
			    {
				$element->parentNode->removeChild($element);
			    }
			else if (preg_match("/(?P<lang>.*)-x-mt$/", $language, $m) > 0)
			    {
				$element->setAttribute($attribute, $m["lang"]);
			    }
		    }
	    } //end _dropAllBut()


    } //end class

?>
