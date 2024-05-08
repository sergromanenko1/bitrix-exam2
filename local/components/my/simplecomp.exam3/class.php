<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Iblock\Elements\ElementNewsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\PageNavigation;

class Simplecomp3 extends CBitrixComponent
{
	protected $pageNavigation;
	protected $userId;

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$this->userId = $USER->GetID();
		
		if (!$this->isAuthorized() || !$this->isIblockModuleInstalled() || $this->emptyParams()) {
			return;
		}

		$this->setPageNavigation();

		if ($this->startResultCache(false, [$this->userId, $this->pageNavigation->getCurrentPage()])) {
			try {
				$this->arResult["ELEMENTS"]   = $this->getElements();
				$this->arResult["NAV_OBJECT"] = $this->pageNavigation;
			} catch (Exception) {
				$this->abortResultCache();
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return;
			}

			$this->setResultCacheKeys(["COUNT_NEWS", "ELEMENTS", "NAV_OBJECT"]);
			$this->includeComponentTemplate();
		}

		$this->setPanelButtons();
		$APPLICATION->SetTitle(Loc::getMessage("TITLE") . $this->arResult["COUNT_NEWS"]);
		$this->showPageNavigation();
	}

	public function onPrepareComponentParams($arParams)
	{
		foreach (["NEWS_IBLOCK_ID", "CACHE_TIME", "N_PAGE_SIZE"] as $key) {
			$arParams[$key] = max(0, (int) $arParams[$key]);
		}

		return $arParams;
	}

	protected function isAuthorized() {
		if (!$this->userId) {
			ShowError(Loc::getMessage("FORBIDDEN"));
			return false;
		}
		return true;
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
			$this->arParams["NEWS_IBLOCK_ID"],
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

	protected function getAuthors() {
		$authorType = UserTable::getList([
			"select" => ["{$this->arParams['USER_PROPERTY_AUTHOR_TYPE_KEY']}"],
			"filter" => [
				"=ACTIVE" => "Y",
				"=ID"     => $this->userId,
			],
		])->fetch();
		$authorType = $authorType ? array_values($authorType)[0] : (int) $authorType;

		$filter = [
			"=ACTIVE"                                             => "Y",
			"={$this->arParams['USER_PROPERTY_AUTHOR_TYPE_KEY']}" => $authorType,
			"!=ID"                                                => $this->userId,
		];

		$authors = UserTable::getList([
			"select" => ["ID", "LOGIN"],
			"filter" => $filter,
			'limit'  => $this->pageNavigation->getLimit(),
			'offset' => $this->pageNavigation->getOffset(),
		])->fetchAll();

		if ($this->arParams["N_PAGE_SIZE"]) {
			$this->pageNavigation->setRecordCount(UserTable::getCount($filter));
		}

		return $authors;
	}

	protected function getNews($authors) {
		$news = ElementNewsTable::getList([
			"select" => [
				"ID",
				"NAME",
				"ACTIVE_FROM",
				"{$this->arParams['IBLOCK_PROPERTY_AUTHOR_KEY']}_VALUE" => "{$this->arParams['IBLOCK_PROPERTY_AUTHOR_KEY']}.VALUE",
			],
			"filter" => [
				"=IBLOCK_ID" => $this->arParams["NEWS_IBLOCK_ID"],
				"=ACTIVE"    => "Y",
			],
		])->fetchAll();

		$countNews = count($news);

		for ($i = $countNews - 1; $i >= 0; $i--) {
			$authorValues = [$news[$i]["AUTHOR_VALUE"]];

			for ($j = $i + 1; $j < $countNews; $j++) {
				if (!empty($news[$j]) && $news[$i]["NAME"] === $news[$j]["NAME"]) {
					$authorValues = array_merge($authorValues, $news[$j]["AUTHOR_VALUE"]);

					unset($news[$j]);
				}
			}

			$news[$i]["AUTHOR_VALUE"] = $authorValues;
			$news[$i]["ACTIVE_FROM"] = $news[$i]["ACTIVE_FROM"]->format("d.m.Y");
		}

		$authorIds = array_column($authors, "ID");
		$news = array_filter($news, function($itemNews) use ($authorIds) {
			return array_intersect($itemNews["AUTHOR_VALUE"], $authorIds) &&
				!in_array($this->userId, $itemNews["AUTHOR_VALUE"]);
		});

		foreach ($news as &$newsItem) {
			$arButtons = $this->getPanelButtons($newsItem["ID"]);

			$newsItem["EDIT_LINK"]   = $arButtons["edit"]["edit_element"]["ACTION_URL"] ?? "";
			$newsItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"] ?? "";
		}

		$this->arResult["COUNT_NEWS"] = count($news);

		return $news;
	}

	protected function getElements() {
		$authors = $this->getAuthors();
		$news    = $this->getNews($authors);

		foreach ($authors as &$author) {
			unset($author["UF_AUTHOR_TYPE"]);

			$author["NEWS"] = array_filter($news, function($itemNews) use ($author) {
				return in_array($author["ID"], $itemNews["AUTHOR_VALUE"]);
			});

			$author["NEWS"] = array_map(function($itemNews) {
				unset($itemNews["AUTHOR_VALUE"]);
				return $itemNews;
			}, $author["NEWS"]);
		}

		return $authors;
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
