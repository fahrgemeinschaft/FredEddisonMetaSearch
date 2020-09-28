<?php


namespace App\Wrapper\Apis;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Mifaz
{
    const API_ENDPOINT = 'https://api.mifaz.de/';
    const DEFAULT_OPTIONS = ['radius' => 10, 'journeydate' => '', 'tolerance' => 0];
    private $client;
    private $lastResponse;

    function __construct() {
        $this->client = resolve(Client::class, ['config' => ['base_uri' => self::API_ENDPOINT ]]);
    }

    public function getEntries($startLat, $startLng, $endLat, $endLng, $options = []): Collection
    {
        $params = ['f' => 'getEntries', 'startlongitude' => $startLng, 'startlatitude' => $startLat, 'goallatitude' => $endLat, 'goallongitude' => $endLng];
        $options = array_merge(self::DEFAULT_OPTIONS, $options);
        $params =  array_filter(array_merge($options, $params), function($value) { return !is_null($value) && $value !== ''; });

        $response = $this->client->get('request/', [
            'query' => $params
        ]);

        $this->lastResponse = $response;

        $content = (string) $response->getBody();

        Log::info("Mifaz:" . $content);

        if (!empty($content)) {
            return collect(json_decode($content, true)['entries']);
        } else {
            return false;
        }

    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

}
