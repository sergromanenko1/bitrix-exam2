<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;

class NewsDetailEpilog
{
	public function __construct(
		protected $arParams,
		protected $arResult,
		protected $application,
		protected $user
	)
	{
		$this->setCanonical();

		if ($this->isComplainRequest()) {
			$this->sendRespond();
		}
	}

	protected function setCanonical()
	{
		if ($this->arResult["CANONICAL"]) {
			$this->application->SetPageProperty("canonical", $this->arResult["CANONICAL"]);
		}
	}

	protected function isComplainRequest()
	{
		return "Y" === Context::getCurrent()->getRequest()->getQuery("complain");
	}

	protected function isAjaxMode()
	{
		return "Y" === $this->arParams["COMPLAINT_AJAX"];
	}

	protected function getUserInfo()
	{
		$info = Loc::getMessage("UNAUTHORIZED");
		
		if ($this->user->IsAuthorized()) {
			$info = UserTable::getList([
				"filter" => ["=ID" => $this->user->GetID()],
				"select" => ["ID", "LOGIN", "NAME", "LAST_NAME"],
			])->fetch();
			$info = implode(" ", array_filter($info));
		}

		return $info;
	}

	protected function createComplaintElement()
	{
		$complaint = new CIBlockElement;
		
		$complaintId = $complaint->Add([
			"IBLOCK_ID"   => COMPLAINTS_IBLOCK_ID,
			"NAME"        => Loc::getMessage("NAME"),
			"ACTIVE_FROM" => new DateTime(),
			"PROPERTY_VALUES" => [
				"USER" => $this->getUserInfo(),
				"NEWS" => $this->arResult["ID"],
			],
		]);
	
		$complaint->Update(
			$complaintId,
			["NAME" => str_replace("#COMLAINT_ID#", $complaintId, Loc::getMessage("NAME"))]
		);
	
		if (!$complaintId) {
			$complaintId = "error";
		}

		return $complaintId;
	}

	protected function sendRespond()
	{
		$complaintId = $this->createComplaintElement();
		
		if ($this->isAjaxMode()) {
			$this->application->RestartBuffer();
			echo $complaintId;
			die;
		} else {
			$redirectPage = $this->application->GetCurPage();
			$redirectPage = $redirectPage . (false !== strpos($redirectPage, "?") ? "&" : "?") . "complaint_id={$complaintId}#complaint";
	
			LocalRedirect($redirectPage);
			die;
		}
	}
}
new NewsDetailEpilog($arParams, $arResult, $APPLICATION, $USER);
