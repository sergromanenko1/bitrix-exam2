<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Простой компонент3");
?><?$APPLICATION->IncludeComponent(
	"my:simplecomp.exam3", 
	".default", 
	array(
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"IBLOCK_PROPERTY_AUTHOR_KEY" => "AUTHOR",
		"NEWS_IBLOCK_ID" => "1",
		"USER_PROPERTY_AUTHOR_TYPE_KEY" => "UF_AUTHOR_TYPE",
		"COMPONENT_TEMPLATE" => ".default",
		"N_PAGE_SIZE" => "1"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>