<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Offer;
use Faker\Generator as Faker;

$factory->define(Offer::class, function (Faker $faker) {
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
        'availability' => $faker->word,
        'availabilityStarts' => $faker->dateTime(),
        'availabilityEnds' => $faker->dateTime(),
        'price' => $faker->word,
        'priceCurrency' => "€",
    ];
});
