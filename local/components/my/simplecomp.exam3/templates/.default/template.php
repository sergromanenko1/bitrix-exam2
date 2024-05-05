<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

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
					printf(
						"<li>%s - %s",
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
