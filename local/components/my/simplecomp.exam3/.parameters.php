<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"NEWS_IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME"   => Loc::getMessage("SIMPLECOMP_EXAM2_CAT_IBLOCK_ID"),
			"TYPE"   => "STRING",
		],
		"IBLOCK_PROPERTY_AUTHOR_KEY" => [
			"PARENT" => "BASE",
			"NAME"   => Loc::getMessage("IBLOCK_PROPERTY_AUTHOR_KEY"),
			"TYPE"   => "STRING",
		],
		"USER_PROPERTY_AUTHOR_TYPE_KEY" => [
			"PARENT" => "BASE",
			"NAME"   => Loc::getMessage("USER_PROPERTY_AUTHOR_TYPE_KEY"),
			"TYPE"   => "STRING",
		],
		"N_PAGE_SIZE" => [
			"NAME"   => Loc::getMessage('N_PAGE_SIZE'),
			"PARENT" => "BASE",
			"TYPE"   => "STRING",
		],
		"CACHE_TIME"  => [
			"DEFAULT"=>36000000
		],
	),
);
