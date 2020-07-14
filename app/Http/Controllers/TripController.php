<?php

namespace App\Http\Controllers;

use App\AsyncPageTrip;
use App\GeoLocation;
use App\Jobs\BlablacarConnector;
use App\Jobs\MifazConnector;
use App\PageResponse;
use App\Search;
use App\SearchRadius;
use App\Trip;
use App\Wrapper\SearchWrapper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class TripController extends Controller
{
    /**
     * @param Request $request
     * @param \App\Trip $trip
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Trip $trip)
    {
        $trip = Trip::find($trip);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $data = json_decode($request->getContent(), true);


        if (isset($data['startPoint']) && isset($data['endPoint'])) {
            $search = new Search();
            $search->tripTypes = $data['tripTypes'];
            $search->reoccurDays = $data['reoccurDays'];
            $search->smoking = $data['smoking'];
            $search->animals = $data['animals'];
            $search->transportTypes = $data['transportTypes'];
            $search->baggage = $data['baggage'];
            $search->gender = $data['gender'];
            $search->organizations = $data['organizations'];
            $search->availabilityStarts = $data['availabilityStarts'];
            $search->availabilityEnds = $data['availabilityEnds'];
            $search->arrival = $data['arrival'];
            $search->departure = $data['departure'];

            if (isset($data['startPoint']['location']['radius']))
                $startRadius = $data['startPoint']['location']['radius'];
            else
                $startRadius = 50;
            $startPoint = new SearchRadius(['radius' => $startRadius]);
            $startPoint->setAttribute('location', new GeoLocation($data['startPoint']['location']));

            if (isset($data['endPoint']['location']['radius']))
                $endRadius = $data['startPoint']['location']['radius'];
            else
                $endRadius = 50;
            $endPoint = new SearchRadius(['radius' => $endRadius]);
            $endPoint->setAttribute('location', new GeoLocation($data['endPoint']['location']));

            $id = Uuid::uuid4();
            $search->setAttribute('startPoint', $startPoint);
            $search->setAttribute('endPoint', $endPoint);
            $search->setAttribute('id', $id);


        } else {
            $search = factory(Search::class)->make(); //TODO: error handling now factory
        }

        SearchWrapper::save($search);

        // dispatch connectors
        MifazConnector::dispatchNow($search);
        BlablacarConnector::dispatchNow($search);

        // get quick results
        $results = SearchWrapper::find($search->id);

        $asyncResponse = new AsyncPageTrip([
            'id' => $search->id,
            'results' => $results,
            'page' => new PageResponse([
                'page' => 1,
                'pageSize' => count($results),
                'totalCount' => 1,
                'lastIndex' => 0,
                'first' => true,
                'last' => true,
                'firstIndex' => 0
            ])
        ]);

        return new JsonResponse($asyncResponse);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search_id(Request $request, $id)
    {
        $trips = SearchWrapper::find($id);

        $asyncResponse = new AsyncPageTrip([
            'id' => $id,
            'results' => $trips,
            'page' => new PageResponse([
                'page' => 1,
                'pageSize' => count($trips),
                'totalCount' => 1,
                'lastIndex' => 0,
                'first' => true,
                'last' => true,
                'firstIndex' => 0
            ])
        ]);
        return new JsonResponse($asyncResponse, 200, [], JSON_UNESCAPED_SLASHES);
    }

}
