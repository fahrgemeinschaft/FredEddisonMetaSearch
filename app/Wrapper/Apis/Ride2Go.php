<?php


namespace App\Wrapper\Apis;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\TransferStats;
use http\Exception;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Ride2Go
{
    const API_ENDPOINT = 'https://r2g.api.dev.services.rd2g.de/';

    const DEFAULT_OPTIONS = [
        'reoccurDays' => [],
        'tripTypes' => ['OFFER'],
        'transportTypes' => ['CAR'],
        'smoking' => 'YES',
        'animals' => 'YES',
        'availabilityStarts' => 'SMALL',
        'baggage' => 'SMALL',
        'organizations' => [],
        'gender' => 'IRRELEVANT',
    ];

    private $client;
    private $lastResponse;

    function __construct()
    {
        $this->client = resolve(Client::class, ['config' => ['base_uri' => self::API_ENDPOINT]]);
    }

    public function getEntries($startLat, $startLng, $endLat, $endLng, $options = []): Collection
    {
        $params = [
            'page' => [
                'pageSize' => 100,
                'page' => 0,
                'firstIndex' => 0,
            ],
            'startPoint' => [
                'location' => [
                    'latitude' => $startLat,
                    'longitude' => $startLng
                ]
            ],
            'endPoint' => [
                'location' => [
                    'latitude' => $endLat,
                    'longitude' => $endLng
                ]
            ]
        ];

        foreach ($options as $key => $value) {
            if (empty($options[$key])) {
                unset($options[$key]);
            }
        }

        $options = array_merge(self::DEFAULT_OPTIONS, $options);
        $params = array_filter(array_merge($options, $params), function ($value) {
            return !is_null($value) && $value !== '';
        });

        if (!empty($params['transportTypes']) && is_string($params['transportTypes'])) {
            $params['transportTypes'] = [$params['transportTypes']];
        }

        if (!empty($params['arrival']) && $params['arrival'] instanceof Carbon) {
            $params['arrival'] = ['time' => $params['arrival']->format('c'), 'toleranceInDays' => 2];
        }
        if (!empty($params['departure']) && $params['departure'] instanceof Carbon) {
            $params['departure'] = ['time' => $params['departure']->format('c'), 'toleranceInDays' => 2];
        }

        if (!empty($params['availabilityStarts']) && $params['availabilityStarts'] instanceof Carbon) {
            $params['availabilityStarts'] = $params['availabilityStarts']->format('c');
        }
        if (!empty($params['availabilityEnds']) && $params['availabilityEnds'] instanceof Carbon) {
            $params['availabilityEnds'] = $params['availabilityEnds']->format('c');
        }

        try {
            $response = $this->client->post('trip/search', [
                'headers' => [
                    'accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $params
            ]);
            $this->lastResponse = $response;

            $content = (string)$response->getBody();

            Log::info("Ride2Go:" . $content);

            if (!empty($content)) {
                return collect(json_decode($content, true)['results']);
            } else {
                return false;
            }

        }
        catch (ServerException $e){
            Log::error("Ride2Go: API not available:".$e);
            return collect();
        }


    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

}
