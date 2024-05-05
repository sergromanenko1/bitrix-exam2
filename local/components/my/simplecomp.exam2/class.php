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

		if($this->startResultCache(false, $USER->GetGroups())) {
			try {
				$products = $this->getProducts();
			
				$this->arResult["ELEMENTS"] = $this->getElements($products);
				$this->arResult["COUNT"] = count($this->arResult["ELEMENTS"]);
			} catch (Exception) {
				$this->abortResultCache();
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return;
			}

			$this->setResultCacheKeys(["COUNT", "ELEMENTS"]);
			$this->includeComponentTemplate();
		}

		if($APPLICATION->GetShowIncludeAreas()) {
			$this->addIncludeAreaIcons(
				CIBlock::GetComponentMenu(
					$APPLICATION->GetPublicShowMode(),
					$this->getPanelButtons()
				)
			);
		}
		$APPLICATION->SetTitle(Loc::getMessage('TITLE') . $this->arResult["COUNT"]);
	}

	public function onPrepareComponentParams($arParams)
	{
		foreach (["PRODUCTS_IBLOCK_ID", "CLASSIFICATOR_IBLOCK_ID", "CACHE_TIME"] as $key) {
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

	protected function getPanelButtons($elementId = 0) {
		return CIBlock::GetPanelButtons(
			$this->arParams["PROCDUCTS_IBLOCK_ID"],
			$elementId,
			0,
			array("SECTION_BUTTONS" => false, "SESSID" => false)
		);
	}

	protected function getItems($params)
	{
		$result = CIBlockElement::GetList(
			[],
			$params["filter"] ?? [],
			false,
			false,
			$params["select"] ?? []
		);
		
		if (array_search("DETAIL_PAGE_URL", $params["select"])) {
			$result->SetUrlTemplates($this->arParams["DETAIL_URL"]);
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

	protected function getElements($products)
	{
		$propertyLinkKey = "PROPERTY_{$this->arParams['PROPERTY_LINK_KEY']}_VALUE";
		$firmIds         = array_unique(array_column($products, $propertyLinkKey));

		if (!$firmIds) {
			return [];
		}

		$firms = $this->getItems([
			"filter" => [
				"ACTIVE"            => "Y",
				"CHECK_PERMISSIONS" => "Y",
				"IBLOCK_ID"         => $this->arParams['CLASSIFICATOR_IBLOCK_ID'],
				"ID"                => $firmIds,
			],
			"select" => ["ID", "NAME"],
		]);

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
}
