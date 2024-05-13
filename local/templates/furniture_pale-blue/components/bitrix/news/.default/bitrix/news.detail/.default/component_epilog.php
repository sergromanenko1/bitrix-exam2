<?php
if ($arResult["CANONICAL"]) {
	$APPLICATION->SetPageProperty('canonical', $arResult["CANONICAL"]);
}
