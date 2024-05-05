<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
	"PARAMETERS" => [
		"IBLOCK_CATALOG_ID" => [
			"PARENT" => "BASE",
			"NAME"   => Loc::GetMessage("IBLOCK_CATALOG_ID"),
			"TYPE"   => "STRING",
		],
		"IBLOCK_NEWS_ID" => [
			"PARENT" => "BASE",
			"NAME"   => Loc::GetMessage("IBLOCK_NEWS_ID"),
			"TYPE"   => "STRING",
		],
		"PROPERTY_CATALOG_KEY" => [
			"PARENT" => "BASE",
			"NAME"   => Loc::GetMessage("PROPERTY_CATALOG_KEY"),
			"TYPE"   => "STRING",
		],
		"DETAIL_PAGE_URL" => [
			"PARENT" => "BASE",
			"NAME"   => Loc::GetMessage("DETAIL_PAGE_URL"),
			"TYPE"   => "STRING",
		],
		"CACHE_TIME" => [
			"DEFAULT" => 36000000
		],
	],
];
