<?php


namespace App\Wrapper\Apis;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Bessermitfahren
{
    const API_ENDPOINT = 'https://api.bessermitfahren.de';
    const DEFAULT_OPTIONS = [];
    private $client;
    private $lastResponse;
    private $key;

    function __construct() {
        $this->client = resolve(Client::class, ['config' => ['base_uri' => self::API_ENDPOINT ]]);
        $this->key = \config('connector.bessermitfahren_key');
    }

    public function getEntries($startLat, $startLng, $endLat, $endLng, $options = []): Collection
    {
        $params = ['from' => $startLat . ',' . $startLng, 'to' => $endLat . ',' . $endLng];
        $options = array_merge(self::DEFAULT_OPTIONS, $options);
        $params =  array_filter(array_merge($options, $params), function($value) { return !is_null($value) && $value !== ''; });
        $response = $this->client->get('/'.$this->key, [
            'query' => $params,
            'on_stats' => function (TransferStats $stats) use (&$url) {
                $url = $stats->getEffectiveUri();
            }
        ]);
        //dump($url);

        $this->lastResponse = $response;
        $content = (string) $response->getBody();
        dd($content);
        if (!empty($content)) {
            return collect(json_decode($content, true)['resultset']);
        } else {
            return false;
        }
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

}
