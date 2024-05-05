<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$newsId = 0;

$this->setFrameMode(true);
?>

<b><?= Loc::getMessage("SUBTITLE"); ?></b>
<? if ($arResult["ELEMENTS"]) : ?>
	<ul>
		<?
		foreach ($arResult["ELEMENTS"] as $element) {
			printf(
				"<li>[%s] - %s",
				(int) $element["ID"],
				HtmlFilter::encode($element["LOGIN"])
			);

			if ($element["NEWS"]) {
				echo "<ul>";

				foreach ($element["NEWS"] as $itemNews) {
					$newsId++;

					$this->AddEditAction(
						$newsId,
						$itemNews["EDIT_LINK"], 
						CIBlock::GetArrayByID($arParams["NEWS_IBLOCK_ID"], "ELEMENT_EDIT")
					);
					$this->AddDeleteAction(
						$newsId,
						$itemNews["DELETE_LINK"], 
						CIBlock::GetArrayByID($arParams["NEWS_IBLOCK_ID"], "ELEMENT_DELETE")
					);
					printf(
						'<li id="%s">%s - %s',
						HtmlFilter::encode($this->GetEditAreaId($newsId)),
						HtmlFilter::encode($itemNews["NAME"]),
						HtmlFilter::encode($itemNews["ACTIVE_FROM"])
					);
				}

				echo "</ul>";
			}

			echo "</li>";
		}
		?>
	</ul>
<? endif; ?>
