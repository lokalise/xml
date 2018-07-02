<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;
use \DOMNodeList;
use \DOMXPath;

/**
 * DOMXPathExtended implements conveninence functions missing from stock DOMXPath
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/DOMXPathExtended.php $
 */

class DOMXPathExtended extends DOMXPath
    {

	/**
	 * Make instance of DOMXPath and register namespaces if required
	 *
	 * @param DOMDocument $doc DOMDocument to run DOMXPath over
	 *
	 * @return void
	 *
	 * @untranslatable null
	 * @untranslatable namespace::*
	 */

	public function __construct(DOMDocument $doc)
	    {
		parent::__construct($doc);

		$rootNamespace = $doc->lookupNamespaceUri($doc->namespaceURI);
		if ($rootNamespace !== null)
		    {
			$prefix = $doc->lookupPrefix($doc->namespaceURI);
			$prefix = (($prefix === null) ? "null" : $prefix);
			$this->registerNamespace($prefix, $rootNamespace);
		    }

		foreach ($this->query("namespace::*") as $node)
		    {
			$prefix = $doc->lookupPrefix($node->nodeValue);
			if ($prefix !== null)
			    {
				$this->registerNamespace($prefix, $node->nodeValue);
			    }
		    }
	    } //end __construct()


	/**
	 * Check if xpath statement yields any results
	 *
	 * @param string $xpath Statement to test
	 *
	 * @return boolean True if results are found
	 */

	public function exists($xpath)
	    {
		$list = $this->query($xpath);
		return ($list->length > 0);
	    } //end exists()


	/**
	 * Get first node value from DOMNodeList
	 *
	 * @param DOMNodeList $list Result of XPath query
	 *
	 * @return mixed node value or false
	 */

	public function getFirstItemValueFrom(DOMNodeList $list)
	    {
		return (($list->length > 0) ? $list->item(0)->nodeValue : false);
	    } //end getFirstItemValueFrom()


    } //end class

?>
