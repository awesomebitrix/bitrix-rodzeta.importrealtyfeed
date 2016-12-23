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
		// TODO
		$currentOptions["x"] = $request->getPost("y");
		Option::set("rodzeta.importrealtyfeed", "default", json_encode($currentOptions));

		\CAdminMessage::showMessage([
	    "MESSAGE" => Loc::getMessage("RODZETA_IMPORTREALTYFEED_OPTIONS_SAVED"),
	    "TYPE" => "OK",
	  ]);
	}
}

$tabControl->begin();

?>

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
			TODO
		</td>
	</tr>

	<tr>
		<td class="adm-detail-content-cell-l" width="50%">
			<label>Корневой раздел</label>
		</td>
		<td class="adm-detail-content-cell-r" width="50%">
			TODO
		</td>
	</tr>

	<?php
	 $tabControl->buttons();
  ?>

  <input class="adm-btn-save" type="submit" name="save" value="Применить настройки">

</form>

<?php

$tabControl->end();
