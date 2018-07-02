<?php

/**
 * XMLparser relies on DOMrunner class and XMLerrors trait
 *
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;
use \Exception;

/**
 * XMLparser trait. Contains methods required to parse an XML document
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/XMLparser.php $
 */

trait XMLparser
    {

	use XMLerrors;

	/**
	 * Validate document
	 *
	 * @param string $document Document to validate
	 * @param array  $schemas  Array containing document types and file names with relevant schemas
	 *
	 * @return string Detected document type
	 *
	 * @throws Exception Invalid or unknown document
	 *
	 * @exceptioncode EXCEPTION_LOAD_XML_FAILED
	 * @exceptioncode EXCEPTION_UNKNOWN_DOCUMENT_TYPE
	 * @exceptioncode EXCEPTION_INVALID_DOCUMENT
	 *
	 * @untranslatable utf-8
	 */

	static protected function validateDocument($document, array $schemas)
	    {
		self::prepareForXMLerrors();
		$doc = new DOMDocument("1.0", "utf-8");
		$doc->loadXML($document, LIBXML_PARSEHUGE);
		$error = self::getXMLerrors();
		if ($error !== false)
		    {
			self::clearXMLerrors();
			throw new Exception($error, EXCEPTION_LOAD_XML_FAILED);
		    }

		$doctype = $doc->documentElement->nodeName;

		if (isset($schemas[$doctype]) === false)
		    {
			self::clearXMLerrors();
			throw new Exception(_("Unknown document type"), EXCEPTION_UNKNOWN_DOCUMENT_TYPE);
		    }

		if ($doc->schemaValidate($schemas[$doctype]) === false)
		    {
			$error = self::getXMLerrors();
			self::clearXMLerrors();
			throw new Exception(_("Invalid document:") . " " . $error, EXCEPTION_INVALID_DOCUMENT);
		    }

		self::clearXMLerrors();

		return $doctype;
	    } //end validateDocument()


	/**
	 * Process XML document
	 *
	 * @param string $document  XML document
	 * @param array  $xlat      Array containing names of translation tables and model objects
	 * @param string $directory Directory which contains classes and translation tables
	 *
	 * @return object
	 *
	 * @throws Exception Unable to process
	 *
	 * @exceptioncode EXCEPTION_UNKNOWN_PATH
	 */

	protected function processDocument($document, array $xlat, $directory = "")
	    {
		$doc       = new DOMrunner($document);
		$tag       = $doc->next();
		$directory = (($directory === "") ? "" : $directory . DIRECTORY_SEPARATOR);
		$methods   = $this->_loadTranslationMethods($xlat, $tag, $directory);
		$namespace = explode("\\", $xlat[$tag]["class"]);
		$file      = array_pop($namespace);
		include_once $directory . $file . ".php";
		$class = new $xlat[$tag]["class"]($this->_db);

		do
		    {
			$attributes = $doc->attributes();
			foreach ($attributes as $idx => $attribute)
			    {
				$attributes[$idx] = trim($attribute);
			    }

			$value = $doc->value();
			$leaf  = $doc->leaf();
			$found = false;
			foreach ($methods as $translation)
			    {
				$found = ($this->_translate($tag, $class, $translation, $value, $attributes, $document, $leaf) || $found);
			    } //end foreach

			if ($found === false)
			    {
				$error = _("Unknown path") . " " . $tag;
				if (count($attributes) > 0)
				    {
					$error .= " " . _("with attribute(s)") . " " . implode(" ", array_keys($attributes));
				    }

				$error .= " " . _("in") . " " . $document;

				throw new Exception($error, EXCEPTION_UNKNOWN_PATH);
			    }
		    } while ($tag = $doc->next());

		return $class;
	    } //end processDocument()


	/**
	 * Load translation table
	 *
	 * @param array  $xlat      Array containing names of translation tables
	 * @param string $tag       Top level tag in XML document (document type) used to identify translation table
	 * @param string $directory Directory which contains translation tables
	 *
	 * @return array
	 *
	 * @throws Exception No translation table is available
	 *
	 * @exceptioncode EXCEPTION_NO_TRANSLATION_FOR_DOCUMENT_TYPE
	 */

	private function _loadTranslationMethods(array $xlat, $tag, $directory)
	    {
		if (isset($xlat[$tag]) === false)
		    {
			throw new Exception(_("No translation for document type") . " " . $tag, EXCEPTION_NO_TRANSLATION_FOR_DOCUMENT_TYPE);
		    }

		include $directory . $xlat[$tag]["xlat"] . ".php";

		foreach ($methods as $idx => $method)
		    {
			if (is_array($method["path"]) === true)
			    {
				$methods[$idx]["path"] = implode($method["path"]);
			    }
		    }

		return $methods;
	    } //end _loadTranslationMethods()


	/**
	 * Perform translation of the tag value and attributes to helper class methods
	 *
	 * @param string $tag         XPath tag
	 * @param mixed  $class       Helper class
	 * @param array  $translation Translation between tag and helper class
	 * @param string $value       Tag value
	 * @param array  $attributes  Tag attributes
	 * @param string $xml         Original XML document
	 * @param string $leaf        XML leaf corresponding to tag
	 *
	 * @return boolean True if translation was successful, false otherwise
	 *
	 * @throws Exception No translation method is not defined in a class
	 *
	 * @exceptioncode EXCEPTION_NO_TRANSLATION_METHOD
	 */

	private function _translate($tag, $class, array $translation, $value, array $attributes, $xml, $leaf)
	    {
		if ($tag === $translation["path"] &&
		    count(array_diff(array_keys($attributes), $translation["attributes"])) === 0 &&
		    count(array_diff($translation["attributes"], array_keys($attributes))) === 0)
		    {
			if (isset($translation["method"]) === true)
			    {
				if (method_exists($class, $translation["method"]) === true)
				    {
					$params = ((isset($translation["function"]) === true) ? array($this->$translation["function"]($xml, $leaf)) : array());
					$args   = array_merge(array($value), array_values($attributes), $params);
					call_user_func_array(array($class, $translation["method"]), $args);
				    }
				else
				    {
					throw new Exception(
						   _("No translation method") . " " . $translation["method"] . " " . _("defined in") . " " . get_class($class),
						   EXCEPTION_NO_TRANSLATION_METHOD
						  );
				    }
			    }

			return true;
		    }
		else
		    {
			return false;
		    } //end if
	    } //end _translate()


    } //end trait

?>
