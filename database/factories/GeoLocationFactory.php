<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\GeoLocation;
use Faker\Generator as Faker;

$factory->define(GeoLocation::class, function (Faker $faker) {
    return [
        'latitude' => 48.139,
        'longitude' => 11.574,
    ];
});
