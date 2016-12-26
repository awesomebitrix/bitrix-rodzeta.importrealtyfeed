<?php
/*******************************************************************************
 * rodzeta.importrealtyfeed - Import realty-feed to infoblock
 * Copyright 2016 Semenov Roman
 * MIT License
 ******************************************************************************/

namespace Rodzeta\Importrealtyfeed;

use Bitrix\Main\{Web\HttpClient, Config\Option};

const ID = "rodzeta.importrealtyfeed";
const APP = __DIR__ . "/";
const LIB = APP  . "lib/";

define(__NAMESPACE__ . "\LOG", $_SERVER["DOCUMENT_ROOT"]  . "/upload/.log_" . ID);
define(__NAMESPACE__ . "\FILE_FEED", $_SERVER["DOCUMENT_ROOT"]  . "/upload/." . ID . "_file1.xml");

function Log($data) {
	file_put_contents(LOG, date("Y-m-d H:i:s")
		. "\t" . print_r($data, true) . "\n", FILE_APPEND);
}

function ParseXml($xmlFile) {
	$xml = new \XMLReader();
	if ($xml->open($xmlFile)) {
		while ($xml->read()) {
			if ($xml->nodeType == \XMLReader::ELEMENT && $xml->name == "offer") {
				$offer = simplexml_load_string($xml->readOuterXML());
				if ($offer !== false) {
					yield $offer;
				} else {
					Log("Error - xml node");
					break;
				}
			}
		}
		$xml->close();
	} else {
		Log("Error - can't open xml-file $xmlFile");
	}
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

function ImportElement($data, $currentOptions) {
	Log($data);
	$element = new \CIBlockElement();
	$fields = [
		"NAME" => $data["NAME"],
		"DETAIL_TEXT" => $data["DETAIL_TEXT"],
		"IBLOCK_SECTION" => $currentOptions["section"],
	];
	foreach ($fields as $k => $v) { // unset element fields from properties
		unset($data[$k]);
	}
	// find element by INTERNAL_ID
	$res = \CIBlockElement::GetList(
		["SORT" => "ASC"],
		[
			"IBLOCK_ID" => $currentOptions["iblock_id"],
			"PROPERTY" => [
				"INTERNAL_ID" => $data["INTERNAL_ID"],
			]
		],
		false,
		false,
		["ID", "*"]
	);
	if ($row = $res->GetNextElement()) {
		$item = $row->GetFields();
		Log("UPDATE INTERNAL_ID: {$data['INTERNAL_ID']}");
		// update fields
		$ok = $element->Update($item["ID"], $fields);
		if ($ok !== false) {
			Log("Updated ID: {$item['ID']}");
		} else {
			Log("Error $element->LAST_ERROR");
		}
		// update properties
		\CIBlockElement::SetPropertyValuesEx($item["ID"], $item["IBLOCK_ID"], $data);
	} else {
		Log("ADD INTERNAL_ID: {$data['INTERNAL_ID']}");
		$fields = array_merge($fields, [
			"IBLOCK_ID" => $currentOptions["iblock_id"],
			"CODE" => \Cutil::translit($fields["NAME"], "ru"),
			"ACTIVE" => "Y",
			"PROPERTY_VALUES" => $data
		]);
		$itemId = $element->Add($fields);
		if ($itemId !== false) {
			Log("Added ID: $itemId");
		} else {
			Log("Error: $element->LAST_ERROR");
		}
	}
}

function Import($start = -1) {
	$currentOptions = json_decode(Option::get("rodzeta.importrealtyfeed", "default", "[]"), true);
	Log($currentOptions);
	if (!empty($currentOptions["src_url"])) {
		$limit = (int)$currentOptions["num"];
		if ($start < 0 || !file_exists(FILE_FEED)) { // fetch feed
			$client = new HttpClient();
			Log("Load file {$currentOptions['src_url']} -> " . FILE_FEED . "...");
			if (!$client->download($currentOptions["src_url"], FILE_FEED)) {
				Log("Error loading src file.");
			} else {
				Log("Start import...");
			}
			$start = 0;
		} else { // run import chunk
			\CModule::IncludeModule("iblock");
			Log("Start next chunk $start:$limit...");
			$hasData = false;
			foreach (Slice(ParseXml(FILE_FEED), $start, $limit) as $i => $offer) {
				$hasData = true;
				$creationDate = (string)$offer->{"creation-date"};
				$lastUpdateDate = (string)$offer->{"last-update-date"};
				$data = [
					"NAME" => (string)$offer->name,
					"DETAIL_TEXT" => (string)$offer->description,
					//
					"INTERNAL_ID" => (string)$offer->attributes()["internal-id"],
					"URL" => (string)$offer->url,
					"FLOOR" => (string)$offer->floor,
					"PRICE" => (string)$offer->price->value,
					"CURRENCY" => (string)$offer->price->currency,
					"PERIOD" => (string)$offer->price->period,
					"DEAL_STATUS" => (string)$offer->{"deal-status"},
					"CREATION_DATE" => $creationDate? ConvertTimeStamp(strtotime($creationDate), "FULL") : "",
					"LAST_UPDATE_DATE" => $lastUpdateDate? ConvertTimeStamp(strtotime($lastUpdateDate), "FULL") : "",
				];
				$images = [];
				foreach ($offer->image as $image) {
					$images[] = (string)$image;
				}
				$data["IMAGE"] = json_encode($images);
				ImportElement($data, $currentOptions);
			}
			$start += $limit;
			Log("Next = $start");
			if (!$hasData) {
				// reset import
				//!!! $start = -1;
				Log("End import.");
				return ""; // TODO add agent for next period
			}
		}
	}
	else {
		Log("Src url not defined.");
	}
	return __FUNCTION__ . "($start);";
}
