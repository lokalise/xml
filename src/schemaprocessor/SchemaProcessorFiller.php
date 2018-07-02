<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMElement;

/**
 * SchemaProcessorFiller class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/schemaprocessor/SchemaProcessorFiller.php $
 */

class SchemaProcessorFiller
    {

	/**
	 * Schema prefix
	 *
	 * @var string
	 */
	protected $schemaPrefix;

	/**
	 * Istaniate class instance.
	 *
	 * @param string $prefix Schema prefix
	 *
	 * @return void
	 */

	public function __construct($prefix)
	    {
		$this->schemaPrefix = $prefix;
	    } //end __construct()


	/**
	 * Pushing elements to the corresponding section of schema keeper.
	 *
	 * @param array      $schemakeeper     Schema keeper instance
	 * @param DOMElement $schemaelement    Current schema node
	 * @param array      $childrenarray    Array of children
	 * @param array      $customattributes Attributes that need to be appended to the element array
	 *
	 * @return array
	 *
	 * @untranslatable element
	 * @untranslatable name
	 */

	public function pushToElementsArray(array $schemakeeper, DOMElement $schemaelement, array $childrenarray, array $customattributes = array())
	    {
		$nodepath = $schemaelement->getNodePath();
		$key      = "element" . md5($nodepath);

		$schemakeeper[$this->schemaPrefix . "element"][$key]          = array();
		$schemakeeper[$this->schemaPrefix . "element"][$key]["xpath"] = $nodepath;
		$schemakeeper[$this->schemaPrefix . "element"][$key]["type"]  = $this->schemaPrefix . "element";

		$childrentoput = array();
		foreach ($childrenarray as $child)
		    {
			if (empty($child) === false)
			    {
				$childrentoput[] = $child;
			    }
		    } //end foreach

		$schemakeeper[$this->schemaPrefix . "element"][$key]["children"] = $childrentoput;

		foreach ($customattributes as $attributename => $attributevalue)
		    {
			$schemakeeper[$this->schemaPrefix . "element"][$key][$attributename] = $attributevalue;
		    } //end foreach

		$schemakeeper[$this->schemaPrefix . "element"][$key]["attributes"] = array();
		foreach ($schemaelement->attributes as $attributename => $attributenode)
		    {
			$attributevalue = $attributenode->value;
			$schemakeeper[$this->schemaPrefix . "element"][$key]["attributes"][$attributename] = $attributevalue;
			if ($attributename === "name")
			    {
				$schemakeeper[$this->schemaPrefix . "element"][$key]["name"] = $attributevalue;
			    } //end if
		    } //end foreach

		$schemakeeperlocation = array(
					 "type"  => $this->schemaPrefix . "element",
					 "xpath" => $nodepath,
					 "id"    => $key,
					);

		$schemakeeper[$this->schemaPrefix . "element"][$key]["schemakeeperlocation"] = $schemakeeperlocation;

		return array(
			"schemakeeperlocation" => $schemakeeperlocation,
			"schemakeeper"         => $schemakeeper,
		       );
	    } //end pushToElementsArray()


	/**
	 * Pushing elements to the corresponding section of schema keeper.
	 *
	 * @param array      $schemakeeper     Schema keeper instance
	 * @param DOMElement $schemaelement    Current schema node
	 * @param array      $childrenarray    Array of children
	 * @param array      $customattributes Attributes that need to be appended to the element array
	 *
	 * @return array
	 *
	 * @untranslatable choice
	 * @untranslatable choice:
	 */

	public function pushToChoicesArray(array $schemakeeper, DOMElement $schemaelement, array $childrenarray, array $customattributes = array())
	    {
		$nodepath = $schemaelement->getNodePath();
		$key      = "choice" . md5($nodepath);

		$schemakeeper[$this->schemaPrefix . "choice"][$key]               = array();
		$schemakeeper[$this->schemaPrefix . "choice"][$key]["name"]       = "choice:" . md5($nodepath);
		$schemakeeper[$this->schemaPrefix . "choice"][$key]["xpath"]      = $nodepath;
		$schemakeeper[$this->schemaPrefix . "choice"][$key]["type"]       = ($this->schemaPrefix . "choice");
		$schemakeeper[$this->schemaPrefix . "choice"][$key]["children"]   = $childrenarray;
		$schemakeeper[$this->schemaPrefix . "choice"][$key]["attributes"] = $customattributes;

		$schemakeeperlocation = array(
					 "type"  => $this->schemaPrefix . "choice",
					 "xpath" => $nodepath,
					 "id"    => $key,
					);

		$schemakeeper[$this->schemaPrefix . "choice"][$key]["schemakeeperlocation"] = $schemakeeperlocation;

		return array(
			"schemakeeperlocation" => $schemakeeperlocation,
			"schemakeeper"         => $schemakeeper,
		       );
	    } //end pushToChoicesArray()


    } //end class

?>
