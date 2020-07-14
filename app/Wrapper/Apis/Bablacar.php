<?php


namespace App\Wrapper\Apis;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Bablacar
{
    const API_ENDPOINT = 'https://public-api.blablacar.com';
    const DEFAULT_OPTIONS = ['locale' => 'de-DE', 'currency' => 'EUR', 'from_country' => 'DE', 'to_country' => 'DE'];

    private $client;
    private $lastResponse;
    private $key;

    function __construct()
    {
        $this->client = resolve(Client::class, ['config' => ['base_uri' => self::API_ENDPOINT]]);
        $this->key = \config('connector.blablacar_key');
    }

    public function getEntries($startLat, $startLng, $endLat, $endLng, $options = []): Collection
    {
        if ($this->key == null)
            return collect();
        $params = ['from_coordinate' => $startLat . ',' . $startLng, 'to_coordinate' => $endLat . ',' . $endLng];
        $options = array_merge(['key' => $this->key], $options);
        $options = array_merge(self::DEFAULT_OPTIONS, $options);
        $params = array_filter(array_merge($options, $params), function ($value) {
            return !is_null($value) && $value !== '';
        });

        $response = $this->client->get('/api/v3/trips', [
            'query' => $params,
            'on_stats' => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            }
        ]);
        //dump($url);
        $this->lastResponse = $response;

        $content = (string)$response->getBody();

        if (!empty($content)) {
            return collect(json_decode($content, true)['trips']);
        } else {
            return collect();
        }

    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

}
