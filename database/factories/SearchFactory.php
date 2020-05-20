<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\GeoLocation;
use App\Search;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Ramsey\Uuid\Uuid;

$factory->define(Search::class, function (Faker $faker) {
    return [
        'tripTypes' => 'OFFER',
        'reoccurDays' => null,
        'smoking' => null,
        'animals' => null,
        'transportTypes' => "CAR",
        'baggage' => null,
        'gender' => null,
        'organizations' => null,
        'availabilityStarts' => Carbon::now()->addDays(1),
        'availabilityEnds' => Carbon::now()->addDays(1),
        'arrival' => Carbon::now()->addDays(1),
        'departure' => Carbon::now()->addDays(1)
    ];
});

$factory->afterMaking(Search::class, function (Search $search) {
    //$startPoint = factory(GeoLocation::class)->make();
    $startPoint = new GeoLocation(['latitude' => 53.5511, 'longitude' => 9.9937]);
    $endPoint = new GeoLocation(['latitude' => 52.522, 'longitude' => 13.410]);
    $id = Uuid::uuid4();

    $search->setAttribute('startPoint', $startPoint);
    $search->setAttribute('endPoint', $endPoint);
    $search->setAttribute('id', $id);
});
