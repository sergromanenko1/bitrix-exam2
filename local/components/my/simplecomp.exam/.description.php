<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME"       => Loc::GetMessage("COMPONENT_NAME"),
	"CACHE_PATH" => "Y",
	"PATH"       => [
		"NAME" => Loc::GetMessage("PATH_NAME"),
		"ID"    => "exam2",
	],
);
