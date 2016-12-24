<?php
/*******************************************************************************
 * rodzeta.importrealtyfeed - Import realty-feed to infoblock
 * Copyright 2016 Semenov Roman
 * MIT License
 ******************************************************************************/

namespace Rodzeta\Importrealtyfeed;

defined("B_PROLOG_INCLUDED") and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\{Application, Config\Option, Localization\Loc, Loader};

if (!$USER->isAdmin()) {
	$APPLICATION->authForm("ACCESS DENIED");
}

\CModule::IncludeModule("iblock");
$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages(__FILE__);

$tabControl = new \CAdminTabControl("tabControl", [
  [
		"DIV" => "edit1",
		"TAB" => Loc::getMessage("RODZETA_IMPORTREALTYFEED_MAIN_TAB_SET"),
		"TITLE" => Loc::getMessage("RODZETA_IMPORTREALTYFEED_MAIN_TAB_TITLE_SET"),
  ],
]);

$currentOptions = json_decode(Option::get("rodzeta.importrealtyfeed", "default", []), true);

?>

<?php

if ($request->isPost() && check_bitrix_sessid()) {
	if (!empty($request->getPost("save"))) {
		$currentOptions["iblock_content"] = $request->getPost("iblock_content");
		$currentOptions["section_content"] = $request->getPost("section_content");
		Option::set("rodzeta.importrealtyfeed", "default", json_encode($currentOptions, true));

		\CAdminMessage::showMessage([
	    "MESSAGE" => Loc::getMessage("RODZETA_IMPORTREALTYFEED_OPTIONS_SAVED"),
	    "TYPE" => "OK",
	  ]);
	}
}

$tabControl->begin();

?>

<script>
function RodzetaImportrealtyfeedUpdate($selectDest) {
	var $selectIblock = document.getElementById("iblock_content");
	var iblockId = $selectIblock.value;
	var selectedOption = $selectDest.getAttribute("data-value");
	BX.ajax.loadJSON("/bitrix/admin/rodzeta.importrealtyfeed/sectionoptions.php?iblock_id=" + iblockId, function (data) {
		var html = ["<option value=''>(выберите раздел)</option>"];
		for (var k in data) {
			var selected = selectedOption == k? "selected" : "";
			html.push("<option " + selected + " value='" + k + "'>" + data[k] + "</option>");
		}
		$selectDest.innerHTML = html.join("\n");
	});
};
</script>

<form method="post" action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>" type="get">
	<?= bitrix_sessid_post() ?>

	<?php $tabControl->beginNextTab() ?>

	<tr class="heading">
		<td colspan="2">Куда импортировать</td>
	</tr>

	<tr>
		<td class="adm-detail-content-cell-l" width="50%">
			<label>Инфоблок</label>
		</td>
		<td class="adm-detail-content-cell-r" width="50%">
			<?= GetIBlockDropDownListEx(
					$currentOptions["iblock_content"],
					"iblock_type",
					"iblock_content",
					[
						"MIN_PERMISSION" => "R",
					],
					"",
					"RodzetaImportrealtyfeedUpdate(document.getElementById('rodzeta-importrealtyfeed-catalogsection-id'));"
				) ?>
		</td>
	</tr>

	<tr>
		<td class="adm-detail-content-cell-l" width="50%">
			<label>Корневой раздел</label>
		</td>
		<td class="adm-detail-content-cell-r" width="50%">
			<select name="section_content" id="rodzeta-importrealtyfeed-catalogsection-id"
					data-value="<?= $currentOptions["section_content"] ?>">
				<option value="">(выберите раздел)</option>
			</select>
		</td>
	</tr>

	<tr class="heading">
		<td colspan="2">Настройки импорта</td>
	</tr>

	<tr>
		<td class="adm-detail-content-cell-l" width="50%">
			<label>URL источника</label>
		</td>
		<td class="adm-detail-content-cell-r" width="50%">
			<input name="src_url" type="text" value="<?= htmlspecialcharsex($currentOptions["src_url"]) ?>" size="40"
				placeholder="http://example.org/path1/example1.xml">
		</td>
	</tr>

	<?php
	 $tabControl->buttons();
  ?>

  <input class="adm-btn-save" type="submit" name="save" value="Применить настройки">

</form>

<?php

$tabControl->end();

?>

<script>
RodzetaImportrealtyfeedUpdate(document.getElementById(
	"rodzeta-importrealtyfeed-catalogsection-id"));
</script>