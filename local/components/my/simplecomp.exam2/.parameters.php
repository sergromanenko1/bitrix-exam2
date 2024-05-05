<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	"PARAMETERS" => [
		"PRODUCTS_IBLOCK_ID" => [
			"NAME"   => Loc::getMessage('PRODUCTS_IBLOCK_ID'),
			"PARENT" => "BASE",
			"TYPE"   => "STRING",
		],
		"CLASSIFICATOR_IBLOCK_ID" => [
			"NAME"   => Loc::getMessage('CLASSIFICATOR_IBLOCK_ID'),
			"PARENT" => "BASE",
			"TYPE"   => "STRING",
		],
		"DETAIL_URL" => [
			"NAME"   => Loc::getMessage('DETAIL_URL'),
			"PARENT" => "BASE",
			"TYPE"   => "STRING",
		],
		"PROPERTY_LINK_KEY" => [
			"NAME"   => Loc::getMessage('PROPERTY_LINK_KEY'),
			"PARENT" => "BASE",
			"TYPE"   => "STRING",
		],
		"CACHE_TIME"  => [
			"DEFAULT" => 36000000
		],
	]
];
