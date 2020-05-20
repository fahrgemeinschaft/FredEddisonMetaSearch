<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Transport;
use Faker\Generator as Faker;

$factory->define(Transport::class, function (Faker $faker) {
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
        'transportType' => $faker->word,
        'seatingCapacity' => $faker->randomNumber(),
        'cargoVolume' => $faker->word,
        'owner' => factory(\App\Owner::class),
        'operator' => factory(\App\Operator::class),
    ];
});
