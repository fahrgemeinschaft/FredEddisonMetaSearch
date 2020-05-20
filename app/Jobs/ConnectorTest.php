<?php

namespace App\Jobs;

use App\Offer;
use App\Search;
use App\Trip;
use App\Wrapper\SearchWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class ConnectorTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Search */
    protected $search;

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
        factory(Trip::class, 10)
            ->make()
            ->each(function (Trip $trip) {
                $offer = factory(Offer::class)->make();

                $trip->setAttribute('offer', $offer);

                $offer->trip_id = $trip->id;
                $trip->offer_id = $offer->id;

                SearchWrapper::insert($trip);
            });

    }
}
