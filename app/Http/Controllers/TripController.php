<?php

namespace App\Http\Controllers;

use App\AsyncPageTrip;
use App\GeoLocation;
use App\Jobs\BessermitfahrenConnector;
use App\Jobs\BlablacarConnector;
use App\Jobs\MifazConnector;
use App\Jobs\Ride2GoConnector;
use App\PageResponse;
use App\Search;
use App\SearchRadius;
use App\Trip;
use App\Wrapper\SearchWrapper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
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
        Ride2GoConnector::dispatchNow($search);
        BessermitfahrenConnector::dispatchNow($search);


        // get quick results
        $results = SearchWrapper::find($search->id);
        $asyncResponse = $this->paginateWithoutKey($results, $search->id, 20, $request->query->get('page'));

        return new JsonResponse($asyncResponse);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search_id(Request $request, $id)
    {
        if ($request->get('only-total-number') == 'true')
            return $this->poll_id($request, $id);

        $trips = SearchWrapper::find($id);
        $asyncResponse = $this->paginateWithoutKey($trips, $id, 20, $request->query->get('page'));

        return new JsonResponse($asyncResponse, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function poll_id(Request $request, $id)
    {
        // get quick results
        $trips = SearchWrapper::find($id);
        $return = array('total' => count($trips));
        return $return;
    }

    public function paginateWithoutKey($items, $searchid, $perPage = 20, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        $lap = new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

        return new AsyncPageTrip([
            'id' => $searchid,
            'results' => $lap->values(),
            'page' => new PageResponse([
                'current_page' => $lap->currentPage(),
                'first_page_url' => $lap->url(1),
                'from' => $lap->firstItem(),
                'last_page' => $lap->lastPage(),
                'last_page_url' => $lap->url($lap->lastPage()),
                'next_page_url' => $lap->nextPageUrl(),
                'per_page' => $lap->perPage(),
                'prev_page_url' => $lap->previousPageUrl(),
                'to' => $lap->lastItem(),
                'total' => $lap->total(),
            ])
        ]);
    }
}
