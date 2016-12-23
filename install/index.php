<?php
/*******************************************************************************
 * rodzeta.importrealtyfeed - Import realty-feed to infoblock
 * Copyright 2016 Semenov Roman
 * MIT License
 ******************************************************************************/

// NOTE this file must compatible with php 5.3

defined("B_PROLOG_INCLUDED") and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class rodzeta_importrealtyfeed extends CModule {

	var $MODULE_ID = "rodzeta.importrealtyfeed"; // NOTE using "var" for bitrix rules

	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_GROUP_RIGHTS;
	public $PARTNER_NAME;
	public $PARTNER_URI;

	//public $MODULE_GROUP_RIGHTS = 'N';
	//public $NEED_MAIN_VERSION = '';
	//public $NEED_MODULES = array();

	function __construct() {
		$this->MODULE_ID = "rodzeta.importrealtyfeed"; // NOTE for showing module in /bitrix/admin/partner_modules.php?lang=ru

		$arModuleVersion = array();
		include __DIR__ . "/version.php";

		if (!empty($arModuleVersion["VERSION"])) {
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("RODZETA_IMPORTREALTYFEED_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("RODZETA_IMPORTREALTYFEED_MODULE_DESCRIPTION");
		$this->MODULE_GROUP_RIGHTS = "N";

		$this->PARTNER_NAME = "Rodzeta";
		$this->PARTNER_URI = "http://rodzeta.ru/";
	}

	function DoInstall() {
		// check module requirements
		global $APPLICATION;
		if (version_compare(PHP_VERSION, "7", "<")) {
			$APPLICATION->ThrowException(Loc::getMessage("RODZETA_REQUIREMENTS_PHP_VERSION"));
			return false;
		}
		if (!defined("BX_UTF")) {
			$APPLICATION->ThrowException(Loc::getMessage("RODZETA_REQUIREMENTS_BITRIX_UTF8"));
			return false;
		}
		ModuleManager::registerModule($this->MODULE_ID);
		RegisterModuleDependences("main", "OnPageStart", $this->MODULE_ID);
		CAgent::AddAgent(
			"Rodzeta\\Importrealtyfeed\\Import();",
			"rodzeta.importrealtyfeed",
			"N", 60, "", "Y"
		);
	}

	function DoUninstall() {
		CAgent::RemoveAgent(
			"Rodzeta\\Importrealtyfeed\\Import();",
			"rodzeta.importrealtyfeed"
		);
		UnRegisterModuleDependences("main", "OnPageStart", $this->MODULE_ID);
		ModuleManager::unregisterModule($this->MODULE_ID);
	}

}
