<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * SchemaExtendedPath class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaExtendedPath.php $
 */

class SchemaExtendedPath extends SchemaProcessor
    {

	use SchemaNametrace;

	/**
	 * Fetches child nodes using given nametrace.
	 *
	 * @param string $extendednametrace Provided nametrace
	 *
	 * @return array
	 *
	 * @untranslatable schema/
	 * @untranslatable element
	 * @untranslatable schema
	 */

	public function stepdown($extendednametrace = null)
	    {
		if ($extendednametrace === null)
		    {
			$rootelements = $this->schemaDocumentXPath->query("/" . $this->schemaPrefix . "schema/" . $this->schemaPrefix . "element");
			$rootitem     = $rootelements->item(0);

			$correspondingelement = $this->schemaKeeper[$this->schemaPrefix . "element"]["element" . md5($rootitem->getNodePath())];
			return $correspondingelement;
		    }
		else
		    {
			$extendednametracenoschema   = str_replace("/" . $this->schemaPrefix . "schema/", "", $extendednametrace);
			$normilizedextendednametrace = $this->_setNametraceIndexes($extendednametracenoschema);
			$splittedextendednametrace   = explode("/", $normilizedextendednametrace);

			$schemaelement    = current($this->schemaKeeper[$this->schemaPrefix . "schema"]);
			$elementtoprocess = $this->getItem($schemaelement, $splittedextendednametrace[0]);

			$modifiedextendeddocumentnametrace = substr($normilizedextendednametrace, (strpos($normilizedextendednametrace, "/") + 1));
			return $this->stepdownIterate($elementtoprocess, $modifiedextendeddocumentnametrace, $schemaelement);
		    } //end if
	    } //end stepdown()


	/**
	 * Recursive routine for walking down the provided nametrace.
	 *
	 * @param array  $element           Container element
	 * @param string $relativenametrace Analyzed nametrace
	 *
	 * @return array
	 */

	protected function stepdownIterate(array $element, $relativenametrace)
	    {
		if ($relativenametrace === "")
		    {
			$key         = 0;
			$returnarray = array();
			foreach ($element["children"] as $child)
			    {
				$key++;
				$correspondingelement = $this->schemaKeeper[$child["type"]][$child["id"]];
				$returnarray[$key]    = $correspondingelement;
			    } //end foreach

			return $returnarray;
		    }
		else
		    {
			$splittedextendednametrace = explode("/", $relativenametrace);
			$firstelementname          = $splittedextendednametrace[0];

			$elementtoprocess = $this->getItem($element, $firstelementname);

			$modifiedrelativenametrace = substr($relativenametrace, (strpos($relativenametrace, "/") + 1),
			(strlen($relativenametrace) - strpos($relativenametrace, "/") - 1));
			if (substr_count($relativenametrace, "/") === 0)
			    {
				$modifiedrelativenametrace = "";
			    } //end if

			return $this->stepdownIterate($elementtoprocess, $modifiedrelativenametrace, $element);
		    } //end if
	    } //end stepdownIterate()


    } //end class

?>
