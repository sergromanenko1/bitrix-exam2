<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Iblock\Elements\ElementCanonicalTable;

$arResult["CANONICAL"] = "";
$arParams["CANONICAL_IBLOCK_ID"] = (int) $arParams["CANONICAL_IBLOCK_ID"];

if ($arParams["CANONICAL_IBLOCK_ID"]) {
	$canonical = ElementCanonicalTable::getList([
		"limit" => 1,
		"select" => ["NAME"],
		"filter" => [
			"=IBLOCK_ID" => $arParams["CANONICAL_IBLOCK_ID"],
			"=NEWS_ID.VALUE" => $arParams['ELEMENT_ID'],
		],
	]);
	$arResult["CANONICAL"] = $canonical->fetch()['NAME'] ?? "";
}

$this->__component->setResultCacheKeys(["CANONICAL"]);
