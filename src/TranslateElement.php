<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMElement;
use \DOMNodeList;
use \DOMText;
use \Locale;

/**
 * Trait for translating provided NodeList according to the locale settings
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/TranslateElement.php $
 */

trait TranslateElement
    {

	use DOMDocumentTools;

	/**
	 * Translates given element according to the locale settings, returns cleaned up element without extra translations for translated element.
	 *
	 * @param DOMElement $rootelement Element, that contains elements for translation
	 * @param bool       $recursive   Specifies if we need to translate down the XML structure
	 *
	 * @return DOMNodeList
	 *
	 * @untranslatable xml
	 * @untranslatable xml:lang
	 */

	protected function translateElement(DOMElement $rootelement, $recursive = false)
	    {
		$xpath = new DOMXPathExtended($rootelement->ownerDocument);
		$xpath->registerNamespace("xml", "http://www.w3.org/XML/1998/namespace");

		$occurencearray = $this->_getOccurenceArray($rootelement, $recursive);
		foreach ($occurencearray as $key => $nodes)
		    {
			if (count($nodes) > 1)
			    {
				$defaultelementoccured = false;
				foreach ($nodes as $node)
				    {
					if ($node->hasAttribute("xml:lang") === false)
					    {
						if ($defaultelementoccured === true)
						    {
							break;
						    }

						$defaultelementoccured = true;
					    }
				    } //end foreach

				$selectedlanguage = $this->_getSelectedElementForSetOfElements($xpath->query(".//" . $key));
				foreach ($nodes as $node)
				    {
					if ($node->getAttribute("xml:lang") !== $selectedlanguage)
					    {
						$node->parentNode->removeChild($node);
					    }
				    } //end foreach
			    } //end if
		    } //end foreach

		return $rootelement;
	    } //end translateElement()


	/**
	 * Getting resulting language for set of similar elements.
	 *
	 * @param DOMNodeList $suitableelements Set of elements
	 *
	 * @return string
	 *
	 * @optionalconst LANGUAGE "en"    Language to be used by the script
	 * @optionalconst LOCALE   "en_AU" Locale to be used by the script
	 *
	 * @untranslatable -x-mt
	 * @untranslatable en
	 * @untranslatable xml:lang
	 */

	private function _getSelectedElementForSetOfElements(DOMNodeList $suitableelements)
	    {
		$langugagehierarchy = array(
				       LANGUAGE,
				       LANGUAGE . "-x-mt",
				       Locale::getPrimaryLanguage(LOCALE),
				       Locale::getPrimaryLanguage(LOCALE) . "-x-mt",
				       "en",
				       "",
				      );

		$selectedlanguage = "";
		foreach ($langugagehierarchy as $currentlanguage)
		    {
			foreach ($suitableelements as $suitableelement)
			    {
				if ($suitableelement->getAttribute("xml:lang") === $currentlanguage)
				    {
					$selectedlanguage = $currentlanguage;
					break 2;
				    } //end if
			    } //end foreach
		    } //end foreach

		return $selectedlanguage;
	    } //end _getSelectedElementForSetOfElements()


	/**
	 * Returns the array that specifies how many times every node occured in the node's child list.
	 *
	 * @param DOMElement $rootelement Root element of the settings document
	 * @param bool       $recursive   Specifies if we need to translate down the XML structure
	 *
	 * @return array
	 */

	private function _getOccurenceArray(DOMElement $rootelement, $recursive)
	    {
		$countarray = array();
		$children   = $this->getFirstLevelChildNodes($rootelement);
		foreach ($children as $child)
		    {
			$nodename = $child->nodeName;
			if ($child->childNodes->length === 1 && $child->childNodes->item(0) instanceof DOMText === true)
			    {
				if (isset($countarray[$nodename]) === true)
				    {
					$countarray[$nodename][] = $child;
				    }
				else
				    {
					$countarray[$nodename]   = array();
					$countarray[$nodename][] = $child;
				    }
			    }
			else if ($recursive === true)
			    {
				$countarray = array_merge($countarray, $this->_getOccurenceArray($child, $recursive));
			    } //end if
		    } //end foreach

		return $countarray;
	    } //end _getOccurenceArray()


    } //end trait

?>
