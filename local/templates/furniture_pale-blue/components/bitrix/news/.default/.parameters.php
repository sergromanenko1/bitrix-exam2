<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"SET_SPECIALDATE" => Array(
		"PARENT" => "LIST_SETTINGS",
		"NAME" => GetMessage("SET_SPECIALDATE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
);

$arTemplateParameters = array(
	"CANONICAL_IBLOCK_ID" => Array(
		"PARENT" => "DETAIL_SETTINGS",
		"NAME" => GetMessage("CANONICAL_IBLOCK_ID"),
		"TYPE" => "STRRING",
	),
	"COMPLAINT_AJAX" => Array(
		"PARENT" => "DETAIL_SETTINGS",
		"NAME" => GetMessage("COMPLAINT_AJAX"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
);
?>
