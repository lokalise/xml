<?php

/**
 * File contains class of serializable DOMDocument
 *
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMDocument;

/**
 * Class to serialize DOMDocument
 *
 * @author    Anastasia Bashkirtseva <anastasia@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SerializableDOMDocument.php $
 */

class SerializableDOMDocument extends DOMDocument
    {

	/**
	 * Container to store serizlaized DOM during serialization
	 *
	 * @var string
	 */
	private $_serializableXML;

	/**
	 * Store XML
	 *
	 * @return array
	 *
	 * @untranslatable _serializableXML
	 */

	public function __sleep()
	    {
		$this->_serializableXML = $this->saveXML();
		return array("_serializableXML");
	    } //end __sleep()


	/**
	 * Load stored XML
	 *
	 * @return void
	 *
	 * @untranslatable <root/>
	 */

	public function __wakeup()
	    {
		$emptydocument = (preg_match("/^<\?xml\s+.*\?>\s*$/", $this->_serializableXML) > 0);
		$s             = $this->_serializableXML . (($emptydocument === true) ? "<root/>" : "");
		parent::__construct();
		$this->loadXML($s);
		if ($emptydocument === true)
		    {
			$this->removeChild($this->documentElement);
		    }
	    } //end __wakeup()


    } //end class

?>
