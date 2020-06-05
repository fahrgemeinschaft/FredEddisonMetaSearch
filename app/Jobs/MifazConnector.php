<?php

namespace App\Jobs;

use App\Offer;
use App\Search;
use App\Trip;
use App\Transport;
use App\GeoLocation;
use App\Wrapper\SearchWrapper;
use App\Wrapper\Apis\Mifaz;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DateTime;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;
use Carbon\Carbon;


class MifazConnector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Search */
    protected $search;
    private $client;

    // Mapping to convert weekdays to Carbon::weekday constants
    const WEEKDAYS = [
        'So' => 0,
        'Mo' => 1,
        'Di' => 2,
        'Mi' => 3,
        'Do' => 4,
        'Fr' => 5,
        'Sa' => 6
    ];

    /**
     * Create a new job instance.
     *
     * @param Search $search
     */
    public function __construct($search)
    {
        $this->search = $search;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = resolve(Mifaz::class);
        $search = $this->search;

        $start = $this->search->startPoint->location;
        $end = $this->search->endPoint->location;

        $options = [
            'tolerance' => $search->departure->toleranceInDays,
            'journeydate' => $search->departure->time->toDateString(),
            'radius' => $this->search->startPoint->radius
        ];

        $entries = $client->getEntries($start['latitude'], $start['longitude'], $end['latitude'], $end['longitude'], $options);

        $entries->each(function($entry) use ($search)
        {
            $trips = $this->convertEntryToTrips($entry, $search);
            $trips->each(function($trip) { SearchWrapper::insert($trip); });
        });

    }

    public function convertEntryToTrips($entry, $search): Collection
    {
        return $this->findCorrespondingDates($entry, $search)->map(function($date) use (&$entry, &$search)
        {
            $tripStart = explode(' ', $entry['startcoord']);
            $tripEnd = explode(' ', $entry['goalcoord']);
            $trip = new Trip([
                'created' => Carbon::create($entry['creationdate']),
                'modified' => Carbon::create($entry['lastupdate']),
                'startPoint' => new GeoLocation(['latitude' => $tripStart[0], 'longitude' => $tripStart[1]]),
                'endPoint' => new GeoLocation(['latitude' => $tripEnd[0], 'longitude' => $tripEnd[1]]),
                'connector' => "Mifaz"
            ]);

            $trip->setAttribute('id', 'mifaz-' . $entry['id'] . '-' . $date->format('Ymd'));

            $offer = new Offer([
                'url' => $entry['url'],
                'name' => $entry['username'],
                'image' => $entry['imgUser'],
                'availabilityStarts' => $date->copy()->setTime(...explode(':', $entry['starttimebegin'])),
                'availabilityEnds' => $date->copy()->setTime(...explode(':', $entry['starttimeend']))
            ]);

            $transport = new Transport([
                'transportType' => $entry['byTrain'] == '1' ? 'TRAIN' : 'CAR'
            ]);

            $trip->setAttribute('offer', $offer);
            $trip->setAttribute('transport', $transport);
            $offer->setAttribute('tripId', $trip->id);
            $trip->setAttribute('searchId', $search->id->toString());
            return $trip;
        });
    }

    public function findCorrespondingDates($entry, $search): Collection {
        // single trip
        if (isset($entry['regulary']) && $entry['regulary'] == '0')
        {
            $date = isset($entry['journeydate']) ? Carbon::create($entry['journeydate']) : Carbon::now();
            return collect([$date]);
        }

        // no offer, only search
        if (isset($entry['type']) && $entry['type'] == '0')
        {
            return collect();
        }

        $dates = [];

        // just assume empty times mean every weekday?!
        if ($entry['times'] == '') { $entry['times'] = 'Mo,Di,Mi,Do,Fr,Sa,So'; }

        $exploded = explode(',', $entry['times']);
        $weekdays = array_map(function ($w) { return self::WEEKDAYS[$w]; }, $exploded);
        $timeRange = $search->getDepartureRange();
        $currDay = $timeRange[0]->copy();

        while ($currDay->isBefore($timeRange[1])) {
            if (in_array($currDay->weekday(), $weekdays)) {
                $dates[] = $currDay->copy();
            }

            $currDay->add(1, 'day');
        }

        return collect($dates);
    }
}
