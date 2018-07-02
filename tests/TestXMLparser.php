<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 */

namespace Logics\Tests\Foundation\XML;

use \Logics\Foundation\SQL\SQLdatabase;

/**
 * TestXMLparser class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-06-01 00:20:46 +0930 (Wed, 01 Jun 2016) $ $Revision: 1627 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/TestXMLparser.php $
 */

class TestXMLparser
    {

	/**
	 * Database connection
	 *
	 * @var SQLdatabase
	 */
	private $_db;

	/**
	 * Root element
	 *
	 * @var string
	 */
	public $rootelement;

	/**
	 * Code
	 *
	 * @var string
	 */
	public $code;

	/**
	 * Mandatory
	 *
	 * @var string
	 */
	public $mandatory;

	/**
	 * Instantiate this class
	 *
	 * @param SQLdatabase $db Database connection
	 *
	 * @return void
	 */

	public function __construct(SQLdatabase $db)
	    {
		$this->_db = $db;
	    } //end __construct()


	/**
	 * Set root element
	 *
	 * @param string $value Value from translated document
	 * @param string $code  Attribute "code" from translated document
	 *
	 * @return void
	 */

	public function setRootElement($value, $code)
	    {
		$this->rootelement = $value;
		$this->code        = $code;
	    } //end setRootElement()


	/**
	 * Set mandatory value
	 *
	 * @param string $value Value from translated document
	 *
	 * @return void
	 */

	public function setMandatory($value)
	    {
		$this->mandatory = $value;
	    } //end setMandatory()


    } //end class

?>
