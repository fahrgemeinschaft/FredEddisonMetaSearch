<?php


namespace App\Wrapper;
use App\Search;
use App\Trip;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Ds\Set;

class SearchWrapper
{
    public static function save(Search $search)
    {
        // TODO: error handling and returns
        Cache::put('search:' . $search->id->toString(), $search);
    }

    public static function insert(Trip $trip)
    {
        // store for 5min, won't update if already present
        if (Cache::add("trip:" . $trip->id, $trip, 300)) {
            Redis::geoAdd(
                "trip_start",
                $trip->startPoint['longitude'],
                $trip->startPoint['latitude'],
                $trip->id
            );

            Redis::geoAdd(
                "trip_end",
                $trip->endPoint['longitude'],
                $trip->endPoint['latitude'],
                $trip->id
            );
        }

    }

    public static function find(string $searchId): array
    {

        $search = Cache::get('search:' . $searchId);

        $trip_start = Redis::geoRadius(
            'trip_start',
            $search->startPoint->longitude,
            $search->startPoint->latitude,
            6000, // TODO add parameter for search radius
            "km"
        );


        $trip_end = Redis::geoRadius(
            'trip_end',
            $search->endPoint->longitude,
            $search->endPoint->latitude,
            6000,
            "km"
        );

        $start_set = new Set($trip_start);
        $end_set = new Set($trip_end);

        $ids = $start_set->intersect($end_set);

        $keys = [];


        foreach ($ids as $id) {
            $keys[] = "trip:" . $id;
        }
        if (count($keys) != 0)
            return array_values(Cache::many($keys));
        else return [];
    }
}
