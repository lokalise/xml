<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * SchemaNametraceDescriptorStepDown class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/schemanametracedescriptor/SchemaNametraceDescriptorStepDown.php $
 */

class SchemaNametraceDescriptorStepDown
    {

	use SchemaNametrace, DOMDocumentTools;

	/**
	 * Last element reference
	 *
	 * @var mixed
	 */
	private $_lastelementreference = false;

	/**
	 * Instaniate the class instance.
	 *
	 * @param string $prefix       Schema prefix
	 * @param array  $schemakeeper Schema keeper array
	 *
	 * @return void
	 */

	public function __construct($prefix, array $schemakeeper)
	    {
		$this->schemaPrefix = $prefix;
		$this->schemaKeeper = $schemakeeper;
	    } //end __construct()


	/**
	 * Performing step down the tree.
	 *
	 * @param string $normalizednametrace Current nametrace
	 * @param array  $startelement        Element description from the schemaKeeper, not the schemalocationelement
	 *
	 * @return array
	 */

	public function perform($normalizednametrace, array $startelement)
	    {
		$stepdownresult = $this->_stepdown($normalizednametrace, $startelement);

		return array(
			"element"              => $stepdownresult,
			"lastelementreference" => $this->_lastelementreference,
		       );
	    } //end perform()


	/**
	 * Iterating down the compiled schema.
	 *
	 * @param string $nametrace      Current nametrace
	 * @param array  $currentelement Element description from the schemaKeeper, not the schemalocationelement
	 *
	 * @return array
	 *
	 * @untranslatable element
	 * @untranslatable choice
	 * @untranslatable annotation
	 */

	private function _stepdown($nametrace, array $currentelement)
	    {
		$result = false;
		if ($currentelement["type"] === $this->schemaPrefix . "element" || $currentelement["type"] === $this->schemaPrefix . "choice")
		    {
			$result = $this->_stepdownOverSemanticNodes($nametrace, $currentelement);
		    }
		else if ($currentelement["type"] !== $this->schemaPrefix . "annotation")
		    {
			$result = $this->_stepdownOverNonSemanticNodes($nametrace, $currentelement);
		    } //end if

		return $result;
	    } //end _stepdown()


	/**
	 * Iterating down the compiled schema semantic (choice or element) nodes.
	 *
	 * @param string $nametrace      Current nametrace
	 * @param array  $currentelement Element description from the schemaKeeper, not the schemalocationelement
	 *
	 * @return array
	 *
	 * @untranslatable element
	 */

	private function _stepdownOverSemanticNodes($nametrace, array $currentelement)
	    {
		$explodednametrace       = explode("/", $nametrace);
		$copyofexplodednametrace = $explodednametrace;
		$currentitem             = array_shift($copyofexplodednametrace);
		$shortenednametrace      = implode("/", $copyofexplodednametrace);

		$this->_lastelementreference = false;
		if (isset($currentelement["attributes"]["ref"]) === true)
		    {
			$this->_lastelementreference = $currentelement;

			$referencedelement = $currentelement["children"][0];
			$currentelement    = $this->schemaKeeper[$referencedelement["type"]][$referencedelement["id"]];
		    } //end if

		if ($currentitem === $currentelement["name"])
		    {
			if (count($explodednametrace) === 1)
			    {
				return $currentelement;
			    }
			else if ($currentelement["type"] === $this->schemaPrefix . "element")
			    {
				return $this->_stepdownOverSemanticNodesRegularIteration($currentelement, $shortenednametrace);
			    }
			else
			    {
				return $this->_stepdownOverSemanticNodesRegularIteration($currentelement, $shortenednametrace);
			    } //end if
		    }
		else
		    {
			$result         = false;
			$missingchoices = $this->_checkForMissingChoiceIterate($currentelement, $currentitem);
			if (empty($missingchoices) === false)
			    {
				$missedchoice = $missingchoices[0];
				$newnametrace = $missedchoice["name"] . "/" . $nametrace;
				$result       = $this->_stepdown($newnametrace, $missedchoice);
			    } //end if

			return $result;
		    } //end if
	    } //end _stepdownOverSemanticNodes()


	/**
	 * Iterating down the compiled schema non-semantic (not choice or element) nodes.
	 *
	 * @param string $nametrace      Current nametrace
	 * @param array  $currentelement Element description from the schemaKeeper, not the schemalocationelement
	 *
	 * @return array
	 */

	private function _stepdownOverNonSemanticNodes($nametrace, array $currentelement)
	    {
		$explodednametrace       = explode("/", $nametrace);
		$copyofexplodednametrace = $explodednametrace;
		$currentitem             = array_shift($copyofexplodednametrace);

		if ($this->_isChoiceNametraceItem($currentitem) === true)
		    {
			return $this->_stepdownOverSemanticNodesRegularIteration($currentelement, $nametrace);
		    }
		else
		    {
			$missingchoices = $this->_checkForMissingChoiceIterate($currentelement, $currentitem);
			if (empty($missingchoices) === true)
			    {
				return $this->_stepdownOverSemanticNodesRegularIteration($currentelement, $nametrace);
			    }
			else
			    {
				$missedchoice = $missingchoices[0];
				$newnametrace = $missedchoice["name"] . "/" . $nametrace;
				return $this->_stepdown($newnametrace, $missedchoice);
			    } //end if
		    } //end if
	    } //end _stepdownOverNonSemanticNodes()


	/**
	 * Regular iteration routine.
	 *
	 * @param array  $currentelement     Current element for iteration
	 * @param string $shortenednametrace Shortened actual nametrace
	 *
	 * @return array
	 */

	private function _stepdownOverSemanticNodesRegularIteration(array $currentelement, $shortenednametrace)
	    {
		$returnresult = false;
		foreach ($currentelement["children"] as $child)
		    {
			$subresult = $this->_stepdown($shortenednametrace, $this->schemaKeeper[$child["type"]][$child["id"]]);
			if ($subresult !== false)
			    {
				$returnresult = $subresult;
				break;
			    } //end if
		    } //end foreach

		return $returnresult;
	    } //end _stepdownOverSemanticNodesRegularIteration()


	/**
	 * Completes missing choice names in the nametrace till the first choice occurence.
	 *
	 * @param array  $schemakeeperelement Element schema keeper location description
	 * @param string $limiter             Current nametrace item name
	 *
	 * @return string
	 *
	 * @untranslatable choice
	 * @untranslatable element
	 */

	private function _checkForMissingChoiceIterate(array $schemakeeperelement, $limiter = "")
	    {
		if ($schemakeeperelement["type"] === $this->schemaPrefix . "choice")
		    {
			$branchisvalid = $this->_checkIfBranchIsValid($schemakeeperelement["schemakeeperlocation"], $limiter);
			if ($branchisvalid === true)
			    {
				return array($schemakeeperelement);
			    }
		    }
		else
		    {
			$returnarray = array();
			foreach ($schemakeeperelement["children"] as $child)
			    {
				$childkeeperelement = $this->schemaKeeper[$child["type"]][$child["id"]];

				if ($child["type"] === $this->schemaPrefix . "choice")
				    {
					$branchisvalid = $this->_checkIfBranchIsValid($child, $limiter);
					if ($branchisvalid === true)
					    {
						$returnarray[] = $childkeeperelement;
					    }
				    }
				else if ($childkeeperelement["type"] !== $this->schemaPrefix . "element")
				    {
					$returnarray = array_merge($returnarray, $this->_checkForMissingChoiceIterate($childkeeperelement));
				    } //end if
			    } //end foreach

			return $returnarray;
		    } //end if
	    } //end _checkForMissingChoiceIterate()


	/**
	 * Checks if branch with selected choice element is suitable for current item in processed nametrace.
	 *
	 * @param array  $schemakeeperelement Element schema keeper location description
	 * @param string $limiter             Current nametrace item name
	 *
	 * @return bool
	 *
	 * @untranslatable element
	 */

	private function _checkIfBranchIsValid(array $schemakeeperelement, $limiter)
	    {
		$result       = false;
		$schemakeeper = $this->schemaKeeper[$schemakeeperelement["type"]][$schemakeeperelement["id"]];
		foreach ($schemakeeper["children"] as $child)
		    {
			$childkeeper = $this->schemaKeeper[$child["type"]][$child["id"]];
			if ($childkeeper["type"] === $this->schemaPrefix . "element")
			    {
				if ($childkeeper["attributes"]["name"] === $limiter)
				    {
					$result = true;
					break;
				    }
			    }
			else
			    {
				$subresult = $this->_checkIfBranchIsValid($child, $limiter);
				if ($subresult === true)
				    {
					$result = true;
					break;
				    } //end if
			    } //end if
		    } //end foreach

		return $result;
	    } //end _checkIfBranchIsValid()


    } //end class

?>
