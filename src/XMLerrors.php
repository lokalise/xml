<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * XMLerrors trait. Contains method required to intercept XML errors
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/XMLerrors.php $
 */

trait XMLerrors
    {

	/**
	 * Prepare for XML errors
	 *
	 * @return void
	 */

	protected static function prepareForXMLerrors()
	    {
		libxml_use_internal_errors(true);
		libxml_clear_errors();
	    } //end prepareForXMLerrors()


	/**
	 * Get last XML errors as text
	 *
	 * @return mixed Errors text or false if no errors
	 */

	protected static function getXMLerrors()
	    {
		$errors = libxml_get_errors();
		$s      = "";
		foreach ($errors as $error)
		    {
			switch ($error->level)
			    {
				case LIBXML_ERR_WARNING: $s .= _("Warning") . " " . $error->code . ": ";
				    break;
				case LIBXML_ERR_ERROR:   $s .= _("Error") . " " . $error->code . ": ";
				    break;
				case LIBXML_ERR_FATAL:   $s .= _("Fatal Error") . " " . $error->code . ": ";
				    break;
			    }

			$s .= trim($error->message);
		    }

		return (($s === "") ? false : $s);
	    } //end getXMLerrors()


	/**
	 * Clear XML errors
	 *
	 * @return void
	 */

	protected static function clearXMLerrors()
	    {
		libxml_use_internal_errors(false);
	    } //end clearXMLerrors()


    } //end trait

?>
