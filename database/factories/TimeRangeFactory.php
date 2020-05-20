<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\TimeRange;
use Faker\Generator as Faker;

$factory->define(TimeRange::class, function (Faker $faker) {
    return [
        'time' => $faker->dateTime(),
        'toleranceInDays' => $faker->randomNumber(),
    ];
});
