<?php
/*******************************************************************************
 * rodzeta.importrealtyfeed - Import realty-feed to infoblock
 * Copyright 2016 Semenov Roman
 * MIT License
 ******************************************************************************/

namespace Rodzeta\Importrealtyfeed;

use Bitrix\Main\Web\HttpClient;

const ID = "rodzeta.importrealtyfeed";
const APP = __DIR__ . "/";
const LIB = APP  . "lib/";

define(__NAMESPACE__ . "\LOG", $_SERVER["DOCUMENT_ROOT"]  . "/upload/.log_" . ID);
define(__NAMESPACE__ . "\FILE_FEED", $_SERVER["DOCUMENT_ROOT"]  . "/upload/." . ID . "_file1.xml");

function Log($data) {
	file_put_contents(LOG, date("Y-m-d H:i:s") . "\t" . print_r($data, true) . "\n", FILE_APPEND);
}

function ImportChunk($xmlFile, $start = 0) {
	//...
}

function Import() {
	// TODO get from options
	$currentOptions = [
		"src_url" => "http://anton.citrus-dev.ru/import.xml",
	];

	if (!file_exists(FILE_FEED)) {
		// fetch feed
		$client = new HttpClient();
		$client->download($currentOptions["src_url"], FILE_FEED);
		Log("load file");
		Log("init import");
		//...
	} else {
		// run import chunk
		Log("start next chunk");
		//...
	}

	return __FUNCTION__ . "();";
}
