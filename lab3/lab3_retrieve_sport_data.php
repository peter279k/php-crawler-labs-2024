<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


$client = new Client();

$userName = 'user name';
$password = 'password';

$headers = [
    'Content-Type' => 'application/json; charset=UTF-8',
];
$jsonPayload = [
    'membername' => $userName,
    'password' => $password,
];

$response = $client->request('POST', 'https://www.data-sports.tw/prod-api/member/login', [
    'headers' => $headers,
    'json' => $jsonPayload,
]);
$responseJson = json_decode((string)$response->getBody(), true);

if (!array_key_exists('data', $responseJson)) {
    echo 'Login is failed', PHP_EOL;
    exit(1);
}
if (!array_key_exists('token', $responseJson['data'])) {
    echo 'Login is failed', PHP_EOL;
    exit(1);
}

echo 'Login is successful.', PHP_EOL;
echo 'Retrieving sport data example is started...', PHP_EOL;
sleep(3);

$token = $responseJson['data']['token'];
$mainType = 'Sport';
$type = 'Run';
$subType = '100m';
$dataSize = 1;
$sportDataApiFormat = 'https://api.data-sports.tw/data/processed?main_type=%s&type=%s&subtype=%s&data_size=%d';

$client = new Client();

$headers = [
    'Accept' => 'application/json',
    'Authorization' => sprintf('Bearer %s', $token),
];
$response = $client->request('GET', sprintf($sportDataApiFormat, $mainType, $type, $subType, $dataSize), [
    'headers' => $headers,
]);

$sportJsonData = json_decode((string)$response->getBody(), true);
var_dump($sportJsonData);
