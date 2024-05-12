<?
use Bitrix\Main\UserTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Mail\Event;

function CheckUserCount()
{
	$now      = new DateTime();
	$lastDate = Option::get("main", "lastDate");

	$admins = UserTable::getList([
		"runtime" => [new Reference("GROUP", UserGroupTable::class, ["=this.ID" => "ref.USER_ID"])],
		"select" => ["EMAIL", "DATE_REGISTER"],
		"filter" => [
			"=ACTIVE"         => "Y",
			"=GROUP.GROUP_ID" => 1,
		],
	])->fetchAll();
	
	$lastDate = DateTime::createFromTimestamp($lastDate ? $lastDate : $admins[0]["DATE_REGISTER"]->getTimestamp());

	Event::send([
		"EVENT_NAME" => "COUNT_OF_REGISTERED_USERS",
		"LID"        => SITE_ID,
		"C_FIELDS"   => [
			"ADMIN_EMAILS" => implode(",", array_column($admins, "EMAIL")),
			"DAYS"         => $now->getDiff($lastDate)->days,
			"COUNT"        => UserTable::getCount([
				"=ACTIVE"         => "Y",
				">=DATE_REGISTER" => $lastDate,
			]),
		],
	]);
	Option::set("main", "lastDate", $now->getTimestamp());

	return "CheckUserCount();";
}
