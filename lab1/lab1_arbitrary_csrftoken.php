<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;


$iboxHomePage = 'https://ezpost.post.gov.tw/Index.html?r=540335';
$iboxUrl = 'https://ezpost.post.gov.tw/WCFService.svc/InsertNewMailInfoNolLogin';

$header = ['Content-Type' => 'application/json; charset=UTF-8'];
$payload = [
    'SName' => '李大明',
    'SPHONE' => '0905285349',
    'SADDCity' => '臺北市',
    'SADDArea' => '松山區',
    'SADDRoad' => '民生東路四段',
    'SADDOther' => '台北市松山區民生東路四段133號',
    'SADDZIP' => '105',
    'SEMAIL' => 'peter279k@gmail.com',

    'RName' => '李昀陞',
    'RPHONE' => '0905285349',
    'RADDCity' => '臺北市',
    'RADDArea' => '松山區',
    'RADDRoad' => '民生東路四段',
    'RADDOther' => '台北市松山區民生東路四段133號',
    'RADDZIP' => '105',
    'RADDType' => '001',
    'RADD_iBox_ADMId' => '',
    'MAIL_DEPTH' => '1',
    'MAIL_WIDTH' => '1',
    'MAIL_HEIGHT' => '1',
    'CONTENTS_NO' => '6',
    'CONTENTS' => '',
    'COMMENT' => '',
    'UnDeliverOptNo' => 2,
];

$client = new Client();

$header['CSRFToken'] = 'arbitrary_token';

$cookieJar = CookieJar::fromArray([
    'CSRFToken' => 'arbitrary_token',
], 'ezpost.post.gov.tw');

$response = $client->request('POST', $iboxUrl, [
    'headers' => $header,
    'json' => $payload,
    'cookies' => $cookieJar,
]);
$responseString = (string)$response->getBody();

var_dump($responseString);
