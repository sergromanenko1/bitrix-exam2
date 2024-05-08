<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

class Simplecomp2 extends CBitrixComponent
{
	public function executeComponent()
	{
		global $USER, $APPLICATION;

		if (!$this->isIblockModuleInstalled() || $this->emptyParams()) {
			return;
		}

		if($this->startResultCache(false, [$USER->GetGroups(), CDBResult::GetNavParams($this->arParams["N_PAGE_SIZE"])])) {
			try {
				$products = $this->getProducts();
			
				$this->arResult["ELEMENTS"]    = $this->getElements($products);
				$this->arResult["PRICE_RANGE"] = $this->getProductPriceRange($products);
				$this->arResult["COUNT"]       = count($this->arResult["ELEMENTS"]);
			} catch (Exception) {
				$this->abortResultCache();
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return;
			}

			$this->setResultCacheKeys(["COUNT", "ELEMENTS", "PRICE_RANGE"]);
			$this->includeComponentTemplate();
		}

		$this->setPanelButtons();
		$APPLICATION->SetTitle(Loc::getMessage('TITLE') . $this->arResult["COUNT"]);
		$this->showPriceRange();
	}

	public function onPrepareComponentParams($arParams)
	{
		foreach (["PRODUCTS_IBLOCK_ID", "CLASSIFICATOR_IBLOCK_ID", "N_PAGE_SIZE", "CACHE_TIME"] as $key) {
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
			$this->arParams["PROCDUCTS_IBLOCK_ID"],
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

	protected function getItems($params)
	{
		$result = CIBlockElement::GetList(
			[],
			$params["filter"] ?? [],
			false,
			$params["nav"] ?? false,
			$params["select"] ?? []
		);
		
		if (array_search("DETAIL_PAGE_URL", $params["select"])) {
			$result->SetUrlTemplates($this->arParams["DETAIL_URL"]);
		}

		if (!empty($params["nav"])) {
			$this->arResult["NAV_STRING"] = $result->GetPageNavStringEx($navComponentObject, Loc::getMessage("NAV_SUBTITLE"));
		}

		$elements = [];

		while ($element = $result->GetNext()) {
			foreach (array_keys($element) as $key) {
				if (preg_match("/^~/", $key) || (!preg_match("/VALUE$/", $key) && !in_array($key, $params["select"]))) {
					unset($element[$key]);
				}
			}
			$elements[] = $element;
		}

		return $elements;
	}

	protected function getProducts()
	{
		$products = $this->getItems([
			"filter" => [
				"CHECK_PERMISSIONS"                                => "Y",
				"ACTIVE"                                           => "Y",
				"IBLOCK_ID"                                        => $this->arParams["PRODUCTS_IBLOCK_ID"],
				"!PROPERTY_{$this->arParams['PROPERTY_LINK_KEY']}" => false,
			],
			"select" => [
				"ID",
				"NAME",
				"PROPERTY_PRICE",
				"PROPERTY_MATERIAL",
				"PROPERTY_ARTNUMBER",
				"PROPERTY_{$this->arParams['PROPERTY_LINK_KEY']}",
				"DETAIL_PAGE_URL",
			],
		]);

		foreach ($products as &$product) {
			$arButtons = $this->getPanelButtons($product["ID"]);

			$product["EDIT_LINK"]   = $arButtons["edit"]["edit_element"]["ACTION_URL"] ?? "";
			$product["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"] ?? "";
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

		$prices =  array_column($products, "PROPERTY_PRICE_VALUE");

		$priceRange = [
			"MIN" => min($prices),
			"MAX" => max($prices),
		];

		return $priceRange;
	}

	protected function getElements($products)
	{
		$propertyLinkKey = "PROPERTY_{$this->arParams['PROPERTY_LINK_KEY']}_VALUE";
		$firmIds         = array_unique(array_column($products, $propertyLinkKey));

		if (!$firmIds) {
			return [];
		}

		$firmParams = [
			"filter" => [
				"ACTIVE"            => "Y",
				"CHECK_PERMISSIONS" => "Y",
				"IBLOCK_ID"         => $this->arParams["CLASSIFICATOR_IBLOCK_ID"],
				"ID"                => $firmIds,
			],
			"select" => ["ID", "NAME"],
		];

		if (!empty($this->arParams["N_PAGE_SIZE"])) {
			$firmParams["nav"] = [
				"nPageSize" => $this->arParams["N_PAGE_SIZE"],
			];
		}

		$firms = $this->getItems($firmParams);

		foreach ($firms as &$firm) {
			$firm["PRODUCTS"] = [];

			foreach ($products as $product) {
				if ($product[$propertyLinkKey] == $firm["ID"]) {
					unset($product[$propertyLinkKey]);
					$firm["PRODUCTS"][] = $product;
				}
			}
		}

		return $firms;
	}

	protected function showPriceRange() {
		global $APPLICATION;

		if (!empty($this->arResult["PRICE_RANGE"]["MAX"]) && !empty($this->arResult["PRICE_RANGE"]["MIN"])) {
			$APPLICATION->SetPageProperty(
				"SIMPLECOMP2",
				sprintf(
					'<div style="color:red; margin: 34px 15px 35px 15px">' . Loc::GetMessage("PRICE_RANGE") .'</div>',
					(int) $this->arResult["PRICE_RANGE"]["MAX"],
					(int) $this->arResult["PRICE_RANGE"]["MIN"]
				),
			);
		}
	}
}
