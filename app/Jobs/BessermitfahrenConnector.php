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

        if (count($entries) != 0) {
            $details = $entries['resultset'];
            $places = $entries['places'];
            foreach ($details as $entry) {
                $trips = $this->convertEntryToTrips($entry, $search, $places);
                $trips->each(function ($trip) {
                    SearchWrapper::insert($trip);
                });
            };
        }
    }

    public function convertEntryToTrips($entry, $search, $places): Collection
    {
        $trips = collect();
        $date = Carbon::createFromTimeString($search->departure['time']);
        $time = Carbon::createFromFormat('Y.m.d - H: i', $date->format('Y.m.d') . ' - ' . $entry[0][1][0]);

        $tripStart = $search->startPoint->location;
        $tripEnd = $search->endPoint->location;

        $trip = new Trip([
            'created' => Carbon::now(),
            'modified' => Carbon::now(),
            'startPoint' => new GeoLocation(['latitude' => $tripStart['latitude'], 'longitude' => $tripStart['longitude'], 'name' => $places[$entry[0][1][1]]]),
            'endPoint' => new GeoLocation(['latitude' => $tripEnd['latitude'], 'longitude' => $tripEnd['longitude'], 'name' => $places[$entry[0][2][1]]]),
            'connector' => "Bessermitfahren",
            'timestamp' => Carbon::now(),
            'departureTime' => Carbon::parse($search->departure['time'])->setTime(...explode(':', $entry[0][1][0])),
            'arrivalTime' => Carbon::parse($search->departure['time'])->setTime(...explode(':', $entry[0][2][0])),
            'availableSeats' => $entry[0][4],
            'smoking' => $this->getbitmask($entry[0][6], 1) ? 'YES' : 'NO',
            'animals' => $this->getbitmask($entry[0][6], 2) ? 'YES' : 'NO',
        ]);

        $trip->setAttribute('id', 'bessermitfahren-' . md5($entry[0][0]));

        $offer = new Offer([
            'url' => $entry[0][0],
            'name' => '',
            'image' => '',
            'price' => $entry[0][3],
            'priceCurrency' => '€',
            'availabilityStarts' => Carbon::parse($search->departure['time'])->setTime(...explode(':', $entry[0][1][0])),
            'availabilityEnds' => Carbon::parse($search->departure['time'])->setTime(...explode(':', $entry[0][2][0])),
        ]);

        $transport = new Transport([
            'transportType' => $this->getbitmask($entry[0][6], 4) ? 'TRAIN' : 'CAR'
        ]);

        $trip->setAttribute('offer', $offer);
        $trip->setAttribute('transport', $transport);
        $offer->setAttribute('tripId', $trip->id);
        $trip->setAttribute('searchId', $search->id->toString());
        $trips->push($trip);
        return $trips;
    }

    private function getbitmask($bitmask, $bit)
    {
        return (($bitmask & $bit) == $bit);
    }
}
