<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Model\Section;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\Elements\ElementProductTable;
use Bitrix\Main\Loader;

class Simplecomp extends CBitrixComponent
{
	public function executeComponent()
	{
		global $APPLICATION;

		if (!$this->isIblockModuleInstalled() || $this->emptyParams()) {
			return;
		}

		if($this->startResultCache())
		{
			try {
				$productSections = $this->getProductSections();
				$products        = $this->getProducts(array_column($productSections, "ID"));

				$this->arResult["COUNT"] = count($products);
				$this->arResult["ELEMENTS"] = $this->getElements($productSections, $products);
			} catch (Exception) {
				$this->abortResultCache();
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return;
			}

			$this->setResultCacheKeys(["COUNT", "ELEMENTS"]);
			$this->includeComponentTemplate();
		}
		$APPLICATION->SetTitle(Loc::GetMessage("TITLE") . $this->arResult["COUNT"]);
	}

	public function onPrepareComponentParams($arParams)
	{
		foreach (["IBLOCK_NEWS_ID", "IBLOCK_CATALOG_ID", "CACHE_TIME"] as $key) {
			$arParams[$key] = max(0, (int) $arParams[$key]);
		}

		return $arParams;
	}

	protected function isIblockModuleInstalled() {
		if(!Loader::includeModule("iblock"))
		{
			ShowError(Loc::getMessage("IBLOCK_MODULE_NOT_INSTALLED"));
			return false;
		}
		return true;
	}

	protected function emptyParams() {
		foreach ($this->arParams as $param) {
			if (empty($param)) {
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return true;
			}
		}
		return false;
	}

	protected function getProductSections()
	{
		$productSectionEntity = Section::compileEntityByIblock($this->arParams["IBLOCK_CATALOG_ID"]);

		if (!$productSectionEntity) {
			return [];
		}

		return $productSectionEntity::getList([
			"select" => ["ID", "NAME", "UF_NEWS_LINK"],
			"filter" => [
				"=ACTIVE"        => "Y",
				"=IBLOCK_ID"     => $this->arParams["IBLOCK_CATALOG_ID"],
				"!=UF_NEWS_LINK" => false,
			]
		])->fetchAll();
	}

	protected function getProducts($productSectionIds)
	{
		$products = ElementProductTable::getList([
			"select" => [
				"CODE",
				"IBLOCK_SECTION_ID",
				"NAME",
				"MATERIAL_VALUE"  => "MATERIAL.VALUE",
				"ARTNUMBER_VALUE" => "ARTNUMBER.VALUE",
				"PRICE_VALUE"     => "PRICE.VALUE",
			],
			"filter" => [
				"=ACTIVE"           => "Y",
				"=IBLOCK_SECTION_ID" => $productSectionIds,
			],
			"order" => [
				"NAME" => "ASC",
				"SORT" => "ASC",
			]
		])->fetchAll();

		foreach ($products as &$product) {
			$product["DETAIL_PAGE_URL"] = CIBlock::ReplaceDetailUrl($this->arParams["DETAIL_PAGE_URL"], $product, false, "E");
		}

		return $products;
	}

	protected function getElements($productSections, $products)
	{
		$news = [];

		foreach(array_column($productSections, "UF_NEWS_LINK") as $sectionNews) {
			$news = array_merge($news, $sectionNews);
		}
		$news = array_unique($news);

		$news = ElementTable::getList([
			"select" => ["ID", "NAME", "ACTIVE_FROM"],
			"filter" => [
				"=ACTIVE"    => "Y",
				"=IBLOCK_ID" => $this->arParams["IBLOCK_NEWS_ID"],
				"=ID"        => $news,
			]
		])->fetchAll();

		foreach ($news as &$newsItem) {
			$newsProductSections = array_filter($productSections, function($productSection) use ($newsItem) {
				return in_array($newsItem["ID"], $productSection["UF_NEWS_LINK"]);
			});

			$newsItem["PRODUCT_SECTION_NAMES"] = array_column($newsProductSections, "NAME");

			$newsProductSectionIds = array_column($newsProductSections, "ID");
			$newsItem["PRODUCTS"] = array_values(
				array_filter(
					$products,
					function($product) use ($newsProductSectionIds) {
						return in_array($product["IBLOCK_SECTION_ID"], $newsProductSectionIds);
					}
				)
			);

			foreach ($newsItem["PRODUCTS"] as &$product) {
				unset($product["IBLOCK_SECTION_ID"]);
			}
	
			$newsItem["ACTIVE_FROM"] = $newsItem["ACTIVE_FROM"]->format('d.m.Y');
			unset($newsItem["ID"]);
		}

		return $news;
	}
}
