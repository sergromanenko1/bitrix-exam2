<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach ($arResult["ITEMS"] as &$item) {
	if (!empty($item["DISPLAY_PROPERTIES"]["NAME_EN"]["VALUE"])) {
		$item["NAME"] = $item["DISPLAY_PROPERTIES"]["NAME_EN"]["VALUE"];
	}
	unset($item["DISPLAY_PROPERTIES"]["NAME_EN"]);

	if (!empty($item["DISPLAY_PROPERTIES"]["ANONCE_EN"]["VALUE"]["TEXT"])) {
		$item["PREVIEW_TEXT"] = $item["DISPLAY_PROPERTIES"]["ANONCE_EN"]["VALUE"]["TEXT"];
	}
	unset($item["DISPLAY_PROPERTIES"]["ANONCE_EN"]);
}
