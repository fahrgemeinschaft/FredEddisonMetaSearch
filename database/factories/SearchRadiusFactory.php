<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SearchRadius;
use Faker\Generator as Faker;

$factory->define(SearchRadius::class, function (Faker $faker) {
    return [
        'radius' => $faker->randomNumber(),
    ];
});
