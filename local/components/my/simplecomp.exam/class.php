<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Model\Section;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\Elements\ElementProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\UI\PageNavigation;

class Simplecomp extends CBitrixComponent
{
	protected $pageNavigation;

	public function executeComponent()
	{
		global $APPLICATION;

		if (!$this->isIblockModuleInstalled() || $this->emptyParams()) {
			return;
		}

		$this->setPageNavigation();

		if(Context::getCurrent()->getRequest()->get("F") || $this->startResultCache(false, [$this->pageNavigation->getCurrentPage()]))
		{
			try {
				$productSections = $this->getProductSections();
				$products        = $this->getProducts(array_column($productSections, "ID"));

				$this->arResult["COUNT"]       = count($products);
				$this->arResult["PRICE_RANGE"] = $this->getProductPriceRange($products);
				$this->arResult["ELEMENTS"]    = $this->getElements($productSections, $products);
				$this->arResult["NAV_OBJECT"]  = $this->pageNavigation;
			} catch (Exception) {
				$this->abortResultCache();
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return;
			}

			$this->setResultCacheKeys(["COUNT", "PRICE_RANGE", "NAV_OBJECT"]);
			$this->includeComponentTemplate();
		}

		$this->setPanelButtons();
		$APPLICATION->SetTitle(Loc::GetMessage("TITLE") . $this->arResult["COUNT"]);
		$this->showPriceRange();
		$this->showPageNavigation();
	}

	public function onPrepareComponentParams($arParams)
	{
		foreach (["IBLOCK_NEWS_ID", "IBLOCK_CATALOG_ID", "CACHE_TIME", "N_PAGE_SIZE"] as $key) {
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
		foreach ($this->arParams as $key => $param) {
			if (false === strpos($key, "N_PAGE_SIZE") && empty($param)) {
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return true;
			}
		}
		return false;
	}

	protected function getPanelButtons($elementId = 0) {
		return CIBlock::GetPanelButtons(
			$this->arParams["IBLOCK_CATALOG_ID"],
			$elementId,
			0,
			array("SECTION_BUTTONS" => false, "SESSID" => false)
		);
	}

	protected function setPanelButtons() {
		global $APPLICATION;

		if ($APPLICATION->GetShowIncludeAreas()) {
			$buttons = $this->getPanelButtons();

			$this->addIncludeAreaIcons(
				array_merge(
					CIBlock::GetComponentMenu(
						$APPLICATION->GetPublicShowMode(),
						$this->getPanelButtons()
					),
					[
						[
							"TITLE"          => Loc::getMessage("PARAMS_MENU_TITLE"),
							"URL"            => $buttons["submenu"]["element_list"]["ACTION_URL"],
							"IN_PARAMS_MENU" => true,
						]
					]
				)
			);
		}
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
		$additionalFilter = [];

		if (Context::getCurrent()->getRequest()->get("F")) {
			$additionalFilter = [
				[
					"LOGIC" => "OR",
					[
						"<=PRICE.VALUE"   => 1700,
						"=MATERIAL.VALUE" => "Дерево, ткань",
					],
					[
						"<=PRICE.VALUE" => 1500,
						"=MATERIAL.VALUE" => "Металл, пластик",
					],
				]
			];
		}

		$products = ElementProductTable::getList([
			"select" => [
				"ID",
				"CODE",
				"IBLOCK_SECTION_ID",
				"NAME",
				"MATERIAL_VALUE"  => "MATERIAL.VALUE",
				"ARTNUMBER_VALUE" => "ARTNUMBER.VALUE",
				"PRICE_VALUE"     => "PRICE.VALUE",
			],
			"filter" => array_merge(
				$additionalFilter,
				[
					"=ACTIVE"            => "Y",
					"=IBLOCK_SECTION_ID" => $productSectionIds
				]
			),
			"order" => [
				"NAME" => "ASC",
				"SORT" => "ASC",
			]
		])->fetchAll();

		foreach ($products as &$product) {
			$arButtons = $this->getPanelButtons($product["ID"]);

			$product["EDIT_LINK"]       = $arButtons["edit"]["edit_element"]["ACTION_URL"] ?? "";
			$product["DELETE_LINK"]     = $arButtons["edit"]["delete_element"]["ACTION_URL"] ?? "";
			$product["DETAIL_PAGE_URL"] = CIBlock::ReplaceDetailUrl($this->arParams["DETAIL_PAGE_URL"], $product, false, "E");
		}

		return $products;
	}

	protected function getProductPriceRange($products)
	{
		$priceRange = [
			"MIN" => 0,
			"MAX" => 0,
		];

		if (!$products) {
			return $priceRange;
		}

		$prices =  array_column($products, "PRICE_VALUE");

		$priceRange = [
			"MIN" => min($prices),
			"MAX" => max($prices),
		];

		return $priceRange;
	}

	protected function getElements($productSections, $products)
	{
		$news = [];

		foreach(array_column($productSections, "UF_NEWS_LINK") as $sectionNews) {
			$news = array_merge($news, $sectionNews);
		}
		$news = array_unique($news);

		$filter = [
			"=ACTIVE"    => "Y",
			"=IBLOCK_ID" => $this->arParams["IBLOCK_NEWS_ID"],
			"=ID"        => $news,
		];

		$news = ElementTable::getList([
			"select" => ["ID", "NAME", "ACTIVE_FROM"],
			"filter" => $filter,
			'limit'  => $this->pageNavigation->getLimit(),
			'offset' => $this->pageNavigation->getOffset(),
		])->fetchAll();

		if ($this->arParams["N_PAGE_SIZE"]) {
			$this->pageNavigation->setRecordCount(ElementTable::getCount($filter));
		}

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
	
			$newsItem["ACTIVE_FROM"] = $newsItem["ACTIVE_FROM"]->format('d.m.Y');
		}

		return $news;
	}

	protected function showPriceRange() {
		global $APPLICATION;

		if (!empty($this->arResult["PRICE_RANGE"]["MAX"]) && !empty($this->arResult["PRICE_RANGE"]["MIN"])) {
			$APPLICATION->SetPageProperty(
				"SIMPLECOMP",
				sprintf(
					'<div style="color:red; margin: 34px 15px 35px 15px">' . Loc::GetMessage("PRICE_RANGE") .'</div>',
					(int) $this->arResult["PRICE_RANGE"]["MAX"],
					(int) $this->arResult["PRICE_RANGE"]["MIN"]
				),
			);
		}
	}

	protected function setPageNavigation()
	{
		$this->pageNavigation = new PageNavigation("news-nav");
		$this->pageNavigation->setPageSize($this->arParams["N_PAGE_SIZE"])->initFromUri();
	}

	protected function showPageNavigation()
	{
		global $APPLICATION;

		if ($this->arParams["N_PAGE_SIZE"] && 1 < $this->arResult["NAV_OBJECT"]->getPageCount()) {
			$APPLICATION->IncludeComponent(
				"bitrix:main.pagenavigation",
				"",
				array(
					"NAV_OBJECT" => $this->arResult["NAV_OBJECT"],
					"SEF_MODE"   => "N",
				),
				false
			);
		}
	}
}
