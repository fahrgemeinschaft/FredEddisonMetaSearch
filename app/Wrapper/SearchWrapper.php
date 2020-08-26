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

        if ($search != null) {


            $trip_start = Redis::geoRadius(
                'trip_start',
                $search->startPoint->location->longitude,
                $search->startPoint->location->latitude,
                $search->startPoint->radius,
                "km"
            );

            $trip_end = Redis::geoRadius(
                'trip_end',
                $search->endPoint->location->longitude,
                $search->endPoint->location->latitude,
                $search->endPoint->radius,
                "km"
            );

            $start_set = new Set($trip_start);
            $end_set = new Set($trip_end);

            $ids = $start_set->intersect($end_set);

            $keys = [];

            foreach ($ids as $id) {
                $keys[] = "trip:" . $id;
            }
            if (count($keys) != 0) {
                $trips = collect();
                foreach (array_filter(array_values(Cache::many($keys))) as $trip) {
                    $trips->push($trip);
                }
                $trips->sortByDesc('timestamp');
                return $trips->toArray();

            } else return [];
        } else return [];
    }


}
