<?php

namespace App\Jobs;

use App\Offer;
use App\Search;
use App\Trip;
use App\Transport;
use App\GeoLocation;
use App\Wrapper\Apis\Ride2Go;
use App\Wrapper\SearchWrapper;
use App\Wrapper\Apis\Mifaz;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DateTime;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;
use Carbon\Carbon;


class Ride2GoConnector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Search */
    protected $search;
    private $client;

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
        $client = resolve(Ride2Go::class);
        $search = $this->search;

        $start = $this->search->startPoint->location;
        $end = $this->search->endPoint->location;

        $date = Carbon::createFromTimeString($search->departure['time']);

        $options = [
            'reoccurDays' => $this->search->reoccurDays ?? [],
            'smoking' => $this->search->smoking,
            'animals' => $this->search->animals,
            'transportTypes' => $this->search->transportTypes,
            'baggage' => $this->search->baggage,
            'gender' => $this->search->gender,
            'organizations' => $this->search->organizations,
            'availabilityStarts' => $this->search->availabilityStarts,
            'availabilityEnds' => $this->search->availabilityEnds,
            'arrival' => $this->search->arrival,
            'departure' => $this->search->departure,
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
        $trips = collect();

        $date = Carbon::create($entry['offer']['availabilityEnds']); //TODO: Richtig? Hab ich so aus anderen Stellen deduziert

        $tripStart = $entry['startPoint'];
        $tripEnd = $entry['endPoint'];

        $trip = new Trip([
            'created' =>  $entry['created'],
            'modified' => $entry['modified'],
            'startPoint' => new GeoLocation(['latitude' => $tripStart['latitude'], 'longitude' => $tripStart['longitude']]),
            'endPoint' => new GeoLocation(['latitude' => $tripEnd['latitude'], 'longitude' => $tripEnd['longitude']]),
            'connector' => "Ride2Go",
            'timestamp' => Carbon::now()
        ]);

        $trip->setAttribute('id', 'ride2go-' . $entry['id']);

        $offer = new Offer([
            'url' => $entry['offer']['url'],
            'name' => $entry['offer']['name'],
            'image' => $entry['offer']['image'],
            'availabilityStarts' => $entry['offer']['availabilityStarts'],
            'availabilityEnds' => $entry['offer']['availabilityEnds']
        ]);

        $transport = new Transport([
            'transportType' => $entry['transport']['transportType']
        ]);

        $trip->setAttribute('offer', $offer);
        $trip->setAttribute('transport', $transport);
        $offer->setAttribute('tripId', $entry['id']);
        $trip->setAttribute('searchId', $search->id->toString());
        $trips->push($trip);

        return  $trips;
    }
}
