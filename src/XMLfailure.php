<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

/**
 * XMLfailure trait. Contains methods required to record failure of XML document
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/XMLfailure.php $
 */

trait XMLfailure
    {

	/**
	 * Log document failure for manual review
	 *
	 * @param string $failure Failure text
	 * @param string $xml     Data in XML form which has failed to validate against schema
	 *
	 * @return void
	 */

	protected function logFailure($failure, $xml)
	    {
		$this->_makeFailedDocumentsTable();
		$result = $this->_db->exec(
		    "SELECT `id` FROM `FailedDocuments` WHERE XML = " . $this->_db->sqlText($xml)
		);
		if ($result->GetNumRows() > 0)
		    {
			$row = $result->GetRow();
			$this->_db->exec(
			    "UPDATE `FailedDocuments` SET " .
			    "`failure` = " . $this->_db->sqlText($failure) . ", " .
			    "`XML` = " . $this->_db->sqlText($xml) . " " .
			    "WHERE `id` = " . $this->_db->sqlText($row["id"])
			);
		    }
		else
		    {
			$this->_db->exec(
			    "INSERT INTO `FailedDocuments` SET " .
			    "`datetime` = NOW(), " .
			    "`failure` = " . $this->_db->sqlText($failure) . ", " .
			    "`XML` = " . $this->_db->sqlText($xml)
			);
		    } //end if
	    } //end logFailure()


	/**
	 * Clear document failure
	 *
	 * @param string $xml Data in XML form which now clear of failure
	 *
	 * @return void
	 */

	protected function clearFailure($xml)
	    {
		$this->_makeFailedDocumentsTable();
		$this->_db->exec(
		    "DELETE FROM `FailedDocuments` WHERE `XML` = " . $this->_db->sqlText($xml)
		);
	    } //end clearFailure()


	/**
	 * Create table needed for record of failed documents
	 *
	 * @return void
	 */

	private function _makeFailedDocumentsTable()
	    {
		$this->_db->execUntilSuccessful(
		    "CREATE TABLE IF NOT EXISTS `FailedDocuments` (" .
		    "`id` int(11) NOT NULL AUTO_INCREMENT," .
		    "`datetime` datetime NOT NULL," .
		    "`failure` text NOT NULL," .
		    "`XML` longtext NOT NULL," .
		    "PRIMARY KEY (`id`)," .
		    "KEY `XML` (`XML`(255))" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
	    } //end _makeFailedDocumentsTable()


    } //end trait

?>
