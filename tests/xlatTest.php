<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\XML
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-04-23 15:04:38 +0930 (Sat, 23 Apr 2016) $ $Revision: 1585 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/tests/XML/xlatTest.php $
 */

/**
 * Test translation table
 *
 * @donottranslate
 */

$methods = array(
	    array(
	     "path"       => "/test",
	     "attributes" => array(),
	    ),
	    array(
	     "path"       => "/test",
	     "attributes" => array("code"),
	     "method"     => "setRootElement",
	    ),
	    array(
	     "path"       => "/test/mandatory",
	     "attributes" => array(),
	     "method"     => "setMandatory",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod1",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod2",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod3",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod4",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod5",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod6",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod7",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod8",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod9",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod10",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod11",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod12",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod13",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod14",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod15",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod16",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod17",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod18",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod19",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod20",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod21",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod22",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod23",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod24",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod25",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod26",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod27",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod28",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod29",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod30",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod31",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod32",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod33",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod34",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod35",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod36",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod37",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod38",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod39",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod40",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod41",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod42",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod43",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod44",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod45",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod46",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod47",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod48",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod49",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod50",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod51",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod52",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod53",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod54",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod55",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod56",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod57",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod58",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod59",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod60",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod61",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod62",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod63",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod64",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod65",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod66",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod67",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod68",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod69",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod70",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod71",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod72",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod73",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod74",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod75",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod76",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod77",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod78",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod79",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod80",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod81",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod82",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod83",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod84",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod85",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod86",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod87",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod88",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod89",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod90",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod91",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod92",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod93",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod94",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod95",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod96",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod97",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod98",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod99",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod100",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod101",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod102",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod103",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod104",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod105",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod106",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod107",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod108",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod109",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod110",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod111",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod112",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod113",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod114",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod115",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod116",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod117",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod118",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod119",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod120",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod121",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod122",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod123",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod124",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod125",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod126",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod127",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod128",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod129",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod130",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod131",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod132",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod133",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod134",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod135",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod136",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod137",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod138",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod139",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod140",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod141",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod142",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod143",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod144",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod145",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod146",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod147",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod148",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod149",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod150",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod151",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod152",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod153",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod154",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod155",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod156",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod157",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod158",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod159",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod160",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod161",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod162",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod163",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod164",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod165",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod166",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod167",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod168",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod169",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod170",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod171",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod172",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod173",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod174",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod175",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod176",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod177",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod178",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod179",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod180",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod181",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod182",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod183",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod184",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod185",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod186",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod187",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod188",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod189",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod190",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod191",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod192",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod193",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod194",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod195",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod196",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod197",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod198",
	    ),
	    array(
	     "path"       => array(
			      "/test",
			      "/nomethod",
			     ),
	     "attributes" => array(),
	     "method"     => "noMethod199",
	    ),
	   );

?>
