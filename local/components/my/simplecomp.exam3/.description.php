<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME"       => Loc::getMessage("SIMPLECOMP_EXAM2_NAME"),
	"CACHE_PATH" => "Y",
	"PATH"       => array(
		"ID" => "exam2",
	),
);
?>
