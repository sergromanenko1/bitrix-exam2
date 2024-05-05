<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Простой компонет2");
?><?$APPLICATION->IncludeComponent(
	"my:simplecomp.exam2", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"PROCDUCTS_IBLOCK_ID" => "2",
		"CLASSIFICATOR_IBLOCK_ID" => "7",
		"DETAIL_URL" => "#SITE_DIR#/catalog_exam/#SECTION_ID#/#ELEMENT_CODE#”",
		"PROPERTY_LINK_KEY" => "FIRM",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"PRODUCTS_IBLOCK_ID" => "2"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>