<?php
/*******************************************************************************
 * rodzeta.importrealtyfeed - Import realty-feed to infoblock
 * Copyright 2016 Semenov Roman
 * MIT License
 ******************************************************************************/

namespace Rodzeta\Importrealtyfeed;

const ID = "rodzeta.importrealtyfeed";
const APP = __DIR__ . "/";
const LIB = APP  . "lib/";

define(__NAMESPACE__ . "\LOG", $_SERVER["DOCUMENT_ROOT"]  . "/upload/.log_" . ID);

function Log($data) {
	file_put_contents(LOG, date("Y-m-d H:i:s") . "\t" . print_r($data) . "\n", FILE_APPEND);
}

function ImportChunk($xmlFile, $start = 0) {
	//...
}

function Import() {
	// TODO use config
	// run import chunk
	Log("start next chunk");
}

