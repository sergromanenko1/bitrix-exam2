<?php
$arResult["SPECIALDATE"] = ("Y" === $arParams["SET_SPECIALDATE"]) ? ($arResult["ITEMS"][0]["ACTIVE_FROM"] ?? "") : "";
$this->__component->setResultCacheKeys(["SPECIALDATE"]);