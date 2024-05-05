<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$productId = 0;

$this->setFrameMode(true);
?>
<b><?= Loc::getMessage('SUBTITLE'); ?></b>
<ul>
	<? foreach ($arResult["ELEMENTS"] as $element) : ?>
		<li>
			<b><?= HtmlFilter::encode($element["NAME"]); ?></b>
			<ul>
				<?
				foreach ($element["PRODUCTS"] as $product) {
					$productId++;

					$this->AddEditAction(
						$productId,
						$product["EDIT_LINK"], 
						CIBlock::GetArrayByID($arParams["PROCDUCTS_IBLOCK_ID"], "ELEMENT_EDIT")
					);
					$this->AddDeleteAction(
						$productId,
						$product["DELETE_LINK"], 
						CIBlock::GetArrayByID($arParams["PROCDUCTS_IBLOCK_ID"], "ELEMENT_DELETE")
					);

					printf(
						'<li id="%s">%s - %s - %s - %s (%s)</li>',
						HtmlFilter::encode($this->GetEditAreaId($productId)),
						HtmlFilter::encode($product["NAME"]),
						(int) $product["PROPERTY_PRICE_VALUE"],
						HtmlFilter::encode($product["PROPERTY_MATERIAL_VALUE"]),
						(int) $product["PROPERTY_ARTNUMBER_VALUE"],
						HtmlFilter::encode($product["DETAIL_PAGE_URL"])
					);
				}
				?>
			</ul>
		</li>
	<? endforeach; ?>
</ul>
