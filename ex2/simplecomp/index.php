<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Простой компонент");
?><?$APPLICATION->IncludeComponent(
	"my:simplecomp.exam",
	".default",
	Array(
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"COMPONENT_TEMPLATE" => ".default",
		"DETAIL_PAGE_URL" => "catalog_exam/#SECTION_ID#/#ELEMENT_CODE#",
		"IBLOCK_CATALOG_ID" => "2",
		"IBLOCK_NEWS_ID" => "1",
		"PROPERTY_CATALOG_KEY" => "UF_NEWS_LINK"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>