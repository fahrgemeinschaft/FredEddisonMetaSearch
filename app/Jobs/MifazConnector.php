<?php

namespace App\Jobs;

use App\Offer;
use App\Search;
use App\Trip;
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


class MifazConnector implements ShouldQueue
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
        $client = resolve(Mifaz::class);
        $search = $this->search;

        $start = $this->search->startPoint;
        $end = $this->search->endPoint;

        $entries = $client->getEntries($start['latitude'], $start['longitude'], $end['latitude'], $end['longitude']);

        foreach ($entries as &$entry)
        {
            $trip = $this->convertEntryToTrip($entry, $search->id->toString());
            SearchWrapper::insert($trip);
        }

    }

    public function convertEntryToTrip($entry, $searchId)
    {
        $tripStart = explode(' ', $entry['startcoord']);
        $tripEnd = explode(' ', $entry['goalcoord']);
        $trip = new Trip([
            'startPoint' => new GeoLocation(['latitude' => $tripStart[0], 'longitude' => $tripStart[1]]),
            'endPoint' => new GeoLocation(['latitude' => $tripEnd[0], 'longitude' => $tripEnd[1]]),
            'connector' => "Mifaz"
        ]);

        $trip->setAttribute('id', 'mifaz-' . $entry['id']);

        $offer = new Offer([
            'url' => $entry['url'],
            'name' => $entry['username'],
            'image' => $entry['imgUser'],
            // FIXME do we have to create trips for each day?!
            'availabilityStarts' => DateTime::createFromFormat('H:i', $entry['starttimebegin']),
            'availabilityEnds' => DateTime::createFromFormat('H:i', $entry['starttimeend'])
        ]);

        $trip->setAttribute('offer', $offer);
        $offer->setAttribute('tripId', $trip->id);
        $trip->setAttribute('searchId', $searchId);

        return $trip;
    }
}
