<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";

use Bitrix\Main\Loader;

Loader::includeModule("iblock");
$currentIblockId = $_GET["iblock_id"];

$resSections = \CIBlockSection::GetTreeList(
	["IBLOCK_ID" => $currentIblockId],
	["ID", "NAME", "DEPTH_LEVEL"]
);
$sections = [];
while ($section = $resSections->GetNext()) {
  $sections[$section["ID"]] = str_repeat(" . ", $section["DEPTH_LEVEL"] - 1) . $section["NAME"];
}

echo json_encode($sections);
