<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * XMLmapping trait. Contains method required to map external entities
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/XMLmapping.php $
 */

trait XMLmapping
    {

	/**
	 * Register additional schema mappings
	 *
	 * @param array $mapping Array specifying mappings for external entities
	 *
	 * @return void
	 */

	private static function _registerSchemaMappings(array $mapping = array())
	    {
		if (isset($_SESSION["SCHEMA_MAPPING"]) === true)
		    {
			$mapping = array_merge($_SESSION["SCHEMA_MAPPING"], $mapping);
		    }

		if (isset($GLOBALS["SCHEMA_MAPPING"]) === true)
		    {
			$mapping = array_merge($GLOBALS["SCHEMA_MAPPING"], $mapping);
		    }

		$_SESSION["SCHEMA_MAPPING"] = $mapping;
		$GLOBALS["SCHEMA_MAPPING"]  = $mapping;

		if (count($mapping) > 0)
		    {
			libxml_set_external_entity_loader(
			function ($public, $system, $context) use ($mapping)
			    {
				unset($public);
				unset($context);
				if (isset($mapping[$system]) === true && is_file($system) === false)
				    {
					return $mapping[$system];
				    }
				else
				    {
					return $system;
				    }
			    }
			);
		    } //end if
	    } //end _registerSchemaMappings()


    } //end trait

?>
