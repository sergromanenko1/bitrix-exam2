<?
use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler("iblock", "OnBeforeIBlockElementUpdate", function(&$arFields)
{
	if ($arFields["IBLOCK_ID"] !== PRODUCTS_IBLOCK_ID) {
		return;
	}
	
	$product = Bitrix\Iblock\ElementTable::getList([
		"select" => ["SHOW_COUNTER"],
		"filter" => [
			"=ID"        => $arFields["ID"],
			"=IBLOCK_ID" => $arFields["IBLOCK_ID"]
		],
		"limit" => 1,
	])->fetch();

	if (MIN_SHOW_COUNTER <= $product["SHOW_COUNTER"] && "N" === $arFields["ACTIVE"])
	{
		global $APPLICATION;

		$APPLICATION->ThrowException("Товар невозможно деактивировать, у него {$product["SHOW_COUNTER"]} просмотров");

		return false;
	}
});

$eventManager->addEventHandler("main", "OnEpilog", function()
{
	if (defined("ERROR_404") && "Y" === ERROR_404) {
		global $APPLICATION;

		CEventLog::Add(array(
			"SEVERITY"      => "INFO",
			"AUDIT_TYPE_ID" => "ERROR_404",
			"MODULE_ID"     => "main",
			"DESCRIPTION"   => $APPLICATION->GetCurPage(),
		));
	}
});

$eventManager->addEventHandler("main", "OnBeforeEventAdd", function($event, $lid, &$arFields)
{
	if ("FEEDBACK_FORM" !== $event) {
		return;
	}

	global $USER;

	$arFields["AUTHOR"] = "Пользователь не авторизован, данные из формы: {$arFields['AUTHOR']}";

	if ($USER->IsAuthorized()) {
		$arFields["AUTHOR"] = sprintf(
			"Пользователь авторизован: %s (%s) %s, данные из формы: {$arFields['AUTHOR']}",
			$USER->GetID(),
			$USER->GetLogin(),
			$USER->GetFirstName(),
			$arFields['AUTHOR']
		);
	}

	CEventLog::Add(array(
		"SEVERITY"      => "INFO",
		"AUDIT_TYPE_ID" => "FEEDBACK_FORM",
		"MODULE_ID"     => "main",
		"DESCRIPTION"   => "Замена данных в отсылаемом письме – {$arFields['AUTHOR']}",
	));
});

$eventManager->addEventHandler("main", "OnBuildGlobalMenu", function(&$aGlobalMenu, &$aModuleMenu)
{
	global $USER;

	if (!$USER->IsAuthorized()) {
		return;
	}

	$userGroups = \Bitrix\Main\UserGroupTable::getList(array(
		"select" => ["GROUP_ID"],
		"filter" => array("=USER_ID" => $USER->getId()),
	))->fetchAll();
	$userGroups = array_column($userGroups, "GROUP_ID");

	if (!in_array(CONTENT_EDITORS_GROUP_ID, $userGroups)) {
		return;
	}

	// Remove parent items
	foreach ($aGlobalMenu as $key => $item)
	{
		if ("global_menu_content" !== $key)
		{
			unset($aGlobalMenu[$key]);
		}
	}

	// Remove child items
	foreach ($aModuleMenu as $i => $item)
	{
		if ("menu_iblock_/news" !== ($item["items_id"] ?? ""))
		{
			unset($aModuleMenu[$i]);
		}
	}
});

$eventManager->addEventHandler("main", "OnEpilog", function()
{
	if (!class_exists("\Bitrix\Iblock\Elements\ElementMetatagTable")) {
		return;
	}

	global $APPLICATION;

	$metatag = \Bitrix\Iblock\Elements\ElementMetatagTable::getList([
		"select" => [
			"TITLE_VALUE"       => "TITLE.VALUE",
			"DESCRIPTION_VALUE" => "DESCRIPTION.VALUE",
		],
		"filter" => [
			"=NAME"      => $APPLICATION->GetCurPage(),
			"=IBLOCK_ID" => METATAGS_IBLOCK_ID,
		],
		"limit" => 1,
	])->fetch();
	
	foreach(["TITLE_VALUE", "DESCRIPTION_VALUE"] as $property) {
		if (!empty($metatag[$property])) {
			$APPLICATION->SetPageProperty(
				strtolower(str_replace("_VALUE", "", $property)),
				$metatag[$property]
			);
		}
	}
});
