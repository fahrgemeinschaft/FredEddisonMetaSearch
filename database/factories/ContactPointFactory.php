<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ContactPoint;
use Faker\Generator as Faker;

$factory->define(ContactPoint::class, function (Faker $faker) {
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
        'email' => $faker->safeEmail,
        'faxnumber' => $faker->word,
        'telephone' => $faker->word,
    ];
});
