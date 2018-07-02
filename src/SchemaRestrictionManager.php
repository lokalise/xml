<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * SchemaRestrictionManager class - validating and processing restrictions.
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaRestrictionManager.php $
 */

class SchemaRestrictionManager
    {

	/**
	 * Restriction which has failed validation
	 *
	 * @var mixed
	 */
	private $_lastFailedAtValidationRestriction;

	/**
	 * Validating specific value against restriction for the element.
	 *
	 * @param string $value        Value of the element
	 * @param array  $restrictions Set of restrictions
	 *
	 * @return bool
	 *
	 * @untranslatable language
	 */

	public function validate($value, array $restrictions)
	    {
		$this->_lastFailedAtValidationRestriction = false;
		$validationresult = true;
		foreach ($restrictions as $restriction)
		    {
			preg_match("/:(.*)/", $restriction["type"], $match);
			switch ($match[1])
			    {
				case "language":
					if (preg_match("/[a-zA-Z]{1,8}(-[a-zA-Z0-9]{1,8})*/", $value) !== false)
					    {
						$validationresult = true;
					    }
				    break;
				default:
					$validationresult = $this->_routeSchemaRestriction($match[1], $restriction, $value);
				    break;
			    } //end switch

			if ($validationresult === false || ($match[1] === "language" && $validationresult === true))
			    {
				break;
			    }
		    } //end foreach

		return $validationresult;
	    } //end validate()


	/**
	 * Returns last generated errors.
	 *
	 * @return mixed
	 */

	public function getLastFailedRestriction()
	    {
		return $this->_lastFailedAtValidationRestriction;
	    } //end getLastFailedRestriction()


	/**
	 * Extract default value depending on element restrictions
	 *
	 * @param array $description Node description
	 *
	 * @return string
	 *
	 * @untranslatable restrictions
	 * @untranslatable enumeration
	 * @untranslatable boolean
	 * @untranslatable false
	 */

	public function getDefaultValue(array $description)
	    {
		$defaultvalue = "";
		if (array_key_exists("restrictions", $description) === true)
		    {
			$restrictions = $description["restrictions"];
			foreach ($restrictions as $restrictiondescription)
			    {
				$explodedrestrictionname = explode(":", $restrictiondescription["type"]);
				if ($explodedrestrictionname[1] === "enumeration")
				    {
					$defaultvalue = $restrictiondescription["values"][0]["value"];
					break;
				    }
			    } //end foreach
		    }

		if ($defaultvalue === "" && isset($description["nodeattributes"]["type"]) === true)
		    {
			$explodedtype = explode(":", $description["nodeattributes"]["type"]);
			if (array_pop($explodedtype) === "boolean")
			    {
				$defaultvalue = "false";
			    }
		    }

		return $defaultvalue;
	    } //end getDefaultValue()


	/**
	 * Routing schema restrictions.
	 *
	 * @param string $restrictionname Name of the restriction
	 * @param array  $restriction     Restriction description
	 * @param string $value           Processed value
	 *
	 * @return bool
	 *
	 * @untranslatable minInclusive
	 * @untranslatable maxInclusive
	 * @untranslatable fractionDigits
	 * @untranslatable totalDigits
	 * @untranslatable enumeration
	 * @untranslatable pattern
	 * @untranslatable minLength
	 * @untranslatable maxLength
	 */

	private function _routeSchemaRestriction($restrictionname, array $restriction, $value)
	    {
		$validationresult = true;
		switch ($restrictionname)
		    {
			case "minInclusive":
				$validationresult = $this->_validateMinInclusive($restriction, $value);
			    break;
			case "maxInclusive":
				$validationresult = $this->_validateMaxInclusive($restriction, $value);
			    break;
			case "fractionDigits":
				$validationresult = $this->_validateFractionDigits($restriction, $value);
			    break;
			case "totalDigits":
				$validationresult = $this->_validateTotalDigits($restriction, $value);
			    break;
			case "enumeration":
				$validationresult = $this->_validateEnumeration($restriction, $value);
			    break;
			case "pattern":
				$validationresult = $this->_validatePattern($restriction, $value);
			    break;
			case "minLength":
				$validationresult = $this->_validateMinLength($restriction, $value);
			    break;
			case "maxLength":
				$validationresult = $this->_validateMaxLength($restriction, $value);
			    break;
		    } //end switch

		return $validationresult;
	    } //end _routeSchemaRestriction()


	/**
	 * Validating minLength restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validateMinLength(array $restriction, $value)
	    {
		$minlength = intval(end($restriction["values"])["value"]);
		if (strlen($value) < $minlength)
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
		else
		    {
			return true;
		    }
	    } //end _validateMinLength()


	/**
	 * Validating maxLength restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validateMaxLength(array $restriction, $value)
	    {
		$maxlength = intval(end($restriction["values"])["value"]);
		if (strlen($value) > $maxlength)
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
		else
		    {
			return true;
		    }
	    } //end _validateMaxLength()


	/**
	 * Validating enumeration restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validateEnumeration(array $restriction, $value)
	    {
		$possiblevalues = array();
		foreach ($restriction["values"] as $valuecontainer)
		    {
			$possiblevalues[] = $valuecontainer["value"];
		    } //end foreach

		if (in_array($value, $possiblevalues) === false)
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
		else
		    {
			return true;
		    } //end if
	    } //end _validateEnumeration()


	/**
	 * Validating pattern restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validatePattern(array $restriction, $value)
	    {
		$pattern = end($restriction["values"])["value"];
		$matches = preg_match("/^" . $pattern . "$/", $value);
		if ($matches === false || $matches < 1)
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
		else
		    {
			return true;
		    }
	    } //end _validatePattern()


	/**
	 * Validating minInclusive restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validateMinInclusive(array $restriction, $value)
	    {
		$floatvalue       = floatval($value);
		$restrictionvalue = floatval(end($restriction["values"])["value"]);
		if (is_numeric($value) === false || $floatvalue < $restrictionvalue)
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
		else
		    {
			return true;
		    }
	    } //end _validateMinInclusive()


	/**
	 * Validating maxnInclusive restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validateMaxInclusive(array $restriction, $value)
	    {
		$floatvalue       = floatval($value);
		$restrictionvalue = floatval(end($restriction["values"])["value"]);
		if (is_numeric($value) === false || $floatvalue > $restrictionvalue)
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
		else
		    {
			return true;
		    }
	    } //end _validateMaxInclusive()


	/**
	 * Validating fractionDigits restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validateFractionDigits(array $restriction, $value)
	    {
		$restrictionvalue = intval(end($restriction["values"])["value"]);
		if (preg_match("/^-?\d+(\.(?P<fraction>\d+))?$/", $value, $matches) > 0 && ((isset($matches["fraction"]) === true
		&& strlen($matches["fraction"]) <= $restrictionvalue) || isset($matches["fraction"]) === false))
		    {
			return true;
		    }
		else
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
	    } //end _validateFractionDigits()


	/**
	 * Validating totalDigits restriction.
	 *
	 * @param array  $restriction Restriction description
	 * @param string $value       Processed value
	 *
	 * @return bool
	 */

	private function _validateTotalDigits(array $restriction, $value)
	    {
		$totaldigits = intval(end($restriction["values"])["value"]);
		if (is_numeric($value) === false || preg_match_all("/\d/", $value) > $totaldigits)
		    {
			$this->_lastFailedAtValidationRestriction = $restriction;
			return false;
		    }
		else
		    {
			return true;
		    }
	    } //end _validateTotalDigits()


    } //end class

?>
