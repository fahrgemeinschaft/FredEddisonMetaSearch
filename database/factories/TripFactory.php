<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Trip;
use Faker\Generator as Faker;

$factory->define(Trip::class, function (Faker $faker) {
    return [
        'created' => $faker->dateTime(),
        'modified' => $faker->dateTime(),
        'createdBy' => $faker->word,
        'modifiedBy' => $faker->word,
        'url' => $faker->url,
        'additionalType' => $faker->word,
        'name' => $faker->name,
        'image' => $faker->word,
        'description' => $faker->text,
        'arrivalTime' => $faker->dateTime(),
        'availableSeats' => $faker->randomNumber(),
        'connector' => "Test Connector",
        'smoking' => $faker->word,
        'animals' => $faker->word
    ];
});
