<?php

namespace App\Jobs;

use App\Offer;
use App\Search;
use App\Trip;
use App\Transport;
use App\GeoLocation;
use App\Wrapper\Apis\Bessermitfahren;
use App\Wrapper\SearchWrapper;
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


class BessermitfahrenConnector implements ShouldQueue
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
        $client = resolve(Bessermitfahren::class);
        $search = $this->search;

        $start = $this->search->startPoint->location;
        $end = $this->search->endPoint->location;

        $date = Carbon::createFromTimeString($search->departure['time']);

        $options = [
            'date' => $date->toDateString(),
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
        $date = Carbon::create($entry['waypoints'][0]['date_time']);
        $tripStart = $search->startPoint->location;
        $tripEnd = $search->endPoint->location;

        $trip = new Trip([
            'created' => Carbon::now(),
            'modified' => Carbon::now(),
            'startPoint' => new GeoLocation(['latitude' => $tripStart['latitude'], 'longitude' => $tripStart['longitude']]),
            'endPoint' => new GeoLocation(['latitude' => $tripEnd['latitude'], 'longitude' => $tripEnd['longitude']]),
            'connector' => "Bessermitfahren"
        ]);

        $trip->setAttribute('id', 'bessermitfahren-' .  (string) Str::uuid() . '-' . $date->format('Ymd'));

        $offer = new Offer([
            'url' => $entry[0],
            'name' => '',
            'image' => '',
            'availabilityStarts' => $search->departure['time'],
            'availabilityEnds' => $search->departure['time']
        ]);

        $transport = new Transport([
            'transportType' => 'CAR'
        ]);

        $trip->setAttribute('offer', $offer);
        $trip->setAttribute('transport', $transport);
        $offer->setAttribute('tripId', $trip->id);
        $trip->setAttribute('searchId', $search->id->toString());
        $trips->push($trip);
        return  $trips;
    }
}
