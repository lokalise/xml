<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;

/**
 * XMLdiff
 *
 * @author    Kate Bizimova <kate@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/XMLdiff.php $
 */

class XMLdiff extends \XMLDiff\Memory
    {

	/**
	 * Computes the difference for two XML documents.
	 *
	 * @param string $basexml     Base document
	 * @param string $comparedxml Compared document
	 *
	 * @return string Document with the computed difference
	 *
	 * @untranslatable //dm:delete/*
	 * @untranslatable and
	 */

	public function diff($basexml, $comparedxml)
	    {
		$dom = new DOMDocument();
		$dom->loadXML(parent::diff($basexml, $comparedxml));

		$xpath   = new DOMXPathExtended($dom);
		$deletes = $xpath->query("//dm:delete/*");
		foreach ($deletes as $node)
		    {
			$query          = "//" . $node->nodeName . "[";
			$firstattribute = true;
			foreach ($node->attributes as $attribute)
			    {
				if ($firstattribute === false)
				    {
					$query .= " and ";
				    }

				$query         .= "@" . $attribute->nodeName . "=\"" . $attribute->nodeValue . "\"";
				$firstattribute = false;
			    }

			$query   .= "]";
			$document = new DOMDocument();
			$document->loadXML($basexml);
			$xpathdoc = new DOMXPathExtended($document);
			$list     = $xpathdoc->query($query);

			foreach ($list as $originalnode)
			    {
				$nodeimport = $dom->importNode($originalnode, true);
				$node->parentNode->replaceChild($nodeimport, $node);
			    }
		    } //end foreach

		$diff = $dom->saveXML();
		return $diff;
	    } //end diff()


	/**
	 * Validate document
	 *
	 * @param string $newxml  Document to validate
	 * @param string $diffxml Array containing document types and file names with relevant schemas
	 *
	 * @return mixed
	 *
	 * @untranslatable //dm:insert
	 * @untranslatable //dm:delete
	 */

	public function mergereverse($newxml, $diffxml)
	    {
		$dom = new DOMDocument();
		$dom->loadXML($diffxml);

		$xpath   = new DOMXPathExtended($dom);
		$inserts = $xpath->query("//dm:insert");
		$deletes = $xpath->query("//dm:delete");

		foreach ($inserts as $node)
		    {
			$newdelete = $dom->createElement("dm:delete");
			$children  = $node->childNodes;
			foreach ($children as $child)
			    {
				$newdelete->appendChild($child->cloneNode(true));
			    }

			$node->parentNode->replaceChild($newdelete, $node);
		    }

		foreach ($deletes as $node)
		    {
			$newdelete = $dom->createElement("dm:insert");
			$children  = $node->childNodes;
			foreach ($children as $child)
			    {
				$newdelete->appendChild($child->cloneNode(true));
			    }

			$node->parentNode->replaceChild($newdelete, $node);
		    }

		$diffxml = $dom->saveXML();
		return $this->merge($newxml, $diffxml);
	    } //end mergereverse()


    } //end class

?>
