<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \Exception;

/**
 * SchemaNametraceDescriptor class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaNametraceDescriptor.php $
 */

class SchemaNametraceDescriptor extends SchemaProcessor
    {

	use SchemaNametrace, DOMDocumentTools;

	/**
	 * Discovery mode
	 *
	 * @var string
	 */
	private $_discoverymode;

	/**
	 * Step down iterator
	 *
	 * @var SchemaNametraceDescriptorStepDown
	 */
	private $_stepdown;

	/**
	 * Preferred root element to use
	 *
	 * @var mixed
	 */
	private $_preferredRoot = false;

	/**
	 * Schema nametrace descriptor formatter
	 *
	 * @var SchemaNametraceDescriptorFormatter
	 */
	private $_schemaNametraceDescriptorFormatter;

	/**
	 * Used external schemas
	 *
	 * @var SchemaExternals
	 */
	private $_externals;

	/**
	 * Instaniate the class instance.
	 *
	 * @param string $schemafile    File path of the schema
	 * @param string $discoverymode Specifies the format children should be returned - "simple" contains only
	 *                              semantic underlying elements, "complex" will include virtual elements as well, keeping the structure.
	 *
	 * @return void
	 *
	 * @untranslatable simple
	 */

	public function __construct($schemafile, $discoverymode = "simple")
	    {
		parent::__construct($schemafile);

		$this->_externals = new SchemaExternals();
		$this->schemaID   = $this->_externals->getID($schemafile);

		$this->_discoverymode = $discoverymode;

		$this->_stepdown = new SchemaNametraceDescriptorStepDown($this->schemaPrefix, $this->schemaKeeper);

		$this->_schemaNametraceDescriptorFormatter = new SchemaNametraceDescriptorFormatter($this->schemaDocument,
		$this->schemaKeeper, $this->schemaID, $this->_discoverymode);
	    } //end __construct()


	/**
	 * Changing the discovery mode of the descriptor.
	 *
	 * @param string $discoverymode New discovery mode
	 *
	 * @return void
	 */

	public function setDiscoveryMode($discoverymode)
	    {
		$this->_discoverymode = $discoverymode;

		$this->_schemaNametraceDescriptorFormatter = new SchemaNametraceDescriptorFormatter($this->schemaDocument,
		$this->schemaKeeper, $this->schemaID, $this->_discoverymode);
	    } //end setDiscoveryMode()


	/**
	 * Prepares instance for serialization.
	 *
	 * @return array
	 *
	 * @untranslatable _discoverymode
	 * @untranslatable _stepdown
	 * @untranslatable _externals
	 */

	public function __sleep()
	    {
		$properties   = parent::__sleep();
		$properties[] = "_discoverymode";
		$properties[] = "_stepdown";
		$properties[] = "_externals";

		return $properties;
	    } //end __sleep()


	/**
	 * Invokes current instance from session
	 *
	 * @return SchemaNametraceDescriptor
	 */

	public function __wakeup()
	    {
		$instance = parent::__wakeup();

		$instance->_schemaNametraceDescriptorFormatter = new SchemaNametraceDescriptorFormatter($this->schemaDocument,
		$this->schemaKeeper, $this->schemaID, $this->_discoverymode);
		return $instance;
	    } //end __wakeup()


	/**
	 * Returns the information concerning current element with child nodes.
	 *
	 * @param string $regularnametrace Nametrace with indexes
	 *
	 * @return array
	 *
	 * @untranslatable ROOTELEMENTS
	 * @untranslatable schema/
	 * @untranslatable element
	 * @untranslatable name
	 */

	public function describe($regularnametrace = "ROOTELEMENTS")
	    {
		$normalizednametrace = $this->_deleteNametraceIndexes($regularnametrace);
		if (isset($_SESSION["nametracequerycache"][$this->schemaID][$normalizednametrace]) === true)
		    {
			return $_SESSION["nametracequerycache"][$this->schemaID][$normalizednametrace];
		    }
		else
		    {
			if ($regularnametrace === "ROOTELEMENTS")
			    {
				$rootelements = $this->schemaDocumentXPath->query("/" . $this->schemaPrefix . "schema/" . $this->schemaPrefix . "element");

				$rootelementsarray = array();
				foreach ($rootelements as $rootelement)
				    {
					$elementkeeperinstance = $this->schemaKeeper[$this->schemaPrefix . "element"]["element" . md5($rootelement->getNodePath())];

					$compoundelement = array(
							    "element"              => $elementkeeperinstance,
							    "lastelementreference" => false,
							   );

					$rootelementsarray[] = $this->_schemaNametraceDescriptorFormatter->format($compoundelement);
				    } //end foreach

				$description = $rootelementsarray;
			    }
			else
			    {
				$rootelements = $this->schemaDocumentXPath->query("/" . $this->schemaPrefix . "schema/" . $this->schemaPrefix . "element");
				if ($this->_preferredRoot === false)
				    {
					$rootindex          = 0;
					$firstnametracename = explode("/", $normalizednametrace, 2)[0];
					foreach ($rootelements as $rootelement)
					    {
						if ($rootelement->getAttribute("name") === $firstnametracename)
						    {
							$this->_preferredRoot = $rootindex;
							break;
						    }

						$rootindex++;
					    }
				    }

				$rootitem        = $rootelements->item($this->_preferredRoot);
				$startelement    = $this->schemaKeeper[$this->schemaPrefix . "element"]["element" . md5($rootitem->getNodePath())];
				$returnedelement = $this->_getClosestElementFromCache($normalizednametrace, $startelement);
				$description     = $this->_schemaNametraceDescriptorFormatter->format($returnedelement);
			    } //end if

			if (isset($_SESSION["nametracequerycache"][$this->schemaID]) === false)
			    {
				$_SESSION["nametracequerycache"][$this->schemaID] = array();
			    } //end if

			$_SESSION["nametracequerycache"][$this->schemaID][$normalizednametrace] = $description;
			return $description;
		    } //end if
	    } //end describe()


	/**
	 * Returns element with certain ID. This function works only for xs:elements in order to retain
	 * secrecy if schemaKeeper internal array.
	 *
	 * @param string $type          Type of the element
	 * @param string $id            Identificator of the element
	 * @param bool   $formatelement Specifies if returned element has to be formatted
	 *
	 * @return array
	 *
	 * @throws Exception Unable to return requested type
	 *
	 * @exceptioncode EXCEPTION_UNABLE_TO_RETURN
	 * @exceptioncode EXCEPTION_NO_ELEMENT_OF_TYPE
	 *
	 * @untranslatable element
	 * @untranslatable choice
	 * @untranslatable schema
	 */

	public function getSchemaElement($type, $id, $formatelement = true)
	    {
		if ($type !== $this->schemaPrefix . "element" && $type !== $this->schemaPrefix . "choice" && $type !== $this->schemaPrefix . "schema")
		    {
			throw new Exception(_("Unable to return") . " " . $type . " " . _("type according to the policy"), EXCEPTION_UNABLE_TO_RETURN);
		    } //end if

		if ($type === $this->schemaPrefix . "schema")
		    {
			$result = current($this->schemaKeeper[$type]);
		    }
		else if (isset($this->schemaKeeper[$type][$id]) === false)
		    {
			throw new Exception(
			    _("No element with type") . " '" . $type . "' " . _("and ID") . " '" . $id . "' " . _("was found in internal array"),
			    EXCEPTION_NO_ELEMENT_OF_TYPE
			);
		    }
		else
		    {
			$result = $this->schemaKeeper[$type][$id];
		    } //end if

		if ($formatelement === true)
		    {
			$compoundelement = array(
					    "element"              => $result,
					    "lastelementreference" => false,
					   );

			$result = $this->_schemaNametraceDescriptorFormatter->format($compoundelement);
		    } //end if

		return $result;
	    } //end getSchemaElement()


	/**
	 * Sets index of the root element.
	 *
	 * @param int $preferredroot Selected root element
	 *
	 * @return void
	 */

	public function setPreferredRoot($preferredroot)
	    {
		$this->_preferredRoot = $preferredroot;
	    } //end setPreferredRoot()


	/**
	 * Returns cached element for given nametrace.
	 *
	 * @param string $normalizednametrace Current nametrace
	 * @param array  $startelement        Not-cached starting element
	 *
	 * @return array
	 */

	private function _getClosestElementFromCache($normalizednametrace, array $startelement)
	    {
		$nametracepartiscached  = false;
		$explodednametraceitems = explode("/", $normalizednametrace);
		foreach (array_keys($explodednametraceitems) as $key)
		    {
			$testendnametrace = implode("/", array_splice($explodednametraceitems, 0, -($key + 1)));
			if (isset($_SESSION["nametracequerycache"][$this->schemaID][$testendnametrace]) === true)
			    {
				$startelementdescription = $_SESSION["nametracequerycache"][$this->schemaID][$testendnametrace];
				$startelementtype        = $startelementdescription["schemakeeperlocation"]["type"];
				$startelementid          = $startelementdescription["schemakeeperlocation"]["id"];
				$startelement            = $this->schemaKeeper[$startelementtype][$startelementid];
				$normalizednametrace     = implode("/", array_splice($explodednametraceitems, -($key + 1)));
				$nametracepartiscached   = true;
				break;
			    } //end if
		    } //end foreach

		if ($nametracepartiscached === true)
		    {
			foreach ($startelement["children"] as $child)
			    {
				$childkeeper     = $this->schemaKeeper[$child["type"]][$child["id"]];
				$returnedelement = $this->_stepdown->perform($normalizednametrace, $childkeeper);
				if ($returnedelement["element"] !== false)
				    {
					break;
				    }
			    }
		    }
		else
		    {
			$returnedelement = $this->_stepdown->perform($normalizednametrace, $startelement);
		    } //end if

		return $returnedelement;
	    } //end _getClosestElementFromCache()


    } //end class

?>
