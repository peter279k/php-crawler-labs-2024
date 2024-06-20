<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;


$model = '@cf/openai/whisper';

$cloudflareToken = '';
$accountId = '';
if (getenv('cloudflare_ai_worker_token') === false) {
    echo 'The cloudflare_ai_worker_token environment vairable is not defined.', PHP_EOL;
    exit(1);
}

if (getenv('cloudflare_account_token') === false) {
    echo 'The cloudflare_account_token environment vairable is not defined.', PHP_EOL;
    exit(1);
}


$accountId = getenv('cloudflare_account_token');
$cloudflareToken = getenv('cloudflare_ai_worker_token');
$reqApiUrl = "https://api.cloudflare.com/client/v4/accounts/$accountId/ai/run/$model";

$client = new Client();
$headers = ['Authorization' => sprintf('Bearer %s', $cloudflareToken)];
$response = $client->request('POST', $reqApiUrl, [
    'headers' => $headers,
    'body' => file_get_contents('jcaptchasnd.wav'),
]);

$responseJson = json_decode((string)$response->getBody(), true);

var_dump($responseJson);
