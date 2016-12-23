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

function ParseXml($xmlFile) {
	$xml = new \XMLReader();
	$xml->open($xmlFile);
	while ($xml->read()) {
		if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == "offer") {
			yield simplexml_load_string($xml->readOuterXML());
		}
	}
	$xml->close();
}

function Slice($collection, $offset = 0, $length = -1) {
	if ($length == 0) {
		return;
	}
	if ($offset < 0) {
		$offset = 0;
	}
	$i = -1;
	$num = $length;
	foreach ($collection as $item) {
		$i++;
		if ($i < $offset) {
			continue;
		}
		if ($length > 0) {
			if ($num == 0) {
				break;
			}
			$num--;
		}
		yield $i => $item;
	}
}

function Import($start = -1) {
	// TODO get from options
	$currentOptions = [
		"src_url" => "http://anton.citrus-dev.ru/import.xml",
		"num" => 3,
	];
	$limit = (int)$currentOptions["num"];

	if ($start < 0 || !file_exists(FILE_FEED)) { // fetch feed
		$client = new HttpClient();
		$client->download($currentOptions["src_url"], FILE_FEED);
		Log("Load file...");
		Log("Init import...");
		$start = 0;
	} else { // run import chunk
		Log("Start next chunk $start:$limit...");
		$hasData = false;
		foreach (Slice(ParseXml(FILE_FEED), $start, $limit) as $i => $offer) {
			$hasData = true;
			$data = [
				"NAME" => (string)$offer->name,
				"DETAIL_TEXT" => (string)$offer->description,
				"DATE_CREATE" => (string)$offer->{"creation-date"},
				"TIMESTAMP_X" => (string)$offer->{"last-update-date"},
				//
				"INTERNAL_ID" => (string)$offer->attributes()["internal-id"],
				"URL" => (string)$offer->url,
				"FLOOR" => (string)$offer->floor,
				"PRICE" => (string)$offer->price->value,
				"CURRENCY" => (string)$offer->price->currency,
				"DEAL_STATUS" => (string)$offer->{"deal-status"},
			];
			$images = [];
			foreach ($offer->image as $image) {
				$images[] = (string)$image;
			}
			$data["IMAGE"] = json_encode($images, true);
			Log($data);
		}
		$start += $limit;
		Log("Next = $start");
		if (!$hasData) {
			Log("End import.");
			// no more data
			// TODO add agent for next period
			// return "";
			$start = 0;
		}
	}

	return __FUNCTION__ . "($start);";
}
