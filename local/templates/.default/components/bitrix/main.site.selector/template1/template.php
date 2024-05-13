<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Text\HtmlFilter;
?>

<select onchange="location.href = this.value;">
	<?
	foreach ($arResult["SITES"] as $site) {
		printf(
			'<option value="%s"%s>%s</option>',
			HtmlFilter::encode($site["DIR"]),
			"Y" === $site["CURRENT"] ? " selected" : "",
			HtmlFilter::encode($site["LANG"]),
		);
	}
	?>
</select>
