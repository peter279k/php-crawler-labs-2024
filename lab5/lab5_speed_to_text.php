<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;


$client = new Client();

$captchaAudioFile = 'jcaptchasnd.wav';
$captchaBase64Audio = base64_encode(file_get_contents($captchaAudioFile));

$speechToTextApiKey = getenv('speech_text_api_key');
if ($speechToTextApiKey === false) {
    echo 'The Google Speech To Text API key environment variable is not defined.', PHP_EOL;
    exit(1);
}

$apiUrl = sprintf('https://speech.googleapis.com/v1/speech:recognize?key=%s', $speechToTextApiKey);

$payload = [
    'config' => [
        'encoding' => 'LINEAR16',
        'languageCode' => 'zh-TW',
        'audioChannelCount' => 2,
        'enableSeparateRecognitionPerChannel' => True,
    ],
    'audio' => [
        'content' => $captchaBase64Audio,
    ],
];

$headers = ['Content-Type' => 'application/json'];
$response = $client->request('POST', $apiUrl, [
    'headers' => $headers,
    'json' => $payload,
]);

$responseJson = json_decode((string)$response->getBody(), true);
var_dump($responseJson['results'][0]['alternatives'][0]['transcript']);
