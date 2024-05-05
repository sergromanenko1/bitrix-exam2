<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

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
					printf(
						'<li>%s - %s - %s - %s (%s)</li>',
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
