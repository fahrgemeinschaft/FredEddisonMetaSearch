<?php

namespace App\Jobs;

use App\Offer;
use App\Search;
use App\Trip;
use App\Transport;
use App\GeoLocation;
use App\Wrapper\Apis\Blablacar;
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


class BlablacarConnector implements ShouldQueue
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
        $client = resolve(Blablacar::class);
        $search = $this->search;

        $start = $this->search->startPoint->location;
        $end = $this->search->endPoint->location;

        $date_start = Carbon::createFromTimeString($search->departure['time']);
        $date_end = Carbon::createFromTimeString($search->departure['time'])->addDays(2);

        $options = [
            'start_date_local' => $date_start->format('Y-m-d\TH:i:s'),
            'end_date_local' => $date_end->format('Y-m-d\TH:i:s'), // TODO: Endzeit
            'radius_in_meters' => $this->search->startPoint['radius'],//TODO: in Meter
        ];
        //TODO Courser durchlaufen
        $entries = $client->getEntries($start['latitude'], $start['longitude'], $end['latitude'], $end['longitude'], $options);
        $entries->each(function ($entry) use ($search) {

            $trips = $this->convertEntryToTrips($entry, $search);
            $trips->each(function ($trip) {
                SearchWrapper::insert($trip);
            });
        });

    }

    public function convertEntryToTrips($entry, $search): Collection
    {
        $trips = collect();
        $date = Carbon::create($entry['waypoints'][0]['date_time']);
        $tripStart = $entry['waypoints'][0]['place'];
        $tripEnd =$entry['waypoints'][1]['place'];
        $trip = new Trip([
            'created' => Carbon::now(),
            'modified' => Carbon::now(),
            'startPoint' => new GeoLocation(['latitude' => $tripStart['latitude'], 'longitude' => $tripStart['longitude']]),
            'endPoint' => new GeoLocation(['latitude' => $tripEnd['latitude'], 'longitude' => $tripEnd['longitude']]),
            'connector' => "BlaBlaCar",
            'timestamp' => Carbon::now()
        ]);

        $trip->setAttribute('id', 'blablacar-' .  md5($entry['link']));

        $offer = new Offer([
            'url' => $entry['link'],
            'name' => '',
            'image' => '',
            'availabilityStarts' => $date,
            'availabilityEnds' => $date
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
