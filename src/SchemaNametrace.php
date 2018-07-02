<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \Exception;

/**
 * Trait for nametrace processing tools
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaNametrace.php $
 */

trait SchemaNametrace
    {

	/**
	 * Determines if the string strats from given substring.
	 *
	 * @param string $haystack Checked string
	 * @param string $needle   Searched substring
	 *
	 * @return bool
	 */

	private function _startsWith($haystack, $needle)
	    {
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	    } //end _startsWith()


	/**
	 * Normalizes any given nametrace - trims extra slashes in the very beginning and places
	 * default [1] index in the nametrace items, where no indexes are mentioned. Cache enhanced.
	 *
	 * @param string $nametrace Provided nametrace
	 *
	 * @return string
	 */

	private function _setNametraceIndexes($nametrace)
	    {
		if (isset($_SESSION["xmlsessionstorage"]["indexednametraces"][$nametrace]) === true)
		    {
			return $_SESSION["xmlsessionstorage"]["indexednametraces"][$nametrace];
		    }
		else
		    {
			$nametraceitems = explode("/", preg_replace("/^\/{1,2}/", "", $nametrace));
			foreach ($nametraceitems as $key => $item)
			    {
				if (preg_match("/^[\w:]+\[[\w:'\"=@]+\]$/", $item) === 0)
				    {
					$nametraceitems[$key] .= "[1]";
				    }
			    } //end foreach

			$result = implode("/", $nametraceitems);
			$_SESSION["xmlsessionstorage"]["indexednametraces"][$nametrace] = $result;
			return $result;
		    }
	    } //end _setNametraceIndexes()


	/**
	 * Deletes indexes from every nametrace item and trims extra slashes.
	 *
	 * @param string $nametrace Provided nametrace
	 *
	 * @return string
	 */

	private function _deleteNametraceIndexes($nametrace)
	    {
		$nametrace      = $this->_setNametraceIndexes($nametrace);
		$nametraceitems = explode("/", preg_replace("/^\/{1,2}/", "", $nametrace));
		$modifieditems  = array();
		foreach ($nametraceitems as $nametraceitem)
		    {
			$modifieditems[] = substr($nametraceitem, 0, strpos($nametraceitem, "["));
		    }

		return implode("/", $modifieditems);
	    } //end _deleteNametraceIndexes()


	/**
	 * Removes index of the last nametrace.
	 *
	 * @param string $nametrace Nametrace that needs to be processed
	 *
	 * @return string
	 */

	private function _removeLastNametraceIndex($nametrace)
	    {
		$explodednametrace = explode("/", $nametrace);
		$lastelement       = array_pop($explodednametrace);

		if (strpos($lastelement, "[") === false)
		   {
			$explodednametrace[] = $lastelement;
		    }
		else
		    {
			$explodednametrace[] = substr($lastelement, 0, strpos($lastelement, "["));
		    }

		return implode("/", $explodednametrace);
	    } //end _removeLastNametraceIndex()


	/**
	 * Filling possible nametrace array for current schema.
	 *
	 * @param SchemaNametraceDescriptor $descriptor Actual SchemaNametraceDescriptor instance
	 *
	 * @return array
	 */

	private function _fillPossibleNametraces(SchemaNametraceDescriptor $descriptor)
	    {
		$possiblenametraces = array();

		$rootelements = $descriptor->describe();
		foreach ($rootelements as $rootelement)
		    {
			$possiblenametraces = array_merge($possiblenametraces, $this->_fillPossibleNametracesIterate($rootelement, $rootelement["name"], $descriptor));
		    } //end foreach

		return $possiblenametraces;
	    } //end _fillPossibleNametraces()


	/**
	 * Iteration routine for collectin possible nametraces.
	 *
	 * @param array                     $elementdata Element description
	 * @param string                    $nametrace   Current nametrace
	 * @param SchemaNametraceDescriptor $descriptor  Actual SchemaNametraceDescriptor instance
	 *
	 * @return array
	 */

	private function _fillPossibleNametracesIterate(array $elementdata, $nametrace, SchemaNametraceDescriptor $descriptor)
	    {
		$cyclewasdetected = false;

		$nametraceitems = explode("/", $nametrace);
		$lastitem       = array_pop($nametraceitems);
		foreach ($nametraceitems as $nametraceitem)
		    {
			if ($nametraceitem === $lastitem)
			    {
				$cyclewasdetected = true;
				break;
			    }
		    }

		if ($cyclewasdetected === true)
		    {
			return array($this->_removeChoicesFromNametrace($this->_deleteNametraceIndexes($nametrace)));
		    }
		else
		    {
			$possiblenametraces = array($this->_removeChoicesFromNametrace($this->_deleteNametraceIndexes($nametrace)));

			$element = $descriptor->getSchemaElement($elementdata["schemakeeperlocation"]["type"], $elementdata["schemakeeperlocation"]["id"]);
			if (isset($element["children"]["branches"]) === true)
			    {
				foreach ($element["children"]["branches"] as $choicebranchelements)
				    {
					foreach ($choicebranchelements as $choicebranchelement)
					    {
						$possiblenametraces = array_merge($possiblenametraces, $this->_fillPossibleNametracesIterate($choicebranchelement, $nametrace .
						"/" . $choicebranchelement["name"], $descriptor));
					    } //end foreach
				    } //end foreach
			    }
			else
			    {
				foreach ($element["children"] as $child)
				    {
					if (isset($child["name"]) === true)
					    {
						$possiblenametraces = array_merge($possiblenametraces, $this->_fillPossibleNametracesIterate($child, $nametrace .
						"/" . $child["name"], $descriptor));
					    }
				    }
			    } //end if

			return $possiblenametraces;
		    } //end if
	    } //end _fillPossibleNametracesIterate()


	/**
	 * Gets last i names from the nametrace divided by "/", $depth has to be less than number of elements in $nametrace - in that case returns full nametrace
	 *
	 * @param string $nametrace Current nametrace of the element
	 * @param int    $depth     Search depth
	 *
	 * @return string
	 */

	private function _getLastNametraceItems($nametrace, $depth)
	    {
		$namesarray = explode("/", $nametrace);

		if (empty($namesarray[0]) === true)
		    {
			array_shift($namesarray);
		    } //end if

		$lastitems = "";
		for ($i = 0; $i < $depth; $i++)
		    {
			$lastelement = array_pop($namesarray);
			$lastitems   = $lastelement . $lastitems;
			if ((empty($lastelement) === false) && ($i < ($depth - 1)))
			    {
				$lastitems = "/" . $lastitems;
			    }
		    } //end for

		return $lastitems;
	    } //end _getLastNametraceItems()


	/**
	 * Returns index of the last nametrace.
	 *
	 * @param string $nametrace Nametrace that needs to be processed
	 *
	 * @return int
	 *
	 * @throws Exception Invalid nametrace
	 *
	 * @exceptioncode EXCEPTION_INVALID_NAMETRACE
	 */

	private function _getLastNametraceIndex($nametrace)
	    {
		$lastslashposition   = strrpos($nametrace, "/");
		$lastbracketposition = strrpos($nametrace, "[");
		if ($lastslashposition > $lastbracketposition)
		    {
			throw new Exception(_("Invalid nametrace") . " " . $nametrace, EXCEPTION_INVALID_NAMETRACE);
		    } //end if

		$nametraceitems = explode("/", $nametrace);
		preg_match("/\[(\d+)/", array_pop($nametraceitems), $matches);
		return intval($matches[1]);
	    } //end _getLastNametraceIndex()


	/**
	 * Detects if provided item is a leaf element nametrace.
	 *
	 * @param string $item Examined item
	 *
	 * @return bool
	 */

	private function _isLeafNametraceItem($item)
	    {
		if (substr($item, -1) === "]")
		    {
			return true;
		    }
		else
		    {
			return false;
		    }
	    } //end _isLeafNametraceItem()


	/**
	 * Detects if string is choice nametrace item.
	 *
	 * @param string $item Processed item
	 *
	 * @return bool
	 *
	 * @untranslatable choice:
	 */

	private function _isChoiceNametraceItem($item)
	    {
		return (substr_count($item, "choice:") === 1) ? true : false;
	    } //end _isChoiceNametraceItem()


	/**
	 * Removes choice nametrace items from the nametrace.
	 *
	 * @param string $nametrace Processed nametrace
	 *
	 * @return string
	 */

	private function _removeChoicesFromNametrace($nametrace)
	    {
		$resultingarray = array();
		$nametraceitems = explode("/", $nametrace);
		foreach ($nametraceitems as $nametraceitem)
		    {
			if ($this->_isChoiceNametraceItem($nametraceitem) === false)
			    {
				$resultingarray[] = $nametraceitem;
			    }
		    }

		return implode("/", $resultingarray);
	    } //end _removeChoicesFromNametrace()


    } //end trait

?>
