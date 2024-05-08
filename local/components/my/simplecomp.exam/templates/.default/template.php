<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$this->setFrameMode(true);
?>
<p>
	<?= Loc::getMessage("SUBTITLE") ?>: <a href="/ex2/simplecomp/?F=Y">/ex2/simplecomp/?F=Y</a><br>
	---
</p>
<b><?= Loc::getMessage("SUBTITLE") ?></b>
<ul>
	<?
	$productId = 0;

	foreach ($arResult["ELEMENTS"] as $element) {
		printf(
			"<li>
				<b>%s</b> - %s (%s)",
			HtmlFilter::encode($element["NAME"]),
			HtmlFilter::encode($element["ACTIVE_FROM"]),
			HtmlFilter::encode(implode(", ", $element["PRODUCT_SECTION_NAMES"]))
		);

		if ($element["PRODUCTS"]) {
			echo "<ul>";
			foreach ($element["PRODUCTS"] as $productItem) {
				$productId++;

				$this->AddEditAction(
					$productId,
					$productItem["EDIT_LINK"], 
					CIBlock::GetArrayByID($arParams["IBLOCK_CATALOG_ID"], "ELEMENT_EDIT")
				);
				$this->AddDeleteAction(
					$productId,
					$productItem["DELETE_LINK"], 
					CIBlock::GetArrayByID($arParams["IBLOCK_CATALOG_ID"], "ELEMENT_DELETE")
				);

				printf(
					'<li id="%s">%s - %s - %s - %s - (%s)</li>',
					HtmlFilter::encode($this->GetEditAreaId($productId)),
					HtmlFilter::encode($productItem["NAME"]),
					(int) $productItem["PRICE_VALUE"],
					HtmlFilter::encode($productItem["MATERIAL_VALUE"]),
					(int) $productItem["ARTNUMBER_VALUE"],
					HtmlFilter::encode($productItem["DETAIL_PAGE_URL"]),
				);
			}
			echo "</ul>";
		}

		echo "</li>";
	}
	?>
</ul>
<? if ($arParams["N_PAGE_SIZE"] && 1 < $arResult["NAV_OBJECT"]->getPageCount()) : ?>
	<p>---</p>
	<p>
		<b><?= Loc::getMessage("NAV_TITLE"); ?></b>
	</p>
<? endif; ?>
