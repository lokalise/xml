<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\XML
 */

namespace Logics\Foundation\XML;

use \DOMElement;

/**
 * SchemaFragment class.
 * Link to documentation can be found in the "link" section of the comment.
 * Generates schema XML element based on provided description. Documentation uses schema prefix "xs:" as the default one. Only leaf elements can be generated.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:37:16 +0000 (Wed, 17 Aug 2016) $ $Revision: 388 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/XML/tags/0.1/src/SchemaFragment.php $
 * @link      http://redmine.logics.net.au/projects/xml/wiki/Wiki#SchemaFragment-element-description-contract
 *
 * @untranslatable dc:
 */

class SchemaFragment
    {

	use DOMDocumentTools, XMLerrors;

	const DCURI = "http://dublincore.org/schemas/xmls/qdc/2008/02/11/dc.xsd";

	const DCPREFIX = "dc:";

	const SCHEMAURI = "http://www.w3.org/2001/XMLSchema";

	/**
	 * Schema prefix
	 *
	 * @var string
	 */
	private $_schemaprefix;

	/**
	 * Parent document
	 *
	 * @var DOMDocument
	 */
	private $_parentDocument;

	/**
	 * Highest depth level
	 *
	 * @var bool
	 */
	private $_highestDepthLevel;

	/**
	 * Instaniate the instance of this class.
	 *
	 * @param string $schemaprefix     Prefix of the outer (client-side) schema
	 * @param array  $customnamespaces Optional custom namespaces
	 *
	 * @return void
	 *
	 * @untranslatable xs
	 * @untranslatable utf-8
	 * @untranslatable :schema
	 * @untranslatable :element
	 * @untranslatable name
	 * @untranslatable RootElement
	 * @untranslatable :complexType
	 * @untranslatable :sequence
	 */

	public function __construct($schemaprefix = "xs", array $customnamespaces = array())
	    {
		$document      = new SerializableDOMDocument("1.0", "utf-8");
		$schemaelement = $document->createElementNS(self::SCHEMAURI, $schemaprefix . ":schema");
		$schemaelement->setAttributeNS("http://www.w3.org/2000/xmlns/", ("xmlns:" . $schemaprefix), self::SCHEMAURI);
		$schemaelement->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:dc", self::DCURI);

		foreach ($customnamespaces as $customnamespace)
		    {
			$schemaelement->setAttributeNS("http://www.w3.org/2000/xmlns/", ("xmlns:" . $customnamespace["prefix"]), $customnamespace["uri"]);
		    }

		$document->appendChild($schemaelement);

		$rootelement = $document->createElementNS(self::SCHEMAURI, $schemaprefix . ":element");
		$rootelement->setAttribute("name", "RootElement");
		$complextype = $document->createElementNS(self::SCHEMAURI, $schemaprefix . ":complexType");
		$sequence    = $document->createElementNS(self::SCHEMAURI, $schemaprefix . ":sequence");

		$complextype->appendChild($sequence);
		$rootelement->appendChild($complextype);

		$document->documentElement->appendChild($rootelement);
		$this->_schemaprefix   = $schemaprefix . ":";
		$this->_parentDocument = $document;
	    } //end __construct()


	/**
	 * Generates XML snippet, based on element description.
	 *
	 * @param array $inputdescription  Element description Examples will be sooner available in corporate PM tool.
	 * @param bool  $highestdepthlevel Specifies if the element is expected to be under the xs:schema element or not (if first case some restrictions will apply)
	 *
	 * @return DOMElement XML element, ready to be imported and inserted into client's schema
	 */

	public function get(array $inputdescription, $highestdepthlevel = true)
	    {
		$this->_highestDepthLevel = $highestdepthlevel;

		$description = $this->_normalizeDescription($inputdescription);
		$elementnode = false;
		if (isset($description["restrictions"]) === true)
		    {
			$elementnode = $this->_getSimpleTypeElement($description);
		    }
		else
		    {
			$elementnode = $this->_getOneLinerElement($description);
		    }

		$resultingnode = $this->_validateNode($elementnode);
		return $resultingnode;
	    } //end get()


	/**
	 * Creates node for simpleType element definitions.
	 * Currently any simpleType element is basically one-liner with restrictions.
	 *
	 * @param array $description Processed element description
	 *
	 * @return DOMElement
	 *
	 * @untranslatable type
	 * @untranslatable simpleType
	 */

	private function _getSimpleTypeElement(array $description)
	    {
		$baseelement = $this->_getOneLinerElement($description);
		$baseelement->removeAttribute("type");

		$restrictionselement = $this->_getRestrictionsElement($description);
		$simpletypeelement   = $this->_parentDocument->createElementNS(self::SCHEMAURI, $this->_schemaprefix . "simpleType");
		$simpletypeelement->appendChild($restrictionselement);
		$baseelement->appendChild($simpletypeelement);

		return $baseelement;
	    } //end _getSimpleTypeElement()


	/**
	 * Testing validity of the generated XML snippet.
	 *
	 * @param DOMElement $elementnode Generated element
	 *
	 * @return DOMElement
	 *
	 * @untranslatable sequence
	 */

	private function _validateNode(DOMElement $elementnode)
	    {
		$parentdocumentxpath = new DOMXPathExtended($this->_parentDocument);
		if ($this->_highestDepthLevel === true)
		    {
			$this->_parentDocument->documentElement->appendChild($elementnode);
		    }
		else
		    {
			$sequencetoappend = $parentdocumentxpath->query("//" . $this->_schemaprefix . "sequence")->item(0);
			$sequencetoappend->appendChild($elementnode);
		    }

		$temporaryschemafilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . ".xsd";
		$this->_parentDocument->save($temporaryschemafilename);
		$this->validateSchema($temporaryschemafilename);
		unlink($temporaryschemafilename);

		return $elementnode;
	    } //end _validateNode()


	/**
	 * Creates node for one-line element definition.
	 *
	 * @param array $description Processed element description
	 *
	 * @return DOMElement
	 *
	 * @untranslatable element
	 * @untranslatable name
	 * @untranslatable type
	 * @untranslatable minOccurs
	 * @untranslatable maxOccurs
	 */

	private function _getOneLinerElement(array $description)
	    {
		$elementnode = $this->_parentDocument->createElementNS(self::SCHEMAURI, $this->_schemaprefix . "element");
		$elementnode->setAttribute("name", $description["name"]);
		$elementnode->setAttribute("type", $this->_schemaprefix . $description["nodeattributes"]["type"]);

		if ($this->_highestDepthLevel === false)
		    {
			$elementnode->setAttribute("minOccurs", $description["nodeattributes"]["minOccurs"]);
			$elementnode->setAttribute("maxOccurs", $description["nodeattributes"]["maxOccurs"]);
		    }

		$annotation = $this->_getAnnotationElement($description);
		if ($annotation !== false)
		    {
			$elementnode->appendChild($annotation);
		    }

		return $elementnode;
	    } //end _getOneLinerElement()


	/**
	 * Constructs the xs:annotation schema element for given element.
	 *
	 * @param array $elementdescription Processed element description
	 *
	 * @return mixed
	 *
	 * @untranslatable documentation
	 * @untranslatable annotation
	 */

	private function _getAnnotationElement(array $elementdescription)
	    {
		$annotationelement = false;

		if (isset($elementdescription["annotation"]) === true)
		    {
			$annotationnode = $this->_parentDocument->createElementNS(self::SCHEMAURI, $this->_schemaprefix . "annotation");

			$annotation = $elementdescription["annotation"];

			$documentation = $this->_parentDocument->createElementNS(self::SCHEMAURI, $this->_schemaprefix . "documentation");
			if (isset($annotation["documentation"]["title"]) === true)
			    {
				$titlenode = $this->_parentDocument->createElement(self::DCPREFIX . "title");
				$title     = $this->_parentDocument->createTextNode($annotation["documentation"]["title"]);
				$titlenode->appendChild($title);

				$annotationisnotempty = true;
				$documentation->appendChild($titlenode);
				unset($title);
				unset($titlenode);
			    }

			if (isset($annotation["documentation"]["description"]) === true)
			    {
				$descriptionnode = $this->_parentDocument->createElement(self::DCPREFIX . "description");
				$description     = $this->_parentDocument->createTextNode($annotation["documentation"]["description"]);
				$descriptionnode->appendChild($description);

				$annotationisnotempty = true;
				$documentation->appendChild($descriptionnode);
				unset($description);
				unset($descriptionnode);
			    }

			if ($documentation->childNodes->length > 0)
			    {
				$annotationnode->appendChild($documentation);
			    }

			if (isset($annotation["appinfo"]) === true)
			    {
				$appinfos    = $this->_getNodesFromArray($annotation["appinfo"]);
				$appinfonode = $this->_parentDocument->createElement($this->_schemaprefix . "appinfo");
				$appinfonode->appendChild(end($appinfos));
				$annotationnode->appendChild($appinfonode);
			    }

			if ($annotationnode->childNodes->length > 0)
			    {
				$annotationelement = $annotationnode;
			    }
		    } //end if

		return $annotationelement;
	    } //end _getAnnotationElement()


	/**
	 * Returns set of DOMElements that were produced from the plain array description.
	 *
	 * @param array $elementdescription Description of the element
	 *
	 * @return array
	 */

	private function _getNodesFromArray(array $elementdescription)
	    {
		$resultingnodes = array();

		$nodes = array_keys($elementdescription);
		foreach ($nodes as $nodename)
		    {
			if (is_array($elementdescription[$nodename]) === true)
			    {
				$underlyingnodes = $this->_getNodesFromArray($elementdescription[$nodename]);
				$node            = $this->_parentDocument->createElement($nodename);
				foreach ($underlyingnodes as $underlyingnode)
				    {
					$node->appendChild($underlyingnode);
				    }

				$resultingnodes[] = $node;
			    }
			else
			    {
				$node = $this->_parentDocument->createElement($nodename, $elementdescription[$nodename]);

				$resultingnodes[] = $node;
			    }
		    }

		return $resultingnodes;
	    } //end _getNodesFromArray()


	/**
	 * Constructs the xs:restriction schema element for given element.
	 *
	 * @param array $description Processed element description
	 *
	 * @return DOMElement
	 *
	 * @untranslatable restriction
	 * @untranslatable base
	 * @untranslatable value
	 */

	private function _getRestrictionsElement(array $description)
	    {
		$restrictionwrappingelement = $this->_parentDocument->createElementNS(self::SCHEMAURI, $this->_schemaprefix . "restriction");
		$restrictionwrappingelement->setAttribute("base", $this->_schemaprefix . $description["nodeattributes"]["type"]);
		foreach ($description["restrictions"] as $restriction)
		    {
			foreach ($restriction["values"] as $restrictionvalue)
			    {
				$singlerestriction = $this->_parentDocument->createElementNS(self::SCHEMAURI, $this->_schemaprefix . $restriction["type"]);
				$singlerestriction->setAttribute("value", $restrictionvalue["value"]);

				$annotation = $this->_getAnnotationElement($restrictionvalue);
				if ($annotation !== false)
				    {
					$singlerestriction->appendChild($annotation);
				    }

				$restrictionwrappingelement->appendChild($singlerestriction);
			    }
		    }

		return $restrictionwrappingelement;
	    } //end _getRestrictionsElement()


	/**
	 * Normalizes given description to the internal expectactions.
	 *
	 * @param array $processeddescription Description array that has to be normilized
	 *
	 * @return array Normalized description
	 *
	 * @throws Exception Name and type should be specified
	 *
	 * @exceptioncode EXCEPTION_NAME_SHOULD_BE_SPECIFIED
	 * @exceptioncode EXCEPTION_TYPE_SHOULD_BE_SPECIFIED
	 */

	private function _normalizeDescription(array $processeddescription)
	    {
		if (isset($processeddescription["name"]) === false)
		    {
			throw new Exception(_("Name has to be specified in order to generate Schema element"), EXCEPTION_NAME_SHOULD_BE_SPECIFIED);
		    }

		$processeddescription["nodeattributes"] = array_merge(array(
								       "maxOccurs" => 1,
								       "minOccurs" => 1,
								      ), $processeddescription["nodeattributes"]);

		if (isset($processeddescription["nodeattributes"]["type"]) === false || substr_count($processeddescription["nodeattributes"]["type"], ":") > 1)
		    {
			throw new Exception(_("Type has to be specified in order to generate Schema element"), EXCEPTION_TYPE_SHOULD_BE_SPECIFIED);
		    }

		if (substr_count($processeddescription["nodeattributes"]["type"], ":") === 1)
		    {
			$typeparts = explode("", $processeddescription["nodeattributes"]["type"]);
			$processeddescription["nodeattributes"]["type"] = $typeparts[1];
		    }

		if (isset($processeddescription["restrictions"]) === true)
		    {
			foreach ($processeddescription["restrictions"] as $restrictionkey => $restriction)
			    {
				if (substr_count($restriction["type"], ":") === 1)
				    {
					$typeparts = explode(":", $restriction["type"]);
					$processeddescription["restrictions"][$restrictionkey]["type"] = $typeparts[1];
				    }
			    }
		    }

		return $processeddescription;
	    } //end _normalizeDescription()


    } //end class

?>
