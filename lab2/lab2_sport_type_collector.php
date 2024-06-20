<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


$sportTypeUrlFormat = 'https://www.data-sports.tw/prod-api/data/specification?main_type=%s&type=%s&subtype=%s';
$sportTypeUrlFormat2 = 'https://www.data-sports.tw/prod-api/data/specification?main_type=%s&type=%s';

$sportTypeJson = 'sport_types.json';
if (!file_exists($sportTypeJson)) {
    echo sprintf('%s file is not existed.' . PHP_EOL, $sportTypeJson);
    exit(1);
}
$specDir = './spec';
if (!is_dir($specDir)) {
    mkdir($specDir);
}

$csvHead = '量測項目,量測項目英文名稱,量測單位,量測單位英文名稱,量測型別,必填,資料範圍,範例';
$csvFormatStr = str_repeat('%s,', 8);
$csvFormatStr = substr($csvFormatStr, 0, strlen($csvFormatStr)-1);

$sportTypeJsonString = file_get_contents($sportTypeJson);
$sportTypeJsonArray = json_decode($sportTypeJsonString, true);

foreach ($sportTypeJsonArray as $typeInfo) {
    $mainType = $typeInfo['MainType'];
    $types = $typeInfo['Type'];
    $subTypes = $typeInfo['SubType'];
    $typeIndex = 0;
    for($typeIndex=0; $typeIndex<count($types); $typeIndex++) {
        $type = $types[$typeIndex];
        $subTypeArr = $subTypes[$typeIndex];
        if (count($subTypeArr) === 0) {
            $subTypeArr[] = '';
        }

        preg_match_all('/(\w+)/', $mainType, $matched);
        $mainTypeEn = $matched[0][0];

        preg_match_all('/(\w+)/', $type, $matched);
        $typeEn = $matched[0][0];

        foreach ($subTypeArr as $subType) {
            $subTypeEn = explode('(', $subType)[0];
            $csvStr = $csvHead . PHP_EOL;

            if ($subType === '') {
                $client = new Client();
                $sportTypeInfoUrl = sprintf($sportTypeUrlFormat2, $mainTypeEn, $typeEn);
                $response = $client->request('GET', $sportTypeInfoUrl);
                $jsonData = json_decode($response->getBody(), true);
            } else {
                $client = new Client();
                $sportTypeInfoUrl = sprintf($sportTypeUrlFormat, $mainTypeEn, $typeEn, $subTypeEn);
                $response = $client->request('GET', $sportTypeInfoUrl);
                $jsonData = json_decode($response->getBody(), true);
            }

            foreach ($jsonData['data'] as $sportSpec) {
                $measuredName = $sportSpec['name_zh'];
                $measuredEnName = $sportSpec['name'];
                $unitName = 'N/A';
                $unitEnName = 'N/A';
                if (array_key_exists('unit_zh', $sportSpec)) {
                    $unitName = $sportSpec['unit_zh'];
                }
                if (array_key_exists('unit', $sportSpec)) {
                    $unitEnName = $sportSpec['unit'];
                }
                if ($unitName === '') {
                    $unitName = 'N/A';
                }
                if ($unitEnName === '') {
                    $unitEnName = 'N/A';
                }
                $required = $sportSpec['required'];
                $reasonalbeData = 'N/A';
                if (array_key_exists('reasonalbe_data', $sportSpec)) {
                    $reasonalbeData = $sportSpec['reasonalbe_data'];
                }
                if ($reasonalbeData === '') {
                    $reasonalbeData = 'N/A';
                }
                $exampleValue = $sportSpec['example'];
                $valueType = $sportSpec['value_type'];
                if ($exampleValue === '') {
                    $exampleValue = 'N/A';
                }
                if ($valueType === '') {
                    $valueType = 'N/A';
                }

                $csvStr .= sprintf(
                    $csvFormatStr,
                    $measuredName,
                    $measuredEnName,
                    $unitName,
                    $unitEnName,
                    $valueType,
                    $required,
                    $reasonalbeData,
                    $exampleValue
[O                );
                $csvStr .= PHP_EOL;
            }

            if ($subType === '') {
                $subType = '無子項目';
            } else {
                $arr = explode('(', $subType);
                $arr[1] = str_replace(')', '', $arr[1]);
                $subType = sprintf('%s(%s)', $arr[1], $arr[0]);
            }

            $csvPath = sprintf('./spec/%s_%s_%s.csv', $mainType, $type, $subType);
            file_put_contents($csvPath, $csvStr);
            echo sprintf('%s file is saved.', $csvPath), PHP_EOL;
            sleep(5);
        }
    }
}
