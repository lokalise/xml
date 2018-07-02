<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * SchemaFlattenElementsArrayFilter class for filtering middleware element array during flattening.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaFlattenElementsArrayFilter.php $
 */

class SchemaFlattenElementsArrayFilter
    {

	use SchemaNametrace;

	/**
	 * Flattened document array
	 *
	 * @var array
	 */
	private $_flattenedDocumentArray;

	/**
	 * Possible nametraces
	 *
	 * @var array
	 */
	private $_possibleNametraces;

	/**
	 * Instantiate this class
	 *
	 * @param string $schemafilepath Filepath to the schema file
	 *
	 * @return void
	 */

	public function __construct($schemafilepath)
	    {
		$this->_schemafilepath = $schemafilepath;
		$this->_fillPossibleNametraces();
	    } //end __construct()


	/**
	 * Filtering and normalizing middleware elements array.
	 *
	 * @param array $elementsarray Set of elements
	 *
	 * @return array
	 */

	public function filter(array $elementsarray)
	    {
		$this->_flattenedDocumentArray = $elementsarray;
		$this->_checkNamesForUniqueness();
		$this->_checkNamesForHierarchyUniquity();
		return $this->_flattenedDocumentArray;
	    } //end filter()


	/**
	 * Checking names in resulting array for uniqueness.
	 *
	 * @return void
	 */

	private function _checkNamesForUniqueness()
	    {
		foreach ($this->_flattenedDocumentArray as $flattenedkey => $flatteneditems)
		    {
			foreach ($flatteneditems as $key => $flatteneditem)
			    {
				$clearedname = $this->_deleteNametraceIndexes($flatteneditem["name"]);
				$conflicts   = array();
				foreach ($this->_possibleNametraces as $possiblenametrace)
				    {
					if (substr($possiblenametrace, -strlen($clearedname)) === ($clearedname))
					    {
						if (substr($possiblenametrace, (-strlen($clearedname) - 1), 1) === "/")
						    {
							$conflicts[] = $possiblenametrace;
						    } //end if
					    } //end if
				    } //end foreach

				if (count($conflicts) > 1)
				    {
					$optimalname           = $this->_getOptimalName($conflicts, $flatteneditem);
					$flatteneditem["name"] = $optimalname;

					$this->_flattenedDocumentArray[$flattenedkey][$key] = $flatteneditem;
				    } //end if
			    } //end foreach
		    } //end foreach
	    } //end _checkNamesForUniqueness()


	/**
	 * Makes nametraces more readable.
	 *
	 * @return void
	 */

	private function _checkNamesForHierarchyUniquity()
	    {
		foreach ($this->_flattenedDocumentArray as $flattenedkey => $flatteneditem)
		    {
			foreach ($flatteneditem as $subflattenedkey => $flattenedsubitem)
			    {
				if (substr_count($flattenedsubitem["name"], "/") === 0)
				    {
					$explodednametrace = explode("/", $flattenedsubitem["nametrace"]);
					foreach ($explodednametrace as $key => $nametraceitem)
					    {
						if (substr_count($nametraceitem, "[") === 1)
						    {
							array_splice($explodednametrace, 0, $key);
							$this->_flattenedDocumentArray[$flattenedkey][$subflattenedkey]["name"] = implode("/", $explodednametrace);
						    } //end if
					    } //end foreach
				    } //end if
			    } //end foreach
		    } //end foreach
	    } //end _checkNamesForHierarchyUniquity()


	/**
	 * Returns the name for the element that uniquely identifies current element.
	 *
	 * @param array  $conflicts     Set of name conflicts
	 * @param string $flatteneditem Flattened item description
	 *
	 * @return string
	 */

	private function _getOptimalName(array $conflicts, $flatteneditem)
	    {
		$fullitemnametrace = $flatteneditem["nametrace"];

		$explodedoriginalfullitemnametrace = explode("/", $fullitemnametrace);
		$explodedfullitemnametrace         = explode("/", $this->_deleteNametraceIndexes($fullitemnametrace));

		$numberofcompareditems = count(explode("/", $flatteneditem["name"]));
		$numberofconflicts     = count($conflicts);
		while ($numberofconflicts > 1)
		    {
			$temporaryoriginalarray = $explodedoriginalfullitemnametrace;
			$temporaryarray         = $explodedfullitemnametrace;

			$itemnametracepartoriginal = implode("/", array_splice($temporaryoriginalarray, (-1 * $numberofcompareditems), $numberofcompareditems));
			$itemnametracepart         = implode("/", array_splice($temporaryarray, (-1 * $numberofcompareditems), $numberofcompareditems));

			foreach ($conflicts as $conflictkey => $conflictnametrace)
			    {
				$explodedconflictnametrace = explode("/", $conflictnametrace);
				$conflictitemnametracepart = implode("/", array_splice($explodedconflictnametrace, (-1 * $numberofcompareditems), $numberofcompareditems));

				if ($conflictitemnametracepart !== $itemnametracepart)
				    {
					unset($conflicts[$conflictkey]);
				    }
			    } //end foreach

			$trynumberofunqiqueelements = count(array_unique($conflicts));
			if ($trynumberofunqiqueelements === 1)
			    {
				break;
			    } //end if

			$numberofconflicts = count($conflicts);
			$numberofcompareditems++;
		    } //end while

		return $itemnametracepartoriginal;
	    } //end _getOptimalName()


	/**
	 * Filling possible nametrace array for current schema.
	 *
	 * @return void
	 */

	private function _fillPossibleNametraces()
	    {
		$this->_descriptor = new SchemaNametraceDescriptor($this->_schemafilepath);

		$rootelements = $this->_descriptor->describe();
		foreach ($rootelements as $rootelement)
		    {
			$this->_fillPossibleNametracesIterate($rootelement, $rootelement["name"]);
		    } //end foreach
	    } //end _fillPossibleNametraces()


	/**
	 * Iteration routine for collectin possible nametraces.
	 *
	 * @param array  $elementdata Element description
	 * @param string $nametrace   Current nametrace
	 *
	 * @return void
	 */

	private function _fillPossibleNametracesIterate(array $elementdata, $nametrace)
	    {
		$this->_possibleNametraces[] = $this->_removeChoicesFromNametrace($nametrace);

		$element = $this->_descriptor->getSchemaElement($elementdata["schemakeeperlocation"]["type"], $elementdata["schemakeeperlocation"]["id"]);
		if (isset($element["children"]["branches"]) === true)
		    {
			foreach ($element["children"]["branches"] as $choicebranchelements)
			    {
				foreach ($choicebranchelements as $choicebranchelement)
				    {
					$this->_fillPossibleNametracesIterate($choicebranchelement, $nametrace . "/" . $choicebranchelement["name"]);
				    } //end foreach
			    } //end foreach
		    }
		else
		    {
			foreach ($element["children"] as $child)
			    {
				$this->_fillPossibleNametracesIterate($child, $nametrace . "/" . $child["name"]);
			    }
		    } //end if
	    } //end _fillPossibleNametracesIterate()


    } //end class

?>
