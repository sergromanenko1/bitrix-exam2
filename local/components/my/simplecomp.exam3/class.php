<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Iblock\Elements\ElementNewsTable;
use Bitrix\Main\Loader;

class Simplecomp3 extends CBitrixComponent
{
	protected $userId;

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$this->userId = $USER->GetID();
		
		if (!$this->isAuthorized() || !$this->isIblockModuleInstalled() || $this->emptyParams()) {
			return;
		}

		if ($this->startResultCache(false, $this->userId)) {
			try {
				$this->arResult["ELEMENTS"] = $this->getElements();
			} catch (Exception) {
				$this->abortResultCache();
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return;
			}

			$this->setResultCacheKeys(["COUNT_NEWS", "ELEMENTS"]);
			$this->includeComponentTemplate();
		}

		$APPLICATION->SetTitle(Loc::getMessage("TITLE") . $this->arResult["COUNT_NEWS"]);
	}

	public function onPrepareComponentParams($arParams)
	{
		foreach (["NEWS_IBLOCK_ID", "CACHE_TIME"] as $key) {
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
		foreach ($this->arParams as $param) {
			if (empty($param)) {
				ShowError(Loc::getMessage("WRONG_PARAMETERS"));
				return true;
			}
		}
		return false;
	}

	protected function getAuthors() {
		$authors = UserTable::getList([
			"select" => ["ID", "LOGIN", "{$this->arParams['USER_PROPERTY_AUTHOR_TYPE_KEY']}"],
			"filter" => [
				"=ACTIVE"                                              => "Y",
				"!={$this->arParams['USER_PROPERTY_AUTHOR_TYPE_KEY']}" => false,
			],
		])->fetchAll();

		$currentAuthor = array_filter($authors, function($author) {
			return $this->userId === $author["ID"];
		});
		$currentAuthor = array_shift($currentAuthor);

		$authors = array_filter($authors, function($author) use ($currentAuthor) {
			return $currentAuthor["ID"] !== $author["ID"] && $currentAuthor["UF_AUTHOR_TYPE"] === $author["UF_AUTHOR_TYPE"];
		});

		return $authors;
	}

	protected function getNews($authors) {
		$news = ElementNewsTable::getList([
			"select" => [
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
}
