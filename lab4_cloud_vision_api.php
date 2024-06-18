<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;


$client = new Client();

$captchaImageFile = 'jcaptcha.jpg';
$captchaBase64Image = base64_encode(file_get_contents($captchaImageFile));

$captchaImageFile2 = 'jcaptcha2.jpg';
$captchaBase64Image2 = base64_encode(file_get_contents($captchaImageFile2));

$cloudVisionApiKey = getenv('cloud_vision_api_key');
if ($cloudVisionApiKey === false) {
    echo 'The Google Cloud Vision API key environment variable is not defined.', PHP_EOL;
    exit(1);
}

$apiUrl = sprintf('https://vision.googleapis.com/v1/images:annotate?key=%s', $cloudVisionApiKey);

$payload = [
    'requests' => [
        [
            'image' => [
                'content' => $captchaBase64Image,
            ],
            'features' => [
                [
                    'type' => 'TEXT_DETECTION',
                ],
            ],
        ],
    ],
];
$payload2 = $payload;
$payload2['requests'][0]['image']['content'] = $captchaBase64Image2;

$headers = ['Content-Type' => 'application/json'];

$response = $client->request('POST', $apiUrl, [
    'headers' => $headers,
    'json' => $payload,
]);

$responseJson = json_decode((string)$response->getBody(), true);
var_dump($responseJson['responses'][0]['fullTextAnnotation']['text']);

echo 'The first request is done and terminate 3 seconds...', PHP_EOL;
sleep(3);

$response = $client->request('POST', $apiUrl, [
    'headers' => $headers,
    'json' => $payload2,
]);

$responseJson = json_decode((string)$response->getBody(), true);
var_dump($responseJson['responses'][0]['fullTextAnnotation']['text']);
