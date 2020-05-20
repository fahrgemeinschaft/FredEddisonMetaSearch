<?php


namespace App\Wrapper\Apis;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

class Mifaz
{
    const API_ENDPOINT = 'https://api.mifaz.de/';
    private $client;
    private $lastResponse;

    function __construct() {
        $this->client = resolve(Client::class, ['config' => ['base_uri' => self::API_ENDPOINT ]]);
    }

    public function getEntries($startLat, $startLng, $endLat, $endLng)
    {
        $response = $this->client->get('request/', [
            'query' => ['f' => 'getEntries', 'startlongitude' => $startLng, 'startlatitude' => $startLat, 'goallatitude' => $endLat, 'goallongitude' => $endLng]
        ]);

        $this->lastResponse = $response;

        $content = (string) $response->getBody();

        if (!empty($content)) {
            return json_decode($content, true)['entries'];
        } else {
            return false;
        }

    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

}
