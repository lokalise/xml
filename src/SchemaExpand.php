<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMElement;
use \Exception;

/**
 * SchemaExpand class. Expanding sets of element description into the XML document
 * according to specified XSD Schema.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaExpand.php $
 */

class SchemaExpand
    {

	use SchemaNametrace;

	use XMLerrors;

	/**
	 * Schema file name
	 *
	 * @var string
	 */
	private $_schemafile;

	/**
	 * Schema nametrace descriptor
	 *
	 * @var SchemaNametraceDescriptor
	 */
	private $_descriptor;

	/**
	 * Expanded document in DOM form
	 *
	 * @var DOMDocument
	 */
	private $_expandedDocument;

	/**
	 * Decision array
	 *
	 * @var array
	 */
	private $_decisionArray = array();

	/**
	 * Base path
	 *
	 * @var string
	 */
	private $_pathhint;

	/**
	 * Input items
	 *
	 * @var array
	 */
	private $_inputitems;

	/**
	 * Item keys
	 *
	 * @var array
	 */
	private $_keys;

	/**
	 * Potential locations and corresponding elements
	 *
	 * @var array
	 */
	private $_potentials;

	/**
	 * Start the instance of the class.
	 *
	 * @param string $schemafile File path of the schema file
	 *
	 * @return void
	 */

	public function __construct($schemafile)
	    {
		$this->_schemafile = $schemafile;
		$this->_descriptor = new SchemaNametraceDescriptor($this->_schemafile);
	    } //end __construct()


	/**
	 * Get current schema ID
	 *
	 * @return string Schema ID
	 */

	public function getSchemaID()
	    {
		return $this->_descriptor->getSchemaID();
	    } //end getSchemaID()


	/**
	 * Returns the expanded document
	 *
	 * @param array  $inputitems Contains description of input items in the form of array
	 * @param int    $rootindex  Index of the chosen root element
	 * @param string $pathhint   Document path hint
	 *
	 * @return DOMDocument
	 *
	 * @throws Exception Unable to expand
	 * @throws Exception Root index is not valid
	 *
	 * @exceptioncode EXCEPTION_UNABLE_TO_EXPAND
	 * @exceptioncode EXCEPTION_ROOT_INDEX_NOT_VALID
	 */

	public function getExpandedDocument(array $inputitems, $rootindex = -1, $pathhint = "")
	    {
		if (is_integer($rootindex) === false)
		    {
			throw new Exception(_("Provided root index is invalid"), EXCEPTION_ROOT_INDEX_NOT_VALID);
		    }

		$this->_inputitems = $inputitems;
		$this->_pathhint   = $pathhint;

		$rootelements = $this->_descriptor->describe();

		$root = false;
		if ($rootindex === -1)
		    {
			$found     = false;
			$ambiguous = false;
			$errors    = "";
			foreach ($rootelements as $key => $rootelement)
			    {
				try
				    {
					$this->_tryRoot($rootelement);
					$root = $this->_buildDocument($rootelement);
					if ($found === true)
					    {
						$ambiguous = true;
						break;
					    }

					$found     = true;
					$rootindex = $key;
				    }
				catch (Exception $e)
				    {
					// We just intercept all exceptions because it means that we cannot build valid document in this case.
					$errors .= (($errors === "") ? "" : "; ") . $e->getMessage();
				    }
			    } //end foreach

			if ($found === false)
			    {
				throw new Exception(_("Unable to expand, attempted all possible roots") . ": " . $errors, EXCEPTION_UNABLE_TO_EXPAND);
			    }
			else if ($ambiguous === true)
			    {
				throw new Exception(_("Unable to expand, multiple possible roots") . ": " . $errors, EXCEPTION_UNABLE_TO_EXPAND);
			    }
		    }
		else
		    {
			$this->_tryRoot($rootelements[$rootindex]);
			$root = $this->_buildDocument($rootelements[$rootindex]);
		    } //end if

		return $root;
	    } //end getExpandedDocument()


	/**
	 * Try to collect items for particluar root
	 *
	 * @param array $root Specific root
	 *
	 * @return void
	 *
	 * @throws Exception Cannot build document from input items
	 *
	 * @exceptioncode EXCEPTION_UNUSED_ITEMS
	 */

	private function _tryRoot(array $root)
	    {
		$this->_potentials = array();

		$this->_keys = array();
		foreach (array_keys($this->_inputitems) as $key)
		    {
			$bits = explode("/", $this->_deleteNametraceIndexes($key));
			$last = array_pop($bits);

			$this->_keys[$last][] = array(
						 "key"  => $key,
						 "used" => false,
						);
		    }

		$this->_lookForPotentialElements($root, $this->_setNametraceIndexes($root["name"]));

		$notused = array();
		foreach ($this->_keys as $kind)
		    {
			foreach ($kind as $item)
			    {
				if ($item["used"] === false)
				    {
					$notused[] = $item["key"];
				    }
			    }
		    }

		if (count($notused) > 0)
		    {
			throw new Exception(_("Following items are unused") . ": " . implode(", ", $notused), EXCEPTION_UNUSED_ITEMS);
		    }

		$this->_filterPotentialsByPathHint();
		$this->_filterPotentialsByExactMatch();
	    } //end _tryRoot()


	/**
	 * Filter potential elements by path hint
	 *
	 * @return void
	 */

	private function _filterPotentialsByPathHint()
	    {
		if ($this->_pathhint !== "")
		    {
			$counts = array();
			foreach ($this->_potentials as $nametrace => $key)
			    {
				if (isset($counts[$key]) === false)
				    {
					$counts[$key] = 1;
				    }
				else
				    {
					$counts[$key]++;
				    }
			    }

			$keys = explode("/", $this->_pathhint);
			foreach ($keys as $idx => $key)
			    {
				if ($key === "")
				    {
					unset($keys[$idx]);
				    }
			    }

			$keys = array_values($keys);
			foreach ($counts as $index => $count)
			    {
				if ($count > 1)
				    {
					foreach ($this->_potentials as $nametrace => $key)
					    {
						if ($key === $index)
						    {
							$bits  = explode("/", $this->_deleteNametraceIndexes($this->_removeChoicesFromNametrace($nametrace)));
							$match = true;
							foreach ($keys as $idx => $key)
							    {
								if ($key !== $bits[$idx])
								    {
									$match = false;
								    }
							    }

							if ($match === false)
							    {
								unset($this->_potentials[$nametrace]);
							    }
						    }
					    }
				    } //end if
			    } //end foreach
		    } //end if
	    } //end _filterPotentialsByPathHint()


	/**
	 * Filter potential elements by exact match
	 *
	 * If there two or more potentially matching elements then we should use only those which match exactly to key nametrace
	 *
	 * @return void
	 */

	private function _filterPotentialsByExactMatch()
	    {
		$keys = array();

		foreach ($this->_potentials as $nametrace => $key)
		    {
			$match                = $this->_keyMatch($this->_setNametraceIndexes($key), $nametrace);
			$keys[$key][$match][] = $nametrace;
		    }

		$this->_potentials = array();
		foreach ($keys as $key => $matches)
		    {
			ksort($matches);
			$matches = array_shift($matches);
			foreach ($matches as $match)
			    {
				$this->_potentials[$match] = $key;
			    }
		    }
	    } //end _filterPotentialsByExactMatch()


	/**
	 * Iterates down the schema representation. Gets the element using provided nametrace (with indexes),
	 * iterates over its children.
	 *
	 * @param array  $currentelementshorteneddata Shortened element description array - contains data only
	 *                                            for nametrace construction and retrieving of full element data
	 * @param string $currentnametrace            Current nametrace
	 *
	 * @return void
	 *
	 * @untranslatable leafelement
	 * @untranslatable hierarchyelement
	 */

	private function _lookForPotentialElements(array $currentelementshorteneddata, $currentnametrace)
	    {
		$cyclewasdetected = false;

		$nametraceitems = explode("/", $currentnametrace);
		foreach ($nametraceitems as $key => $nametraceitem)
		    {
			$nametraceitems[$key] = $this->_removeLastNametraceIndex($nametraceitem);
		    }

		$lastitem = array_pop($nametraceitems);
		foreach ($nametraceitems as $nametraceitem)
		    {
			if ($nametraceitem === $lastitem)
			    {
				$cyclewasdetected = true;
			    }
		    }

		if ($cyclewasdetected === false)
		    {
			$currentelement = $this->_descriptor->getSchemaElement(
			    $currentelementshorteneddata["schemakeeperlocation"]["type"],
			    $currentelementshorteneddata["schemakeeperlocation"]["id"]
			);

			if ($currentelement["elementtype"] === "leafelement")
			    {
				$this->_findPotentialElements($currentnametrace);
			    }
			else
			    {
				$nolastindexnametrace   = $this->_removeLastNametraceIndex($currentnametrace);
				$numberofhierarchynodes = $this->_getNumberOfHierarchyNodes($nolastindexnametrace);

				for ($i = 1; $i <= $numberofhierarchynodes; $i++)
				    {
					$currentnametrace = $nolastindexnametrace . "[" . $i . "]";
					$this->_findPotentialElements($currentnametrace);
					if ($currentelement["elementtype"] === "hierarchyelement")
					    {
						$branches = array($currentelement["children"]);
					    }
					else
					    {
						$branches = $currentelement["children"]["branches"];
					    } //end if

					foreach ($branches as $branchelements)
					    {
						foreach ($branchelements as $child)
						    {
							$this->_lookForPotentialElements($child, $this->_setNametraceIndexes($currentnametrace . "/" . $child["name"]));
						    } //end foreach
					    } //end foreach
				    } //end for
			    } //end if
		    } //end if
	    } //end _lookForPotentialElements()


	/**
	 * Find potential elements for nametrace
	 *
	 * @param string $nametrace Nametrace
	 *
	 * @return void
	 */

	private function _findPotentialElements($nametrace)
	    {
		foreach (array_keys($this->_keys) as $last)
		    {
			$matches = array();
			foreach ($this->_keys[$last] as $idx => $element)
			    {
				$key   = $this->_setNametraceIndexes($element["key"]);
				$match = $this->_keyMatch($key, $nametrace);
				if ($match !== false)
				    {
					$matches[$match][$idx] = $element["key"];
				    }
			    }

			ksort($matches);
			$matches = array_shift($matches);
			if ($matches !== null && count($matches) === 1)
			    {
				foreach ($matches as $idx => $value)
				    {
					$this->_keys[$last][$idx]["used"] = true;
					$this->_potentials[$nametrace]    = $value;
				    }
			    }
		    } //end foreach
	    } //end _findPotentialElements()


	/**
	 * Check if key matches with nametrace
	 *
	 * @param string $key       Key
	 * @param string $nametrace Nametrace
	 *
	 * @return bool
	 *
	 * @untranslatable choice:
	 */

	private function _keyMatch($key, $nametrace)
	    {
		$bits = explode("/", $nametrace);
		foreach ($bits as $idx => $bit)
		    {
			if ($bit === "")
			    {
				unset($bits[$idx]);
			    }
		    }

		$bits = array_values($bits);

		$keys = explode("/", $key);
		foreach ($keys as $idx => $key)
		    {
			if ($key === "")
			    {
				unset($keys[$idx]);
			    }
		    }

		$keys = array_values($keys);

		if (array_pop($keys) === array_pop($bits))
		    {
			$match = 1;

			$i = (count($keys) - 1);
			$j = (count($bits) - 1);
			while ($i >= 0 && $j >= 0)
			    {
				while ($bits[$j] !== $keys[$i])
				    {
					if ($j <= 0)
					    {
						$match = false;
						break 2;
					    }

					if (strpos($bits[$j], "choice:") === 0 && $match !== 3)
					    {
						$match = 2;
					    }
					else
					    {
						$match = 3;
					    }

					$j--;
				    }

				$i--;
				$j--;
			    } //end while
		    }
		else
		    {
			$match = false;
		    } //end if

		return $match;
	    } //end _keyMatch()


	/**
	 * Make an attempt to build resulting document
	 *
	 * @param array $rootelement Root element description
	 *
	 * @return DOMDocument
	 *
	 * @throws Exception Unable to build valid document
	 *
	 * @exceptioncode EXCEPTION_INVALID_DOCUMENT
	 *
	 * @untranslatable utf-8
	 */

	private function _buildDocument(array $rootelement)
	    {
		$this->prepareForXMLerrors();
		$this->_expandedDocument = new SerializableDOMDocument("1.0", "utf-8");
		$root                    = $this->_iterate($rootelement, $this->_setNametraceIndexes($rootelement["name"]));
		$root                    = array_pop($root);

		$data = $this->_getValuesAndAttributes($rootelement["name"]);
		if ($data !== null)
		    {
			foreach ($data["attributes"] as $attribute)
			    {
				$root->setAttribute($attribute["name"], array_pop($attribute["values"]));
			    }
		    }

		if ($root instanceof DOMElement)
		    {
			$this->_expandedDocument->appendChild($root);
		    }

		if ($this->_expandedDocument->schemaValidate($this->_schemafile) === false)
		    {
			$error = $this->getXMLerrors();
			$this->clearXMLerrors();
			throw new Exception(_("Invalid document") . ": " . $error, EXCEPTION_INVALID_DOCUMENT);
		    }

		$this->clearXMLerrors();

		return $this->_expandedDocument;
	    } //end _buildDocument()


	/**
	 * Iterates down the schema representation. Gets the element using provided nametrace (with indexes),
	 * iterates over its children.
	 *
	 * @param array  $currentelementshorteneddata Shortened element description array - contains data only
	 * for nametrace construction and retrieving of full element data
	 * @param string $currentnametrace            Current nametrace
	 *
	 * @return array
	 *
	 * @untranslatable leafelement
	 * @untranslatable hierarchyelement
	 */

	private function _iterate(array $currentelementshorteneddata, $currentnametrace)
	    {
		$cyclewasdetected = false;

		$nametraceitems = explode("/", $currentnametrace);
		foreach ($nametraceitems as $key => $nametraceitem)
		    {
			$nametraceitems[$key] = $this->_removeLastNametraceIndex($nametraceitem);
		    }

		$lastitem = array_pop($nametraceitems);
		foreach ($nametraceitems as $nametraceitem)
		    {
			if ($nametraceitem === $lastitem)
			    {
				$cyclewasdetected = true;
			    }
		    }

		if ($cyclewasdetected === true)
		    {
			return array();
		    }
		else
		    {
			$currentelement = $this->_descriptor->getSchemaElement(
			    $currentelementshorteneddata["schemakeeperlocation"]["type"],
			    $currentelementshorteneddata["schemakeeperlocation"]["id"]
			);

			if ($currentelement["elementtype"] === "leafelement")
			    {
				$result = $this->_iterateLeafElement($currentelement, $currentnametrace);
			    }
			else
			    {
				$nolastindexnametrace   = $this->_removeLastNametraceIndex($currentnametrace);
				$numberofhierarchynodes = $this->_getNumberOfHierarchyNodes($nolastindexnametrace);

				$result = array();
				for ($i = 1; $i <= $numberofhierarchynodes; $i++)
				    {
					$currentnametrace = $nolastindexnametrace . "[" . $i . "]";
					if ($currentelement["elementtype"] === "hierarchyelement")
					    {
						$subresult = $this->_iterateHierarchyElement($currentelement, $currentnametrace);
					    }
					else
					    {
						$subresult = $this->_iterateChoice($currentelement, $currentnametrace);
					    } //end if

					$result = array_merge($result, $subresult);
				    } //end for
			    } //end if

			return $result;
		    } //end if
	    } //end _iterate()


	/**
	 * Iterating over leaf element.
	 *
	 * @param array  $currentelement   Full element description array
	 * @param string $currentnametrace Current nametrace
	 *
	 * @return array
	 */

	private function _iterateLeafElement(array $currentelement, $currentnametrace)
	    {
		$valuesandattributes = $this->_getValuesAndAttributes($currentnametrace);

		$values = $valuesandattributes["values"];
		if (empty($values) === true)
		    {
			$result = array();
		    }
		else
		    {
			$iterationresultsarray = array();
			foreach ($values as $key => $value)
			    {
				$textvalue   = $this->_expandedDocument->createTextNode($value);
				$leafelement = $this->_expandedDocument->createElement($currentelement["name"]);
				$leafelement->appendChild($textvalue);

				if (isset($valuesandattributes["attributes"]) === true)
				    {
					foreach ($valuesandattributes["attributes"] as $attribute)
					    {
						if (isset($attribute["values"][$key]) === true)
						    {
							$leafelement->setAttribute($attribute["name"], $attribute["values"][$key]);
						    } //end if
					    } //end foreach
				    } //end if

				$iterationresultsarray[] = $leafelement;
			    } //end foreach

			$result = $iterationresultsarray;
		    } //end if

		return $result;
	    } //end _iterateLeafElement()


	/**
	 * Iterating over regular hierarchy element.
	 *
	 * @param array  $currentelement   Full element description array
	 * @param string $currentnametrace Current nametrace
	 *
	 * @return array
	 */

	private function _iterateHierarchyElement(array $currentelement, $currentnametrace)
	    {
		$iterationresultsarray = array();
		$hierarchynode         = $this->_expandedDocument->createElement($currentelement["name"]);
		$attributesandvalues   = $this->_getValuesAndAttributes($currentnametrace);
		if (isset($attributesandvalues["attributes"]) === true)
		    {
			foreach ($attributesandvalues["attributes"] as $attribute)
			    {
				if (count($attribute["values"]) === 1)
				    {
					$currentvalue = array_pop($attribute["values"]);
					if ($currentvalue !== null)
					    {
						$hierarchynode->setAttribute($attribute["name"], $currentvalue);
					    } //end if
				    } //end if
			    } //end foreach
		    } //end if

		$parentnametrace = $currentnametrace;
		$nodeispopulated = false;
		foreach ($currentelement["children"] as $child)
		    {
			$currentnametrace           = $parentnametrace . "/" . $child["name"];
			$childiterationresultsarray = $this->_iterate($child, $this->_setNametraceIndexes($currentnametrace));
			if (empty($childiterationresultsarray) === false)
			    {
				$nodeispopulated = true;
				foreach ($childiterationresultsarray as $childnode)
				    {
					$hierarchynode->appendChild($childnode);
				    } //end foreach
			    } //end if
		    } //end foreach

		if ($nodeispopulated === true)
		    {
			$iterationresultsarray[] = $hierarchynode;
		    } //end if

		return $iterationresultsarray;
	    } //end _iterateHierarchyElement()


	/**
	 * Iterating over choice hierarchy element.
	 *
	 * @param array  $currentelement   Full element description array
	 * @param string $currentnametrace Current nametrace
	 *
	 * @return array
	 */

	private function _iterateChoice(array $currentelement, $currentnametrace)
	    {
		$preprocessedchoices   = array();
		$iterationresultsarray = array();
		$choicebranches        = $currentelement["children"]["branches"];

		$preprocessedchoices         = array();
		$maxnumberofmatchingchildren = 0;
		foreach ($choicebranches as $key => $choicebranchelements)
		    {
			$choicebranchsubresult = array();
			foreach ($choicebranchelements as $choicebranchelement)
			    {
				$newnametrace    = $currentnametrace . "/" . $choicebranchelement["name"];
				$subchoiceresult = $this->_iterate($choicebranchelement, $this->_setNametraceIndexes($newnametrace));

				$choicebranchsubresult = array_merge($choicebranchsubresult, $subchoiceresult);
			    } //end foreach

			if (empty($choicebranchsubresult) === false)
			    {
				$preprocessedchoices[] = array(
							  "nodesarray" => $choicebranchsubresult,
							  "nametrace"  => $currentnametrace,
							  "key"        => $key,
							 );

				$numberofmatchingchildren = count($choicebranchsubresult);
				if ($numberofmatchingchildren > $maxnumberofmatchingchildren)
				    {
					$maxnumberofmatchingchildren = $numberofmatchingchildren;
				    } //end if
			    } //end if
		    } //end foreach

		foreach ($preprocessedchoices as $preprocessedchoice)
		    {
			if (count($preprocessedchoice["nodesarray"]) === $maxnumberofmatchingchildren)
			    {
				foreach ($preprocessedchoice["nodesarray"] as $item)
				    {
					$iterationresultsarray[] = $item;
				    }

				$this->_decisionArray[$preprocessedchoice["nametrace"]] = $preprocessedchoice["key"];
				break;
			    } //end if
		    } //end foreach

		return $iterationresultsarray;
	    } //end _iterateChoice()


	/**
	 * Returns number of hierarchy nodes for specific nametrace.
	 *
	 * @param string $nametrace Given nametrace
	 *
	 * @return int
	 */

	private function _getNumberOfHierarchyNodes($nametrace)
	    {
		$explodednametrace = explode("/", $nametrace);
		$lastnametraceitem = array_pop($explodednametrace);

		$numberofhierarchynodes = 1;
		foreach (array_keys($this->_inputitems) as $key)
		    {
			$index   = 1;
			$matches = array();
			if (strpos($key, "/" . $nametrace) === 0)
			    {
				$nosearchednamtrace         = str_replace("/" . $nametrace, "", $key);
				$explodednosearchednamtrace = explode("/", $nosearchednamtrace);
				preg_match("/\d+/", $explodednosearchednamtrace[0], $matches);
			    }
			else if (strpos($key, $lastnametraceitem) !== false)
			    {
				$explodedkey = explode("/", $key);
				foreach ($explodedkey as $keyitem)
				    {
					if (strpos($keyitem, $lastnametraceitem) === 0)
					    {
						preg_match("/\d+/", $keyitem, $matches);
						break;
					    } //end if
				    } //end foreach
			    } //end if

			if (empty($matches) === false)
			    {
				$index = intval($matches[0]);
				if ($index > $numberofhierarchynodes)
				    {
					$numberofhierarchynodes = $index;
				    } //end if
			    } //end if
		    } //end foreach

		return $numberofhierarchynodes;
	    } //end _getNumberOfHierarchyNodes()


	/**
	 * Returns values and attributes for specific leaf node.
	 *
	 * @param string $elementnametrace Nametrace of the element
	 *
	 * @return array
	 */

	private function _getValuesAndAttributes($elementnametrace)
	    {
		$nametrace = $this->_deleteNametraceIndexes($elementnametrace);
		if (isset($this->_potentials[$elementnametrace]) === true)
		    {
			return $this->_inputitems[$this->_potentials[$elementnametrace]];
		    }
		else if (isset($this->_potentials[$nametrace]) === true)
		    {
			return $this->_inputitems[$this->_potentials[$nametrace]];
		    }
		else
		    {
			return null;
		    }
	    } //end _getValuesAndAttributes()


    } //end class

?>
