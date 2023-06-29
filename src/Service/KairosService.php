<?php

namespace App\Service;

use GuzzleHttp\Client;

class KairosService
{
    private $clientId;
    private $clientKey;
    private $client;

    public function __construct($clientId, $clientKey)
    {
        $this->clientId = $clientId;
        $this->clientKey = $clientKey;
        $this->client = new Client(['base_uri' => 'https://api.kairos.com/']);
    }

    public function verifyFace($image1, $image2)
    {
        $payload = [
            'image1' => $image1,
            'image2' => $image2,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'app_id' => $this->clientId,
            'app_key' => $this->clientKey,
        ];

        $response = $this->client->post('verify', [
            'headers' => $headers,
            'json' => $payload,
        ]);

        return json_decode($response->getBody(), true);
    }
}
