<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Participation;
use Faker\Generator as Faker;

$factory->define(Participation::class, function (Faker $faker) {
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
        'role' => $faker->word,
        'status' => $faker->word,
    ];
});
